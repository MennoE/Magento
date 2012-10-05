<?php
/**
 * This class is used to build a stock file in the format required by the rapidflow plugin
 *
 */
class Kega_URapidFlow_Model_Stock extends Kega_URapidFlow_Model_Importabstract
{
    /**
     * Placeholder for csv output headers (columns).
     * @var array
     */
    protected $processedCsvHeaders = array('sku',
										   'stock.qty',
										   'stock.use_config_manage_stock',
										   'stock.is_in_stock',
										   'last_updated',
										    );

	/**
	 * Placeholder for name of output file.
	 * @var string
	 */
    protected $processedFileName = 'stock.txt';

    /**
     * Builds a product csv file in the format required by rapidflow
     *
     * @throws Exception
     *
     * @param array $parsedStockData - @see Kega_URapidFlow_Model_Stock::parseData
     * @param string $processedFilePath
     * @param string $processedFileName optional
     *
     */
    public function buildParsedCsv($parsedStockData, $processedFilePath, $processedFileName = '', $chainType = '')
    {
        $processedFilePath = $this->getProcessedFileCompletePath($processedFilePath, $processedFileName);

		if (empty($parsedStockData)) {
			echo Mage::helper('core')->__('There are no %s records to write', $this->getProcessedFileName());
			return;
		}

        $handle = fopen($processedFilePath, "w");

        //write header
        fputcsv($handle, array_merge($this->processedCsvHeaders, array('last_updated')));

        //write product stock lines
        $now = Mage::getModel('core/date')->timestamp(time());
        $i = 0;
        foreach ($parsedStockData as $stockLine) {
			// Fill mandatory stock fields with data if not provided.
			if (!isset($stockLine['stock.use_config_manage_stock'])) {
				$stockLine['stock.use_config_manage_stock'] = 1;
            }
			if (!isset($stockLine['last_updated'])) {
				$stockLine['last_updated'] = date('Y-m-d h:i:s', $now);
            }
            if (!isset($stockLine['stock.is_in_stock'])) {
				$stockLine['stock.is_in_stock'] = ($stockLine['stock.qty'] > 0 ? 1 : 0);
            }

            // build row in case $stockLine doesn't have the columns in the same order as the csv header
            $row = array();
            foreach ($this->processedCsvHeaders as $columnName) {
				if (!isset($stockLine[$columnName]) || is_null($stockLine[$columnName])) {
					Mage::throwException(sprintf("Missing column %s in stockData on line %s", $columnName, $i));
				}
                $row[$columnName] = $stockLine[$columnName];
            }

            fputcsv($handle, $row);
            $i++;

            echo sprintf('Wrote product stock with sku %s and data (%s)' . PHP_EOL,
                         $stockLine['sku'],
                         implode(',', $stockLine));
        }

        echo sprintf('Wrote %s lines in %s' . PHP_EOL,
                         $i,
                         $processedFilePath);

        fclose($handle);
    }
}