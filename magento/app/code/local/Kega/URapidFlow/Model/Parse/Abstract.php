<?php
/**
 * This abstract class is used as base for parse models.
 */
abstract class Kega_URapidFlow_Model_Parse_Abstract extends Mage_Core_Model_Abstract
{
	/**
     * Placeholder for header columns of raw (CSV) file.
     * @var array
     */
	protected $_rawCsvHeaders = null;

    /**
     * Parses the csv data and loads the data into the model.
     *
     * Returns an array with parsed product/stock data: key -> product sku; value-> array with values
     *
     * @throws Mage_Exception
     *
     * @param string $rawFilePath
     */
    public function parseData($rawFilePath = '')
    {
        if (!is_file($rawFilePath)) {
            throw new Exception(Mage::helper('core')->__('Invalid raw file location %s', $rawFilePath));
        }

        echo sprintf('Started to parse %s' . PHP_EOL, $rawFilePath);

        $handle = fopen($rawFilePath, "r");
        $count = 0;

        while (($data = fgetcsv($handle, 4000, "|")) !== false) {
            if ($count == 0 && count($data) != count($this->_rawCsvHeaders)) {
                throw new Exception(sprintf('File header does not match column count %s, headers does not match %s fields in file.',
                                    count($this->_rawCsvHeaders),
                                    count($data))
                                    );
            }

            $rawData = @array_combine($this->_rawCsvHeaders, $data);
            if (!$rawData) {
                throw new Exception('array_combine, not possible.');
            }

            // Convert and add the raw data to the parsed data array (fieldnames must match the uRapidFlow profile).
            $this->addData($rawData);
        }

		// create backup of processed file
		$backupDestinationDir = Mage::helper('kega_urapidflow')->getFileDir('raw/backup');
		Mage::helper('kega_urapidflow')->backupFile($rawFilePath, $backupDestinationDir);

		return $this->_data;
    }

    /**
     * Convert and add the raw data to $this->_data
     */
    public function addData(array $rawData)
    {
		$this->_data[] = $rawData;
    }
}