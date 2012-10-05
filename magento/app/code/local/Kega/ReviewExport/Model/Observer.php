<?php

class Kega_ReviewExport_Model_Observer
{
	public function exportReviews()
	{
		ini_set('memory_limit', '3024M');
		$exportModel = Mage::getModel('kega_reviewexport/export');
		return $exportModel->export();
	}
	
	public function importReviews()
	{
		ini_set('memory_limit', '3024M');
		$importModel = Mage::getModel('kega_reviewexport/import');
		return $importModel->import();
	}
}