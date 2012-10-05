<?php
/**
 * This class is used build the products links(1) file
 * in a format required by the rapidflow plugin
 *
 * (1)CPSA: Catalog Product Super Configurable Attribute
 * CPSI: Catalog Product Super Configurable Item
 * CPSAP: Catalog Product Super Configurable Attribute Pricing
 *
 * @see http://www.unirgy.com/wiki/urapidflow/fixed_row_format
 *
 */
class Kega_URapidFlow_Model_Product_Links extends Kega_URapidFlow_Model_Importabstract
{

	/**
     * processed CPSA section csv header columns
     */
    protected $cpsaHeaders = array(
		'CPSA',
		'sku',
		'attribute_code',
		'position',
		'label',
    );


    /**
     * processed CPSI section csv header columns
     */
    protected $cpsiHeaders = array(
		'CPSI',
		'sku',
		'linked_sku',
    );

	/**
     * processed CPSAP section csv header columns
     */
    protected $cpsapHeaders = array(
		'CPSAP',
		'sku',//this is the configurable product sku
		'attribute_code',
		'value_label',//this is the value label: eg: for attribute code size we have the value_label: 18,19,20,...
		'website',
		'pricing_value',
		'is_percent',
    );

    protected $processedFileName = 'combine.txt';



	/**
	 * Prepares the data for writing in the csv file - buidls the cpsa and cpsi lines
	 *
	 * @param array $parsedProductData - @see Unirgy_RapidFlow_Helper_Kega_Productparse::parseCsv
	 * @return array
	 */
	public function parseData($parsedProductData)
	{
		$parsedData = array('cpsa' => array(), 'cpsi' => array(), 'cpsap' => array());

		foreach ($parsedProductData as $productInfo) {
			// only configurable products
			if (empty($productInfo['simple_sku'])) continue;

			$parsedData['cpsa'][] = $this->getCPSALine($productInfo);

			if (empty($parsedData['cpsa'])) continue;

			foreach($productInfo['simple_sku'] as $simpleSku) {
				$parsedData['cpsi'][] = $this->getCPSILines($productInfo, $simpleSku);
			}

			// No need to parse cpsap lines here, only needed during rebuild
		}

		return $parsedData;
	}


	/**
	 * Additional method to parse CPSAP data lines for usage during rebuild configurable proces
	 *
	 * NOTE: We could have re-used the ::parseData method, but then we needed to added all types of if
	 * condition checks to not bite other parts of the import process.
	 *
	 * @param array $parsedProductData
	 * @return array
	 */
	public function parseCPSAPData($parsedProductData)
	{
		$parsedData = array('cpsap' => array());

		foreach($parsedProductData as $configurableSku => $productData) {
			if (!isset($productData['price_differences'])) {
				continue;
			}

			foreach ($productData['price_differences'] as $priceDifference) {
				$parsedData['cpsap'][] = $this->getCPSAPLines($priceDifference);
			}
		}
		return $parsedData;
	}

    /**
     * Builds a combine csv file in the format required by rapidflow
     *
     * @throws Exception
     *
     * @param array $parsedProductData - @see Unirgy_RapidFlow_Helper_Kega_Productlinks::parseData
     * @param string $processedFilePath
     * @param string $processedFileName optional
     *
     */
    public function buildParsedCsv($parsedData, $processedFilePath, $processedFileName = '', $chainType = '')
    {

        $processedFilePath = $this->getProcessedFileCompletePath($processedFilePath, $processedFileName, $chainType);

		if (empty($parsedData)) {
			echo Mage::helper('core')->__('There are no %s records to write', $this->getProcessedFileName());
			return;
		}

        $handle = fopen($processedFilePath, "w");

		$header = array();
		$columnNames = array();

		$i = 0;
		foreach ($parsedData as $type => $lines) {
			if ($type == 'cpsa') {
				$header = array_merge(array('##'), $this->cpsaHeaders);
				$columnNames = $this->cpsaHeaders;
			} elseif ($type == 'cpsi') {
				$header = array_merge(array('##'), $this->cpsiHeaders);
				$columnNames = $this->cpsiHeaders;
			} elseif ($type == 'cpsap') {
				$header = array_merge(array('##'), $this->cpsapHeaders);
				$columnNames = $this->cpsapHeaders;
			} else {
				throw new Exception (sprintf('Invalid combine type %s',$type));
			}

			//write lines
			$j = 0;
			foreach ($lines as $line) {

				// build row in case $productData doesn't have the columns in the same order as the csv header
				$row = array();
				foreach ($columnNames as $columnName) {
					$row[$columnName] = $line[$columnName];
				}

				fputcsv($handle, $row);
				$j++;

				echo sprintf('Wrote %s line with sku %s and data (%s)' . PHP_EOL,
							 $type,
							 $line['sku'],
							 implode(',', $line));

				$i++;
			}

		}

        fclose($handle);

		echo sprintf('Wrote %s lines' . PHP_EOL,
							 $type,
							 $i);

    }


	/**
	 * Returns an array with all the cspa section records
	 * The array keys must be the same as
	 * the Unirgy_RapidFlow_Helper_Kega_Productlinks::cpsaHeaders
	 *
	 */
	public function getCPSALine($productData)
	{
		$cpsaLine = array(
			'CPSA' => 'CPSA',
			'sku' => $productData['sku'],
			'attribute_code' => 'size',
			'position' => '1',
			'label' => 'Maat',
		);
		return $cpsaLine;
	}

	/**
	 * Returns an array with all the cspi section records
	 * The array keys must be the same as the Unirgy_RapidFlow_Helper_Kega_Productlinks::cpsiHeaders
	 *
	 * @param array $productData
	 * @return array
	 */
	public function getCPSILines($productData, $simpleSku)
	{
		$cpsiLine = array(
			'CPSI' => 'CPSI',
			'sku' => $productData['sku'],
			'linked_sku' => $simpleSku,
		);

		return $cpsiLine;
	}

	/**
	 * Returns an array with all the cpsap section records
	 * The array keys must be the same as the Unirgy_RapidFlow_Helper_Kega_Productlinks::cpsapHeaders
	 *
	 * @param array $productData
	 * @return array
	 */
	public function getCPSAPLines($priceDifference)
	{
		$cpsapLine = array(
			'CPSAP' => 'CPSAP',
			'sku' => $priceDifference['configurable_sku'],
			'attribute_code' => 'size',
			'value_label' => $priceDifference['size'],
			'website' => isset($priceDifference['website']) ? $priceDifference['website'] : 0,
			'pricing_value' => $priceDifference['pricing_value'],
			'is_percent' => $priceDifference['is_percent'],
		);

		return $cpsapLine;
	}
}