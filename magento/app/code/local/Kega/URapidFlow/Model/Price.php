<?php

/**
 * @category Kega
 * @package Kega_URapidFlow
 */
class Kega_URapidFlow_Model_Price extends Kega_URapidFlow_Model_Importabstract
{
	const PRICES_TABLE = 'kega_urapidflow_productprices';

    /**
     * @var Array with raw csv file header columns
	 * used for combining arrays and inserting the data into the db
     */
    protected $priceTableHeaders = array(
		'identifier',
		'chain_id',
		'productprices_id',
		'article_id',
		'variant_id',
		'pricetype',
		'priority',
		'start_at',
		'end_at',
		'currency',
		'price',
		'active',
		'price_event_id',
	);

    /**
     * @var Array processed csv header column names used for urapidflow insertion
     */
    protected $processedCsvHeaders = array(
		'sku',
		'price',
		'special_price',
		'special_from_date',
		'special_to_date',
		'price_updated',
    );

    /**
     * @var string filename to where we are going to write our urapidflow ready data
     */
    protected $processedFileName = 'prices.txt';

	/**
	 * Automatically load the collection model for this model:
	 * Kega_URapidFlow_Model_Mysql4_Price_Collection
	 *
	 * @param Array $prices
	 */
	protected function _construct()
	{
		parent::_construct();
		$this->_init('kega_urapidflow/price');
	}

	/**
	 * Loops through all the prices and stores them into the
	 * custom price table 'kega_urapidflow_productprices'
	 *
	 * NOTE: the script does a small correction on the year that is being
	 * fetched from the import file. For some ranges the year in the timestamp
	 * is 9999, which isn't a valid format for a MySQL timestamp.
	 * Hence the string replace to 2030, which ought to be enough.
	 *
	 * @param Array $prices
	 */
	public function processPriceData()
	{
		if (!($handle = Mage::helper('kega_urapidflow')->getSplitFile('PRICE'))) {
			return false;
		}

		while (($data = fgetcsv($handle, 400000000, "|")) !== false) {
			$priceData = Mage::helper('kega_urapidflow')->combineHeadersWithData(
							$this->priceTableHeaders,
							$data
						);

			if (preg_match('/^9999/', $priceData['end_at'])) {
				$priceData['end_at'] = str_replace('9999', '2030', $priceData['end_at']);
			}

			$this->setData($priceData);
			$this->save();
		}
	}

	/**
	 * Fetch the active prices from our custom price table
	 * The follwing logic is applied to render a price valid for a product
	 *
	 * + Current date must be between start_at and end_at date
	 * + Use highest priority (lowest number!) when multiple timeframes are found for a specific price type
	 * + Use the timeframe that started last when frames have the same priority
	 *
	 * Current we handle to types of prices:
	 *	1: special price
	 *  2: regular price
	 *
	 * return array
	 */
	public function getActivePrices($chainType)
	{
		$this->_resource = Mage::getSingleton('core/resource');
        $this->_read = $this->_resource->getConnection('core_read');

		$dateToday = date('Y-m-d') . ' 00:00:00';

		$select = $this->_read->select();
		$select->from(array('p' => self::PRICES_TABLE))
			    ->where('start_at <= ?', $dateToday)
		        ->where('end_at > ?', $dateToday)
				->where('active', 1)
				->where('chain_id = ?', $chainType);

		$data = $this->_read->fetchAll($select);

		return $data;
	}

	/**
	 * Applies the pricing rules step by step:
	 *	- When multiple timeframes are found, the one with the highest priorty (lowest number!) is used
	 *  - When timeframes have the same priority, the one with the latest (most recent!) start date is used
	 *  - Prices where only the article_id is available count for all underlying simples
	 *
	 * The prices are parsed to the following struct:
	 * $prices[<productType>][<article_id>]['simples']
	 * $prices[<productType>][<article_id>]['rules'][<priceType>][<priorty>][timeframes]
	 *
	 * Simple product price updates are being stored in a separate sub-array (simples).
	 * This is to prevent that a configurable price update, which also updates all simples, overrules the price update for a specific size
	 *
	 */
	public function applyPricingRules($rawPrices)
	{
		$prices = array();

		foreach($rawPrices as $priceInfo) {

			$type = empty($priceInfo['variant_id']) ? 'configurable' : 'simple';
			$articleId = empty($priceInfo['variant_id']) ? $priceInfo['article_id'] : $priceInfo['variant_id'];

			if (!isset($prices[$type][$articleId])) {
				$prices[$type][$articleId] = array(
					'rules' => array(),
				);
			}

			$prices[$type][$articleId]['rules'][$priceInfo['pricetype']][$priceInfo['priority']][strtotime($priceInfo['start_at'])] = array(
				'start_at' => date('d-m-Y H:i:s', strtotime($priceInfo['start_at'])),
				'end_at' => date('d-m-Y H:i:s', strtotime($priceInfo['end_at'])),
				'price' => $priceInfo['price'],
			);
		}

		foreach($prices as $productType => $products) {
			foreach($products as $product => $data) {
				foreach($data['rules'] as $priceType => $priorities) {

					// Sort rules on priority to get the highest priority as the first key
					if (count($priorities) > 1) {
						ksort($priorities);
					}

					// Overwrite unneeded priorities, with highest prio struct
					$condition = current($priorities);

					// Sort timeframes, most recent will become the latest key-value pair in the array
					if (count($condition) > 1) {
						ksort($condition);
					}

					$priceType = $priceType == 2 ? 'regular' : 'special';
					$prices[$productType][$product]['conditions'][$priceType] = end($condition);
				}
				unset($prices[$productType][$product]['rules']);
			}
		}

		return $prices;
	}

	/**
	 * Parses the price data to a crawlable array for CSV file building
	 *
	 * During parsing, configurable prices are also updated to all underlying simple products.
	 *
	 * Price updates for simple products are straight forward. Just grab the Magento SKU.
	 * NOTE: simple products are by purpose parsed _AFTER_ the configurables. This way we are
	 * compatible with a mass price update on a configurable in combination with an update for
	 * a sole simple within the same configurable.
	 *
	 * @param array $rawPriceData
	 * return array
	 */
	public function parsePriceData($rawPriceData)
	{
		$parsedPriceData = array();
		$products = Kega_URapidFlow_Model_Product_File::getAllProductData();

		foreach(array('configurable', 'simple') as $productType) {
			if (!isset($rawPriceData[$productType])) {
				continue;
			}

			foreach($rawPriceData[$productType] as $product => $productData) {

				$priceData = array(
					'price' => '',
					'special_price' => '',
					'special_from_date' => '',
					'special_to_date' => '',
					'price_updated' => 1,
				);

				if (!empty($productData['conditions']['regular'])) {
					$priceData['price'] = $productData['conditions']['regular']['price'];
				}

				if (!empty($productData['conditions']['special'])) {
					$priceData['special_price'] = $productData['conditions']['special']['price'];
					$priceData['special_from_date'] = $productData['conditions']['special']['start_at'];
					$priceData['special_to_date'] = $productData['conditions']['special']['end_at'];
				}

				if ($productType === 'configurable') {
					if (isset($products[$product])) {
						foreach($products[$product]['associated_products'] as $simpleProduct) {
							$parsedPriceData[$simpleProduct['sku']] = $priceData;
						}
					}
				}
				else {
					$parsedPriceData[$product] = $priceData;
				}
			}
		}
		return $parsedPriceData;
	}

	/**
	 * Build price array for all updated configurable products
	 * This price array can be feeded into the CSV parser.
	 *
	 * @param array $rawPriceData
	 * @return array $parsedPriceData
	 */
	public function parseRebuildPriceData($rawPriceData)
	{
		$parsedPriceData = array();

		foreach($rawPriceData as $product => $productData) {
			foreach(array('price', 'special_price', 'special_from_date', 'special_to_date') as $key) {
				$parsedPriceData[$product][$key] = $productData[$key];
			}

			if (isset($productData['price_differences'])) {
				foreach($productData['price_differences'] as $sku => $simpleData) {
					foreach(array('price', 'special_price', 'special_from_date', 'special_to_date') as $key) {
						$parsedPriceData[$sku][$key] = $simpleData[$key];
					}
				}
			}
		}

		return $parsedPriceData;
	}

    /**
	 * Prepares the data for writing in the csv file - builds the CSV lines
	 *
	 * @param array $parsedPriceData
	 * @return array
	 */
    public function parseData($parsedPriceData)
    {
		$formattedPriceData = array();

		foreach($parsedPriceData as $product => $priceData) {
			$formattedPriceData[] = array(
				'sku' => $product,
				'price' => $priceData['price'],
				'special_price' => $priceData['special_price'],
				'special_from_date' => $priceData['special_from_date'],
				'special_to_date' => $priceData['special_to_date'],
				'price_updated' => isset($priceData['price_updated']) ? $priceData['price_updated'] : '0',
			);
		}
		return $formattedPriceData;
    }

    /**
	 * Write all parsed and formatted lines to a CSV for insertion into urapidflow
	 * NOTE: By purpose do we loop through the processedCsvHeaders to build the CSV line,
	 * in case the headers and delivered data are not in the same order.
	 *
	 * @param array $parsedPriceData
	 * @param string $processedFilePath
	 * @return void
	 */
	public function buildParsedCsv($parsedPriceData, $processedFilePath, $processedFileName = '', $prefix = '')
	{
        $processedFilePath = $this->getProcessedFileCompletePath($processedFilePath, $processedFileName, $prefix);

		if (empty($parsedPriceData)) {
			echo Mage::helper('core')->__('There are no %s records to write', $this->getProcessedFileName());
			return;
		}

        $handle = fopen($processedFilePath, "w");
        fputcsv($handle, $this->processedCsvHeaders);

        $i = 0;
        foreach ($parsedPriceData as $priceLine) {

			$row = array();
            foreach ($this->processedCsvHeaders as $columnName) {
				if (!is_null($priceLine[$columnName]) && !isset($priceLine[$columnName])) {
					throw new Exception (sprintf("Missing column %s in priceLine", $columnName));
				}
                $row[$columnName] = $priceLine[$columnName];
            }

            fputcsv($handle, $row);
            $i++;

            echo sprintf('Wrote product price with sku %s and data (%s)' . PHP_EOL,
                         $priceLine['sku'],
                         implode(',', $priceLine));
        }

        echo sprintf('Wrote %s  lines in %s' . PHP_EOL,
                         $i,
                         $processedFilePath);

        fclose($handle);
	}

	/**
	 * Creates a new array entry for each product with price differences
	 *
	 * NOTE: Kega_URapidFlow_Model_Product_File::loadCsv($exportFilePath) has to be called first
	 * to load the array with the existent product data
	 *
	 * @param array $parsedProductData
	 * @param string $websiteCode
	 * @return array with parsedProductData + price differences + updated price for configurables
	 */
	public function rebuildConfigurableProduct($parsedProductData, $websiteCode = '')
	{
		$parsedPriceDifferences = array();

		foreach ($parsedProductData as $productSku => $productData) {

			if (!isset($productData['associated_products']) || !count($productData['associated_products'])) {
				continue;
			}

			$lowestPrice =  Kega_URapidFlow_Model_Product_File::getLowestPrice($productData['associated_products']);

			// update configurable price with the lowest associated product price
			$parsedPriceDifferences[$productSku]['price'] = $lowestPrice['price'];
			$parsedPriceDifferences[$productSku]['special_price'] = $lowestPrice['special_price'];
			$parsedPriceDifferences[$productSku]['special_from_date'] = $lowestPrice['special_from_date'];
			$parsedPriceDifferences[$productSku]['special_to_date'] = $lowestPrice['special_to_date'];

			$parsedPriceDifferences[$productSku]['price_differences'] = Kega_URapidFlow_Model_Product_File::getSimpleProductsPriceDifferences(
				$productSku,
				$productData['associated_products'],
				$lowestPrice,
				$websiteCode
			);
		}

		return $parsedPriceDifferences;
	}
}