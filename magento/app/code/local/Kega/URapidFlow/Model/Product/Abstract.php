<?php

class Kega_URapidFlow_Model_Product_Abstract extends Mage_Core_Model_Abstract
{
    protected $rawFilePath;


    public function setRawFilePath($rowFilePath)
    {
        $this->rawFilePath = $rowFilePath;
    }

    public function getRawFilePath()
    {
        return $this->rawFilePath;
    }

	/**
	 * Validates the delivered data struct to its related headers.
	 * If the amount of columns does not match the proces is being aborted.
	 *
	 * @throws Mage_Core_Exception when column amounts do not match
	 */
	public function verifyHeaders($headers, $data)
	{
		if (count($data) != count($headers)) {
			$msg = sprintf(
				'File header does not match column count %s, headers does not match %s fields in file.',
				count($headers), count($data)
			);
			$msg .= var_export($data, true);
			Mage::throwException($msg);
		}
	}
}