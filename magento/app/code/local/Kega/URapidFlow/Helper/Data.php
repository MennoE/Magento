<?php

/**
 * @category   Kega
 */
class Kega_URapidFlow_Helper_Data extends Mage_Core_Helper_Abstract
{
	/**
	 * Placeholder for logfile path and name
	 *
	 * @var string
	 */
	protected $_logFilename = null;

	/**
	 * Verify if all needed directories are available on disk before we do anything.
	 * (non-existing dir's are created)
	 */
	public function setupCheck()
	{
		$types = array('export',
					   'import',
					   'import/backup',
					   'lock',
					   'log',
					   'raw',
					   'raw/backup',
					   );

		foreach($types as $type) {
			$this->getFileDir($type);
		}
	}

	/**
	 * Write (append) data to log file
	 *
	 * @param string $content
	 * @return string (filename)
	 */
	public function writeToLog($content)
	{
		if (!$this->_logFilename) {
			$this->_logFilename = $this->getFileDir('log') . DS . 'run-'.date('Ymd_his').'.log';
		}

		$handle = fopen($this->_logFilename, 'a');
		fwrite($handle, $content . PHP_EOL);
		fclose($handle);

		return $this->_logFilename;
	}

	/**
	 * Append output form output buffer to logfile.
	 * (only if output buffering is active)
	 *
	 * @param boolean $flush
	 */
	public function logOutput($flush = false)
	{
		if (!ob_get_level()) {
			return;
		}

		$this->writeToLog(ob_get_contents());
		if ($flush) {
			ob_flush();
		}

		ob_clean();
	}

	/**
	 * When class is destructed and we have created a logfile, output some info about the logfile.
	 */
	public function __destruct()
	{
		if ($this->_logFilename) {
			echo 'More info can be found in logfile: ' . $this->_logFilename . PHP_EOL;
		}
	}

	/**
	 * Fetch dir location for urapidflow file with given type.
	 * (non-existing dir's are created)
	 *
	 * @param string $type
	 * @return string
	 */
	public function getFileDir($type)
	{
		return Mage::getConfig()->getVarDir('urapidflow/' . $type);
	}

	/**
	 * Run the provided urapidflow import profiles
	 * Related processing files are automatically backuped by this method
	 *
	 * @param string/array $profiles (run given profile(s))
	 * @return boolean
	 */
	public function runProfiles($profiles = array())
	{
		if (!is_array($profiles)) {
			$profiles = array($profiles);
		}
		if (empty($profiles)) {
			echo 'No URapidFlow profiles were selected to run' . PHP_EOL;
		}
		$processedFilesBackupDir = $this->getFileDir('import/backup');

		$hasRun = false;
		foreach ($profiles as $profileTitle) {
			echo sprintf('Started URapifFlow profile: %s', $profileTitle) . PHP_EOL;
			$profile = Mage::helper('urapidflow')->run($profileTitle);
			if (!$profile->getId()) {
				Mage::throwException('Could\'t load profile: ' . $profileTitle);
			}

			$filePath = $profile->getFileBaseDir() . DS . $profile->getFilename();
			if (is_file($filePath)) {
				echo sprintf('Profile URapifFlow \'%s\' was finished processing file: %s', $profileTitle, $filePath) . PHP_EOL;

				// Check if there is created a logfile with newly imported products, if so we mail the contents.
				$logFilename = $this->getFileDir('log') . DS . $profile->getFilename() . '.new';
				if (file_exists($logFilename)) {
					$this->sendEmail(file_get_contents($logFilename));
					$this->backupFile($logFilename, $processedFilesBackupDir);
				}

				$backupDir = $this->backupFile($filePath, $processedFilesBackupDir);
				echo 'Moved processed file to: ' . $backupDir . PHP_EOL;
				$hasRun = true;
			} else {
				echo sprintf('No \'%s\' preprocessed file found: %s', $profileTitle, $filePath) . PHP_EOL;
			}

			$this->logOutput();

			if (file_exists($filePath)) {
				$message = sprintf('The file \'%s\' was not processed by Urapidflow (or no backup was created)', $filePath).PHP_EOL;

				echo $message;

				// We want this message to be displayed so it can be caputured by cron, true = flush.
				$this->logOutput(true);

				$message .= sprintf('You can also check the log file %s', $this->_logFilename);
				$this->sendEmail($message);
			}

			$this->logOutput();
		}

		return $hasRun;
	}

    /**
	 * Dir where backup files are located
	 *
	 * @throws Varien_Exception when dir path is not valid or it's not writable
	 */
	public function getBackupDestinationDir()
	{
		$dirPath = Mage::getBaseDir('var'). DS . 'urapidflow' . DS . 'raw/backup/';

		if (!is_dir($dirPath)) {
			Mage::throwException(sprintf('Invalid backup directory path %s', $dirPath));
		}

		if (!is_writable($dirPath)) {
			Mage::throwException(sprintf('Backup directory path %s is not writable', $dirPath));
		}

		return $dirPath;
	}

	/**
	 * Retrieve import files from the FTP import dir.
	 *
	 * ! Skip .ok and .oke files
	 * // TODO: Add filtering (with the use of extra parameter).
	 *
	 * @param array $credentials (host, user, password, passive, directory)
	 * @return array $files
	 */
	public function downloadImports($credentials)
	{
		if (empty($credentials['host'])) {
			Mage::throwException("No FTP host provided.\n");
		}

		$conn_id = ftp_connect($credentials['host']);
		if (!$conn_id) {
			Mage::throwException("Could not connect to host '{$credentials['host']}'.\n");
		}

		$login_result = ftp_login($conn_id, $credentials['user'], $credentials['password']);

		if ((!$conn_id) || (!$login_result)) {
            Mage::throwException(
				"Could not login to host '{$credentials['host']}' with username '{$credentials['user']}'.\n"
			);
			return false;
		}

		ftp_pasv($conn_id, $credentials['passive']);
		if (!empty($credentials['directory'])) {
	        if (!@ftp_chdir($conn_id, $credentials['directory'])) {
	            Mage::throwException("Could not change directory to {$credentials['directory']}.\n");
	        }
		}

		$contents = ftp_nlist($conn_id, ".");

		$files = array();
		foreach ($contents as $remoteFile) {

			if ($this->_isFtpDir($conn_id, $remoteFile)) {
				continue;
			}

			if (strpos($remoteFile, '.ok')) {
				continue;
			}

			$localFile = Mage::app()->getConfig()->getTempVarDir() . '/urapidflow/raw/' . basename($remoteFile);
			@unlink($localFile);

			// retry for 5 times
			$fileDownloaded = false;
			if (!ftp_get($conn_id, $localFile, $remoteFile, FTP_ASCII)) {
				for ($i = 0; $i < 5; $i++) {
					sleep(2);
					if (ftp_get($conn_id, $localFile, $remoteFile, FTP_ASCII)) {
						$fileDownloaded = true;
						break;
					}
				}
			} else {
				$fileDownloaded = true;
			}

			if (!$fileDownloaded) {
				Mage::throwException(
					"There was a problem while downloading {$remoteFile} to {$localFile}\n"
				);
			}

			$files[] = $localFile;

			if (!Mage::getStoreConfigFlag('urapidflow/import_ftp/testmode')) {
				ftp_delete($conn_id , $remoteFile);
			}
		}

        @ftp_close($conn_id);

		return $files;
    }

	/**
	 * Download all files of $fileType from the import FTP.
	 * Fetches and returns the raw file.
	 *
	 * @param string $filePrefix	(vrd, art)
	 * @param boolean $mail			(if no file found, send mail)
	 * @return string
	 */
	public function fetchImportFile($filePrefix, $mail = false)
	{
		$file = $this->getRapidFlowFile($filePrefix);

		if ($mail && $file === false) {
			$this->sendEmail(
				sprintf("
					-- No product import file found on FTP
					During my latest run I was unable to find any import files on the FTP server.
				")
			);
		}

		return $file;
	}

	/**
	 * Fetch files from $type defined dir which match the given $filePrefix
	 *
	 * @param string $filePrefix	(vrd, art)
	 * @param string $type			(raw)
	 *
	 * @return boolean
	 */
	public function getRapidFlowFile($filePrefix, $type = 'raw')
	{
		$dir = $this->getFileDir($type);

		if (!($handle = opendir($dir))) {
			Mage::throwException(sprintf(
				"Unable to fetch files from %s dir. The dir could not be found.", $dir
			));
		}

		$serialFailure = false;
		while (false !== ($file = readdir($handle))) {
			if ($file === "." || $file === "..") {
				continue;
			}

            if (strpos($file, $filePrefix) === false) {
            	continue;
            }

			return $dir . DS . $file;
	    }

		return false;
	}

	/**
	 * Check if given file is a directory.
	 * @return boolean
	 */
	private function _isFtpDir($ftpConnect, $fileOrDir)
	{
		if (@ftp_chdir($ftpConnect, basename($fileOrDir))) {
			ftp_chdir($ftpConnect, '..');
			return true;
		}

		return false;
	}

	/**
	 * Send email to specified email in urapidflow configuration
	 *
	 * @param String $message
	 */
	public function sendEmail($message)
	{
		$email = Mage::getStoreConfig('urapidflow/import/confirmation_email');

		$mail = new Zend_Mail('utf-8');
		$mail->setFrom($email);
		$mail->addTo($email);
		$mail->setBodyText($message);
		$mail->setSubject(Mage::getStoreConfig('urapidflow/import/confirmation_email_subject'));

		$result = $mail->send();
	}

	/**
	 * Merge raw headers with CSV data, return the data
	 *
	 * @throws Mage_Core_Exception when headers could not be combined with the data
	 * @return Array
	 */
	public function combineHeadersWithData($headers, $data)
	{
		unset($data[count($data)-1]);
		$productData = @array_combine($headers, $data);
		if (!$productData) {
			Mage::throwException('array_combine, not possible.');
		}
		return $productData;
	}

	/**
	* Move processed file to backup dir - every day a new backup dir,
	* old file name is preserved
	*
	* Save the backup timestamp in the register upon creating the first backup
	* We want all backups from the current session in the same dir.
	* This is needed since we must have the hours and minutes in the dir name to
	* prevent overwriting older backups when running the script twice a day/hour
	*
	* @param string $sourceFilePath
	* @param string $destinationDir
	* @return string $backupDir
	*/
	public function backupFile($sourceFilePath, $destinationDir)
	{
        $fileName = basename($sourceFilePath);

		if (!Mage::registry('backup-time')) {
			Mage::register('backup-time', date('Y-m-d_H:i'));
		}

		$backupFolderName = Mage::registry('backup-time');
        $backupDir = $destinationDir . DS . $backupFolderName;

        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0777);
        }

        $target = $backupDir . DS. $fileName;
		rename($sourceFilePath, $target);

		return $backupDir;
	}
}