<?php
class Kega_URapidFlow_Model_Product_Observer
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
	 * Run webshop related product import
	 * Download product files, preprocess and run uRapidFlow import profile.
	 *
	 * @param Mage_Cron_Model_Schedule $schedule
	 * @return Kega_URapidFlow_Model_Observer
	 */
	public function runImport($schedule)
	{
		echo 'Product import is started.' . PHP_EOL;

		// Start output buffering, so we can redirect the output to a log file.
		ob_start();

		try {
			Mage::helper('kega_urapidflow')
				->downloadImports(Mage::getStoreConfig('urapidflow/import_ftp'));

			$filePath = Mage::helper('kega_urapidflow')
							->fetchImportFile('ter-art.ina', false);

			if ($filePath) {
				$this->preProcessFile($filePath);

				// Do not change the order
				$profiles = array('Product Import',
								  'Product Import Update',
								  'Product Links',
								  );

				if (Mage::helper('kega_urapidflow')->runProfiles($profiles)) {
					// Rebuild configurable products (prices)
					$this->rebuildConfigurableProducts();
				}
			} else {
				echo 'No product article file found to preprocess'.PHP_EOL;
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

		echo 'Product import is done.' . PHP_EOL;

		return $this;
	}

	/**
	 * Parse stock into rapidflow CSV lines and write them to the import file.
	 * @return void
	 */
	private function preProcessFile($filePath)
	{
		$processedFilePath = Mage::helper('kega_urapidflow')->getFileDir('import');

		$parsedData = Mage::getModel('kega_urapidflow/product_parse_pfa')->parseData($filePath);

		$parsedProductData = $parsedData['parsed_product_data'];
		$parsedProductOptionsData = $parsedData['parsed_product_attributes_options'];

		// save product.txt, products_update.txt - Products
		$formatedProductData = Mage::getModel('kega_urapidflow/product')
			->parseData($parsedProductData);

		Mage::getModel('kega_urapidflow/product')
			->buildParsedCsv($formatedProductData, $processedFilePath);

		// save combine.txt - Product Simple-Configurables links
		$formatedProductLinksData = Mage::getModel('kega_urapidflow/product_links')
										->parseData($parsedProductData);

		Mage::getModel('kega_urapidflow/product_links')
					->buildParsedCsv($formatedProductLinksData, $processedFilePath);
	}

	/**
	 * Export all products are recalculate the product price on the configurable level and
	 * the price diferences on the super attribute level between the simples within the configurable
	 *
	 * NOTE: We chose the easiest way to just export all products after all custom processes are runned.
	 * This recalculates all products (also the ones without an update). URapidFlow is fast enough to it this way
	 *
	 * @return boolean
	 */
	public function rebuildConfigurableProducts()
	{
		echo 'Start rebuilding configurable proces' . PHP_EOL;

		$processedFilePath = Mage::helper('kega_urapidflow')->getFileDir('import');

		// Create export of current products in Magento.
		$this->exportProducts();

		$products = Kega_URapidFlow_Model_Product_File::getAllProductData();

		// Calculate the price differences
		$priceData = Mage::getModel('kega_urapidflow/price')->rebuildConfigurableProduct($products);

		// Write import for price differences on super attribute level
		$formattedPriceDifferences = Mage::getModel('kega_urapidflow/product_links')->parseCPSAPData($priceData);

		Mage::getModel('kega_urapidflow/product_links')
			->buildParsedCsv($formattedPriceDifferences, $processedFilePath);

		// Needed structure to re-use price parse logic
		$priceModel = Mage::getModel('kega_urapidflow/price');
		$parsedPriceData = $priceModel->parseRebuildPriceData($priceData);

		$formattedPriceData = $priceModel->parseData($parsedPriceData);

		$priceModel->buildParsedCsv($formattedPriceData, $processedFilePath);

		// Rerun these profiles to process the rebuilded data
		$profiles = array('Product Links',
						  'Product Prices',
						  );

		return Mage::helper('kega_urapidflow')->runProfiles($profiles);
	}

	/**
	 * Create an export file of all known products in our database
	 * Use buildType to key the array either on configurable or on simple product
	 *
	 * @param string $buildType
	 * @return void
	 */
	public function exportProducts($buildType = 'configurable')
	{
		$profile = Mage::helper('urapidflow')
					->run('Product Export');

		if (!$profile->getId()) {
			Mage::throwException('Could\'t load profile: Product Export');
		}

		echo 'Generated the product export file.' . PHP_EOL;

		$filePath = $profile->getFileBaseDir() . DS . $profile->getFilename();
		if ($buildType === 'configurable') {
			Kega_URapidFlow_Model_Product_File::loadCsv($filePath);
		}
		else if ($buildType === 'simple') {
			Kega_URapidFlow_Model_Product_File::buildSimpleProductListing($filePath);
		}
		else {
			Mage::throwException(sprintf(
				'Unknown buildtype "%s" specified for product export',
				$buildType
			));
		}

		// create backup of product export file
		$backupDestinationDir = Mage::helper('kega_urapidflow')->getBackupDestinationDir();
		Mage::helper('kega_urapidflow')->backupFile($filePath, $backupDestinationDir);
	}
}
