<?php
/**
 * This class is used to build a products file in the format required by the rapidflow plugin
 *
 */
class Kega_URapidFlow_Model_Product extends Kega_URapidFlow_Model_Importabstract
{
    /**
     * processed csv header columns for new products
     */
    protected $processedCsvHeaders = array(
             'sku',
             'name',
             'status',
             'tax_class_id',
             'visibility',
             'product.attribute_set',
             'product.type',
			 'price_view',
			 'weight',
             'description',
             'short_description',
             'price',
             'special_price',
             'size',
			 'color',
			 'color_code',
			 'barcode',
			 'manufacturer',
			 'url_key',
			 'product.websites',
			 'created_by_urapidflow',
			 'last_updated',
			 'stock.qty',
			 'stock.use_config_manage_stock',
			 'stock.is_in_stock',
    );


    /**
     * processed csv header columns for updating
     */
    protected $processedCsvHeadersUpdate = array(
            'sku',
            'price',
            'special_price',
	//		'stock.qty',
	//		'stock.use_config_manage_stock',
	//		'stock.is_in_stock',
    		'last_updated',
    );

	protected $processedFileName = 'product.txt';

	/**
	 * Contains records that have to be updated
	 * @var string
	 */
	protected $processedFileNameUpdate = 'product_update.txt';


	public function getProcessedFileNameUpdate()
	{
		return $this->processedFileNameUpdate;
	}

	public function setProcessedFileNameUpdate($processedFileNameUpdate)
	{
		return $this->processedFileNameUpdate = $processedFileNameUpdate;
	}


    /**
	 * Prepares the data for writing in the csv file - builds the product lines
	 *
	 * @param array $parsedProductData - @see Kega_URapidFlow_Model_Product::parseCsv
	 * @return array
	 */
    public function parseData($parsedProductData)
    {
        // at the moment no parsing necessary, data is already in the right format
		return $parsedProductData;
    }

    /**
     * Builds a product csv file in the format required by rapidflow
     *
     * @throws Exception
     *
     * @param array $parsedProductData - @see Unirgy_RapidFlow_Helper_Kega_Product::parseData
     * @param string $processedFilePath
     * @param string $processedFileName optional
     *
     */
    public function buildParsedCsv($parsedProductData, $processedFilePath, $processedFileName = '', $chainType = '')
    {
        $this->buildParsedCsvCreate($parsedProductData, $processedFilePath, $this->getProcessedFileName());
        $this->buildParsedCsvUpdate($parsedProductData, $processedFilePath, $this->getProcessedFileNameUpdate());
    }

    /**
     * Builds the csv file for new products
     *
     * @param array $parsedProductData - @see Unirgy_RapidFlow_Helper_Kega_Product::parseData
     * @param string $processedFilePath
     * @param string $processedFileName optional
     */
    private function buildParsedCsvCreate($parsedProductData, $processedFilePath, $processedFileName = '')
    {
    	$processedFilePath = $this->getProcessedFileCompletePath($processedFilePath, $processedFileName);

		if (empty($parsedProductData)) {
			echo Mage::helper('core')->__('There are no %s records to write', $this->getProcessedFileName());
			return;
		}

        $handle = fopen($processedFilePath, "w");

        //write header
        fputcsv($handle, $this->processedCsvHeaders);

        //write product lines
        $i = 0;
        foreach ($parsedProductData as $productInfo) {
			$productLine = $productInfo['data'];

            // build row in case $productData doesn't have the columns in the same order as the csv header
            $row = array();
            foreach ($this->processedCsvHeaders as $columnName) {
                $row[$columnName] = $productLine[$columnName];
            }

            fputcsv($handle, $row);
            $i++;

            echo sprintf('Wrote product with sku %s and data (%s)' . PHP_EOL,
                         $productLine['sku'],
                         implode(',', $productLine));
        }

        echo sprintf('Wrote %s  lines in %s' . PHP_EOL,
                         $i,
                         $processedFilePath);

        fclose($handle);
    }

	/**
     * Builds the csv file to update product info (it contains the price info at the moment)
     *
     * @param array $parsedProductData - @see Unirgy_RapidFlow_Helper_Kega_Product::parseData
     * @param string $processedFilePath
     * @param string $processedFileName
     */
    private function buildParsedCsvUpdate($parsedProductData, $processedFilePath, $processedFileName)
    {
    	$processedFilePath = $this->getProcessedFileCompletePath($processedFilePath, $processedFileName);

		if (empty($parsedProductData)) {
			echo Mage::helper('core')->__('There are no %s records to write', $this->getProcessedFileNameUpdate());
			return;
		}

        $handle = fopen($processedFilePath, "w");

        //write header
        fputcsv($handle, $this->processedCsvHeadersUpdate);


        //write product lines
        $i = 0;
        foreach ($parsedProductData as $productInfo) {
			$productLine = $productInfo['data'];

            // build row in case $productData doesn't have the columns in the same order as the csv header
            $row = array();
            foreach ($this->processedCsvHeadersUpdate as $columnName) {
                $row[$columnName] = $productLine[$columnName];
            }

            fputcsv($handle, $row);
            $i++;

            echo sprintf('Wrote product with sku %s and data (%s)' . PHP_EOL,
                         $productLine['sku'],
                         implode(',', $productLine));
        }

        echo sprintf('Wrote %s  lines in %s' . PHP_EOL,
                         $i,
                         $processedFilePath);

        fclose($handle);
    }
}