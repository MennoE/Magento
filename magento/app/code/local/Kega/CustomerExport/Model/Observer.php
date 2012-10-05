<?php

class Kega_CustomerExport_Model_Observer
{
	public function exportCustomers()
	{
		ini_set('memory_limit', '3024M');
		$exportModel = Mage::getModel('kega_customerexport/export');
		return $exportModel->export();
	}
	
	public function importCustomers()
	{
		ini_set('memory_limit', '3024M');
		$importModel = Mage::getModel('kega_customerexport/import');
		return $importModel->import();
	}
}