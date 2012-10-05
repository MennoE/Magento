<?php
/**
 * @category Kega
 * @package	Kega_Store
 */
class Kega_Store_Model_Observer
{

	/**
	 * Runs the store and store extra opening import. Is run by cron
	 *
	 * the output of this method is saved in a log file (one log file per run)
	 *
	 * @param   Mage_Cron_Model_Schedule $schedule
	 * @return  Kega_URapidFlow_Model_Observer
	 */
	public function runStoreImport($schedule)
	{
		$logDir = Mage::helper('store/ftp')->getLogDir();
		$logFilePath = $logDir.'run-store-import'.date('Y-m-d-h-i-s').'.log';

		// we have some echoes so we capture them in a log file
		ob_start();

		try {
			echo sprintf('Started store import process').PHP_EOL;
			$logContent = ob_get_contents();
			$this->writeToLog($logFilePath, $logContent);
			ob_clean();

			$this->import();

			$logContent = ob_get_contents();
			$this->writeToLog($logFilePath, $logContent);

			ob_clean();

			echo  sprintf('Success: Generated log file %s', $logFilePath).PHP_EOL;
	        ob_flush();//we want the message displayed  so it can be caputured by cron

	        $logContent = ob_get_contents();
			$this->writeToLog($logFilePath, $logContent);
			ob_clean();

		} catch (Exception $e) {
			$logContent = 'Error occured: ' . $e->getMessage() . PHP_EOL . $e->getTraceAsString();
			$this->writeToLog($logFilePath, $logContent);

			$message = 'Error occured in store import: ' . $logContent . PHP_EOL . PHP_EOL;
			$message .= sprintf('You can also check the log file %s', $logFilePath).PHP_EOL;

			echo $message;
			ob_flush();//we want the message displayed so it can be caputured by cron

			$this->sendEmail($message);
		}

		ob_end_clean();

		return $this;
	}

	public function import()
	{
		mb_internal_encoding("UTF-8");
		Mage::helper('store/ftp')->downloadImports();

		$processedFilesBackupDir = Mage::helper('store/ftp')->getDownloadBackupDir();

		// import store file
		$storeFileName = Mage::getStoreConfig('store/ftp_settings/store_csv_filename');
		// in case the $storeFileName contains not the filename but the pattern -eg Store and the filename is 2011_09_25_Store.csv
		$realStoreFileName = Mage::helper('store/ftp')->getFile($storeFileName);


		$storeFilePath = Mage::helper('store/ftp')->getDownloadDir() . DS . $realStoreFileName;

		if (is_file($storeFilePath)) {
			echo sprintf("Found store file %s to process", $storeFilePath).PHP_EOL;
			$csvObject  = new Varien_File_Csv();
			$csvObject->setDelimiter(';');
			$csvData = $csvObject->getData($storeFilePath);

			Mage::helper('store/import')->importStores($csvData);
			echo sprintf("Finished import for %s", $storeFilePath).PHP_EOL;

			Mage::helper('store/ftp')->backupFile($storeFilePath, $processedFilesBackupDir);
			echo sprintf('Created Backup processed file %s', $storeFilePath).PHP_EOL;

		} else {
			echo sprintf("No store file found to import").PHP_EOL;
		}

		// import extraopening file
		$extraopeningFileName = Mage::getStoreConfig('store/ftp_settings/extraopening_csv_filename');
		// in case the $storeFileName contains not the filename but the pattern -eg Store and the filename is 2011_09_25_Store.csv
		$realExtraopeningFileName = Mage::helper('store/ftp')->getFile($extraopeningFileName);
		$extraopeningFilePath = Mage::helper('store/ftp')->getDownloadDir() . DS . $realExtraopeningFileName;

		if (is_file($extraopeningFilePath)) {
			echo sprintf("Found extra opening store file %s to process", $extraopeningFilePath).PHP_EOL;
			$csvObject  = new Varien_File_Csv();
			$csvObject->setDelimiter(';');
			$csvData = $csvObject->getData($extraopeningFilePath);

			Mage::helper('store/import')->importExtraOpenings($csvData);

			echo sprintf("Finished import for %s", $extraopeningFilePath).PHP_EOL;

			Mage::helper('store/ftp')->backupFile($extraopeningFilePath, $processedFilesBackupDir);
			echo sprintf('Created Backup processed file %s', $extraopeningFilePath).PHP_EOL;
		} else {
			echo sprintf("No extra opening store file path found to import").PHP_EOL;
		}


	}


	private function writeToLog($logFilePath, $logContent) {
		$handle = fopen($logFilePath, 'a');
		fwrite($handle, $logContent.PHP_EOL);
		fclose($handle);
	}


	private function sendEmail($message) {
		$email = Mage::getStoreConfig('store/general/notification_email');
		$subject =  Mage::getStoreConfig('store/general/notification_email_subject');

		$mail = new Zend_Mail('utf-8');
		$mail->setFrom($email);
		$mail->addTo($email);
		$mail->setBodyText($message);
		$mail->setSubject($subject);
		$result = $mail->send();
	}
}
