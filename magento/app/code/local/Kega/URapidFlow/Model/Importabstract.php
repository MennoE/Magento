<?php
/**
 * This class should be impemented by classes that generates files in the format required by URapidFlow plugin
 *
 */
abstract class Kega_URapidFlow_Model_Importabstract extends Mage_Core_Model_Abstract
{

    protected $processedFilePath;
    protected $processedFileName;


    public function setProcessedFilePath($processedFilePath)
    {
        $this->processedFilePath = $processedFilePath;
    }

    public function setProcessedFileName($processedFileName)
    {
        $this->processedFileName = $processedFileName;
    }

    public function getProcessedFilePath()
    {
        return $this->processedFilePath;
    }

    public function getProcessedFileName()
    {
        return $this->processedFileName;
    }

    /**
     * Writes the csv file in the format required by the URapidFlow plugin
     *
     * @param array $formatedData - see Kega_URapidFlow_Model_Importabstract::parseData()
     * @param string $processedFilePath - absolute directory where the csv shoudl be saved
     * @param string $processedFileName - name of the csv file
     */
    public abstract function buildParsedCsv($formatedData, $processedFilePath, $processedFileName = '', $chainType = '');


    public function getProcessedFileCompletePath($processedFilePath, $processedFileName = '', $filePrefix = '')
    {
        if (!empty($processedFileName)) {
            $this->setProcessedFileName($processedFileName);
        }

        if (!empty($processedFilePath)) {
            $this->setProcessedFilePath($processedFilePath);
        }

        if (!is_dir($this->getProcessedFilePath())) {
            $msg = Mage::helper('core')->__('Invalid processed file dir %s', $this->getProcessedFilePath());
            throw new Exception($msg);
        }

        if (!is_writable($this->getProcessedFilePath())) {
            $msg = Mage::helper('core')->__('Processed file dir %s is not writable', $this->getProcessedFilePath());
            throw new Exception($msg);
        }

        return $this->getProcessedFilePath() . DS . ($filePrefix ? $filePrefix . '-' : '') . $this->getProcessedFileName();
    }

}