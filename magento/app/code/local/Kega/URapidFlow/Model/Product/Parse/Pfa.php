<?php
/**
 * This class is used to parse the raw product file and build an array that is
 * later used by other classes to build the product file required by the Urapidflow plugin.
 *
 */
class Kega_URapidFlow_Model_Product_Parse_Pfa extends Kega_URapidFlow_Model_Parse_Abstract
{
	/**
     * Placeholder for header columns of raw (CSV) file.
     * @var array
     */
    protected $_rawCsvHeaders = array('start_mark',//0
								      'barcode',//1
								      'product_code',//2
								      'name',//3
								      'description',//4
								      'unknown1',//5 - Leverancieromschrijving
								      'unknown2',//6 - Merkcode
								      'manufacturer',//7
								      'color_code',//8
								      'color',//9
								      'size',//10
								      'size_numeric',//11
								      'unknown3',//12 - Kwaliteitscode (materiaal)
								      'unknown4',//13 - Kwaliteitsomschrijving
								      'unknown5',//14 -  Kenmerkcode
								      'unknown6',//15 - Internetkeuze
								      'end_mark',//16
									  );
	/**
     * Placeholder for parsed data
     *
     * @var array
     */
    protected $_data = array('parsed_product_data' => array(),
							 'parsed_product_attributes_options' => array(),
							 );

	/**
     * Placeholder for price data from the raw price file; key- barcode
     * @var array
     */
    protected $_rawPriceData = array();

    public function __construct()
    {
    	parent::__construct();

    	$filePath = Mage::helper('kega_urapidflow')
						->fetchImportFile('ter-prs.ina', true);

    	$this->_rawPriceData = Mage::getModel('kega_urapidflow/price_parse_pfa')->parseData($filePath);
    }

	/**
     * Parses the csv data and returns an array with simple and configurable products
     *
     * Note: you have to parse the price and stock file first because we need to set the price and the stock info
     * (the price and stock file does not contain info to get the sku of the product and the sku is required by urapidflow
     * - so we set all the data here).
     * Also, the stock file is use do determine if we have to import a product or not
     *
     * Returns an array with parsed product data: key -> product sku; value->array(sku => '', simple_sku => array(), data => array())
     * @see Kega_URapidFlow_Model_Productparse::getConfigurableProductData and
     * @see Kega_URapidFlow_Model_Productparse::getSimpleProductData for data content
     * simple_sku is set only for configurable
     *
     * @throws Mage_Exception
     *
     * @param string $rawFilePath - optional; path to file that needs to be parsed
     * @return array
     */
    public function parseData($rawFilePath = '')
    {
    	if (empty($this->_rawPriceData)) {
    		$msg = Mage::helper('core')->__('Please parse the price file first and set the resulting price data - @see Kega_URapidFlow_Model_Productparse::setRawPriceData');
            throw new Exception($msg);
    	}

        if (!is_file($rawFilePath)) {
            $msg = Mage::helper('core')->__('Invalid raw file location %s', $rawFilePath);
            throw new Exception($msg);
        }

        echo sprintf('Started to parse %s' . PHP_EOL, $rawFilePath);

        $handle = fopen($rawFilePath, "r");
        $count = 0;
        $configurableProductData = array();
        $simpleProductData = array();
        $parsedProductData = array();

        $productAttributeOptions = Mage::getModel('kega_urapidflow/product_attributeoptions')->getProductAttributesOptions();

        while (($data = fgetcsv($handle, 4000, "|")) !== false) {
            if ($count == 0 && count($data) != count($this->_rawCsvHeaders)) {
				$msg = sprintf('File header does not match column count %s, headers does not match %s fields in file.',
                                    count($this->_rawCsvHeaders),
                                    count($data)
									);
				$msg .= var_export($data, true);
                throw new Exception($msg);
            }

            $rawData = @array_combine($this->_rawCsvHeaders, $data);
            if (!$rawData) {
                throw new Exception('array_combine, not possible.');
            }

			// add attribute option if new
			foreach ($rawData as $attributeName => $attributeValue) {
				if ($attributeValue === '') continue;
				if (!isset($productAttributeOptions[$attributeName])) continue;

				if (!isset($productAttributeOptions[$attributeName]['options'][strtolower($attributeValue)])
					&&
					!isset($this->_data['parsed_product_attributes_options'][$attributeName]['options'][$attributeValue])
					) {
					$this->_data['parsed_product_attributes_options'][$attributeName]['attribute_code'] = $attributeName;
					$this->_data['parsed_product_attributes_options'][$attributeName]['options'][$attributeValue] = $attributeValue;
					//echo sprintf('New attribute option %s for %s', $attributeValue, $attributeName). PHP_EOL;
					continue;
				}
			}

			$simpleSku =  $this->getSimpleProductSku($rawData);

            // add configurable product
            if (!$this->isOnlySimpleProduct($rawData)) {
                $configurableSku = $this->getConfigurableProductSku($rawData);

                if (!isset($configurableProductData[$configurableSku])) {
                    $configurableProductData = $this->getConfigurableProductData($rawData);
                    $configurableProductData[$configurableSku] = array(
                        'sku' => $configurableSku,
                        'simple_sku' => array($simpleSku),
                        'data' => $configurableProductData,
                    );
                } else {
                    $configurableProductData[$configurableSku]['simple_sku'][] = $simpleSku;
                }

                $parsedProductData[$configurableSku] = $configurableProductData[$configurableSku];
            }


            // add simple product
            if (!isset($simpleProductData[$simpleSku])) {
                $simpleProductData[$simpleSku] = array(
                    'sku' => $simpleSku,
                    'data' => array(),
                );
            }
            $simpleProductData[$simpleSku]['data'] = $this->getSimpleProductData($rawData);

            $parsedProductData[$simpleSku] = $simpleProductData[$simpleSku];
        }

		$parsedProductData = $this->rebuildConfigurableProduct($parsedProductData);
		$this->_data['parsed_product_data'] = $parsedProductData;

		// create backup of processed file
		$backupDestinationDir = Mage::helper('kega_urapidflow')->getBackupDestinationDir();
		Mage::helper('kega_urapidflow')->backupFile($rawFilePath, $backupDestinationDir);

        return $this->_data;
    }

    /**
     * Check if given productdata is only a simple product
     * (without configurable parent product).
     * For example: In some cases a few categories only contain simple products like: bags, sprays etc.
     * (no size option is used there)
     *
     * @param unknown_type $rawData
     */
	public function isOnlySimpleProduct($rawData)
    {
		// In this case we only have products that consist of simple with configurable product.
		return false;
    }

	/**
     * Gets simple product sku from import data
     *
     * @param array $rawData
     * @return string
     */
    public function getSimpleProductSku($rawData)
    {
        $sku = sprintf('%s-%s-%s',
					$rawData['product_code'],
					$rawData['color_code'],
					$rawData['barcode']);

        return $sku;
    }

	/**
     * Gets the configurable product sku from import data
     *
     * @param array $csvRowData
     * @return string
     */
    public function getConfigurableProductSku($rawData)
    {
        return $rawData['product_code'].'-'.$rawData['color_code'];
    }

	/**
	 * Creates a new array entry for each product with price differences
	 *
	 * Kega_URapidFlow_Model_Product_File::loadCsv($exportFilePath) has to be called first
	 * to load the array with the existent product data
	 *
	 * @param array $parsedProductData
	 * @return array with parsedProductData + price differences + updated price for configurables
	 */
	public function rebuildConfigurableProduct($parsedProductData)
	{
		foreach ($parsedProductData as $productSku => $productData) {
			if (empty($productData['simple_sku'])) continue; // not an configurable product

			$now = Mage::getModel('core/date')->timestamp(time());

			$configurableProductSku = $productSku;
			$existentProductData = Kega_URapidFlow_Model_Product_File::getProductData($configurableProductSku);
			$configurableSimpleProducts = Kega_URapidFlow_Model_Product_File::getAllConfigurableSimpleProducts(
															$configurableProductSku, $parsedProductData, $existentProductData);

			$lowestPrice =  Kega_URapidFlow_Model_Product_File::getLowestPrice($configurableSimpleProducts);

			// update configurable price with the lowest associated product price
			$parsedProductData[$productSku]['data']['price'] = $lowestPrice['price'];
			$parsedProductData[$productSku]['data']['special_price'] = $lowestPrice['special_price'];
			$parsedProductData[$productSku]['data']['special_from_date'] = date('Y-m-d', $now);

			$parsedProductData[$productSku]['price_differences'] =  Kega_URapidFlow_Model_Product_File::getSimpleProductsPriceDifferences($configurableProductSku,
																																		 $configurableSimpleProducts,
																																		 $lowestPrice);

		}

		return $parsedProductData;
	}

	/**
     * Returns an array with configurable product data
     * The array keys must be the same as
     * the Kega_URapidFlow_Model_Product::processedCsvHeaders values
     *
     * @param $csvRowData
     * @return array
     */
    public function getConfigurableProductData($csvRowData)
    {
        $configurableProductData = array();
        $configurableSku = $this->getConfigurableProductSku($csvRowData);

        $now = Mage::getModel('core/date')->timestamp(time());

        // new configurable products are set by default disabled
		$websiteCodes = $this->getWebSiteCodes();
		unset($websiteCodes[0]);//remove admin
		$productWebsites = implode(';',$websiteCodes);

		// new configurable products are set by default disabled
		$status = (Mage::getStoreConfigFlag('urapidflow/import_advanced/import_configurable_with_state') ? 1 : 2);

		$urlKey = sprintf('%s-%s-%s-%s',
						ltrim($csvRowData['manufacturer'],'.'),
						$csvRowData['name'],
						$csvRowData['color'],
						$configurableSku);

        $configurableProductData = array(
                'sku' => $configurableSku,
                'name' => $csvRowData['name'],
                'status' => $status,
                'tax_class_id' => 'Taxable Goods',
                'visibility' => 4,
                'product.attribute_set' => 'Default',
                'product.type' => 'configurable',
				'price_view' => null, // this is used only for bundle products
				'weight' => null, //dummy value - because this is a required field
                'stock.qty' => null, // no qty
				'stock.use_config_manage_stock' => 1, // use option label
				'stock.is_in_stock' => 1, // use option label
                'description' => $csvRowData['description'],
                'short_description' => $csvRowData['description'],
                'price' => 0,// it's going to be set later
                'special_price' => 0, // is going to be set later
				'special_from_date' => '', // Mandatory, can be empty
				'special_to_date' => '', // Mandatory, can be empty
                'size' => null, // does not apply to configurable products
				'color' => $csvRowData['color'],
				'color_code' => $csvRowData['color_code'],
				'barcode' => '',
				'manufacturer' => $csvRowData['manufacturer'],
				'url_key' => $urlKey,
				'product.websites' => $productWebsites,
				'options_container' => 'Kolom productgegevens',
				'created_by_urapidflow' => date('Y-m-d h:i:s', $now),
				'last_updated' => date('Y-m-d h:i:s', $now),
        );

        return $configurableProductData;
    }


    /**
     * Returns an array with simple product data
     * The array keys must be the same as
     * the Kega_URapidFlow_Model_Product::processedCsvHeaders values
     *
     * @param $csvRowData
     * @return array
     */
    public function getSimpleProductData($csvRowData)
    {
        $simpleProductData = array();

        $websiteCodes = $this->getWebSiteCodes();
		unset($websiteCodes[0]);//remove admin
		$productWebsites = implode(';',$websiteCodes);

		$configurableSku = $this->getConfigurableProductSku($csvRowData);
		$sku = $this->getSimpleProductSku($csvRowData);

		$now = Mage::getModel('core/date')->timestamp(time());

        // If no name is set, use sku.
        if (empty($csvRowData['name'])) {
            $csvRowData['name'] = $sku;
        }
		$urlKey = sprintf('%s-%s-%s-%s-%s',
						ltrim($csvRowData['manufacturer'],'.'),
						$csvRowData['name'],
						$csvRowData['color'],
						$configurableSku,
						$csvRowData['barcode']);

		//get the price info
		$price = $specialPrice = 0;
		if (isset($this->_rawPriceData[$csvRowData['barcode']])) {
			$price = $this->_rawPriceData[$csvRowData['barcode']]['price'];
			$specialPrice = $this->_rawPriceData[$csvRowData['barcode']]['special_price'];

			/*
			echo sprintf("We found price info for %s - %s  - in the parsed price file result: %s - %s",
				$csvRowData['barcode'],
				$sku,
				$price,
				$specialPrice
			).PHP_EOL;
			*/
		} else {
			echo sprintf("No price info found for %s - %s  - in the parsed price file result",
				$csvRowData['barcode'],
				$sku
			).PHP_EOL;
		}

        $simpleProductData = array(
                'sku' => $sku,
                'name' => $csvRowData['name'],
                'status' => 1,
                'tax_class_id' => 'Taxable Goods',
                'visibility' => ($this->isOnlySimpleProduct($csvRowData))? 4 : 1,
                'product.attribute_set' => 'Default',
                'product.type' => 'simple',
				'price_view' => null, // this is used only for bundle products
				'weight' => '500.0000', //dummy value - because this is a required field
                'stock.qty' => 0,
				'stock.use_config_manage_stock' => 1, // use option label
				'stock.is_in_stock' => 0, // use option label
                'description' => $csvRowData['description'],
                'short_description' => $csvRowData['description'],
                'price' => $price,
                'special_price' => $specialPrice,
				'special_from_date' => '', // Mandatory, can be empty
				'special_to_date' => '', // Mandatory, can be empty
                'size' => $csvRowData['size'],
				'color' => $csvRowData['color'],
				'color_code' => $csvRowData['color_code'],
				'barcode' => $csvRowData['barcode'],
				'manufacturer' => $csvRowData['manufacturer'],
				'url_key' => $urlKey,
				'product.websites' => $productWebsites,
				'options_container' => 'Blok na info-kolom',
				'created_by_urapidflow' => date('Y-m-d h:i:s', $now),
				'last_updated' => date('Y-m-d h:i:s', $now),
        );

        return $simpleProductData;
    }

	/**
	 * Returns the website ids from all known webshops
	 *
	 * @param void
	 * @return array
	*/
    protected function getWebSiteCodes()
    {
        $websiteCodes = array();
		$webstores = Mage::app()->getStores(true, true);
        foreach ($webstores as $store) {
            $id = $store->getWebsite()->getId();
            $websiteCodes[$id] = $store->getWebsite()->getCode();
        }

        return $websiteCodes;
    }
}
