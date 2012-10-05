<?php
class Kega_URapidFlow_Model_Stock_Observer
{
	/**
	 * Run a setup check and initialize the locale settings.
	 */
	public function __construct()
	{
		ini_set("memory_limit", "2048M");

		// uRapidFlow triggers a session start, so we already do it over here, so we can output data without any problem.
		@session_start();

		Mage::helper('kega_urapidflow')->setupCheck();
	}

	/**
	 * Run webshop related stock import
	 * Download stock file, preprocess and run uRapidFlow import profile.
	 *
	 * @param Mage_Cron_Model_Schedule $schedule
	 * @return Kega_URapidFlow_Model_Observer
	 */
	public function runImport($schedule)
	{
		echo 'Stock import is started.' . PHP_EOL;

		// Start output buffering, so we can redirect the output to a log file.
		ob_start();

		try {
			Mage::helper('kega_urapidflow')
				->downloadImports(Mage::getStoreConfig('urapidflow/import_ftp'));

			$filePath = Mage::helper('kega_urapidflow')
							->fetchImportFile('ter-vrd.ina', false);

			if ($filePath) {
				$this->preProcessFile($filePath);
				if (Mage::helper('kega_urapidflow')->runProfiles('Product Stock Import')) {
					// Substract orders that have the status 'processing'.
					Mage::getModel('kega_urapidflow/stock_rebuild')->orderProductStockUpdate();

					// Set correct 'is in stock status' on the configurable products.
					Mage::getModel('kega_urapidflow/stock_rebuild')->setConfigurableProductIsInStockStatus();

					// Rebuild stock index.
					Mage::helper('kega_urapidflow')->reindexCatalogData('cataloginventory_stock');
				}
			} else {
				echo 'No stock file found to preprocess'.PHP_EOL;
			}
		} catch (Exception $e) {
			$logContent = 'Error occured: ' . $e->getMessage() . PHP_EOL . $e->getTraceAsString();
			$logFilePath = Mage::helper('kega_urapidflow')->writeToLog($logContent);

			$message = 'Error occured in import: ' . $logContent . PHP_EOL . PHP_EOL;
			$message .= sprintf('You can also check the log file %s', $logFilePath).PHP_EOL;

			echo $message;

			// We want this message to be displayed so it can be caputured by cron, true = flush.
			Mage::helper('kega_urapidflow')->logOutput(true);

			Mage::helper('kega_urapidflow')->sendEmail($message);

			// Make sure the Exception is sent to cron scheduler also.
			throw $e;
		}

		Mage::helper('kega_urapidflow')->logOutput();
		ob_end_clean();

		echo 'Stock import is done.' . PHP_EOL;

		return $this;
	}

	/**
	 * Parse stock into rapidflow CSV lines and write them to the import file.
	 * @return void
	 */
	private function preProcessFile($filePath)
	{
		$processedFilePath = Mage::helper('kega_urapidflow')->getFileDir('import');

		$parsedData = Mage::getModel('kega_urapidflow/stock_parse_pfa')->parseData($filePath);

		Mage::getModel('kega_urapidflow/stock')->buildParsedCsv($parsedData, $processedFilePath);
	}
}
