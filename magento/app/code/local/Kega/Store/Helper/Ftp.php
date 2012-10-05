<?php
/**
 * @category   Kega
 */
class Kega_Store_Helper_Ftp extends Mage_Core_Helper_Abstract
{
    public function getDownloadDir()
    {
        $downloadDir = Mage::getBaseDir('var'). DS . 'store' . DS;

        if (!is_dir($downloadDir)) {
            mkdir($downloadDir, 0777);
        }

        return $downloadDir;
    }

    public function getDownloadBackupDir()
    {
        $downloadDir = $this->getDownloadDir();
        $downloadBackupDir = $downloadDir . 'backup/';

        if (!is_dir($downloadBackupDir)) {
            mkdir($downloadBackupDir, 0777);
        }

        return $downloadBackupDir;
    }

    public function getLogDir()
    {
        $downloadDir = $this->getDownloadDir();
        $downloadLogDir = $downloadDir . 'log/';

        if (!is_dir($downloadLogDir)) {
            mkdir($downloadLogDir, 0777);
        }

        return $downloadLogDir;
    }



    /**
	 * Retreive import files from the FTP import dir.
	 * @return array $files
	 */
	public function downloadImports()
	{
		$host = Mage::getStoreConfig('store/ftp_settings/host');
		$user = Mage::getStoreConfig('store/ftp_settings/user');
		$pass = Mage::getStoreConfig('store/ftp_settings/password');

		$dirImports = str_replace(
			'\\',
			'/',
			trim(Mage::getStoreConfig('store/ftp_settings/import_dir'), '\/')
		);

		$conn_id = ftp_connect($host);
		if (!$conn_id) {
			throw new Exception("Could not connect to host '{$host}'.\n");
		}

		$login_result = ftp_login($conn_id, $user, $pass);

		if ((!$conn_id) || (!$login_result)) {
            throw new Exception(
				"Could not login to host '{$host}' with username '{$user}'.\n"
			);
			return false;
		}

		ftp_pasv($conn_id, Mage::getStoreConfig('store/ftp_settings/passive'));
		if (!empty($dirImports)) {
	        if (!@ftp_chdir($conn_id, $dirImports)) {
	            throw new Mage_Core_Exception(
					"Could not change directory to {$dirImports}.\n"
				);
	        }
		}

		$contents = ftp_nlist($conn_id, ".");
		$files = array();

		foreach ($contents as $remoteFile) {
			if ($this->_isFtpDir($conn_id, $remoteFile)) {
				continue;
			}

            if (stripos($remoteFile, '.csv') === false) {
                continue;
            }

            $downloadDir = $this->getDownloadDir();
			$localFile = $downloadDir . basename($remoteFile);
			@unlink($localFile);

			// retry for 5 times
			$fileDownloaded = false;
			if (!ftp_get($conn_id, $localFile, $remoteFile, FTP_ASCII)) {
				for ($i = 0; $i < 5; $i++) {
					sleep(2);
					echo sprintf('FTP download retry #%s', $i+1);
					if (ftp_get($conn_id, $localFile, $remoteFile, FTP_ASCII)) {
						$fileDownloaded = true;
						break;
					}
				}
			} else {
				$fileDownloaded = true;
			}

			if (!$fileDownloaded) {
				throw new Exception(
					"There was a problem while downloading {$remoteFile} to {$localFile}\n"
				);
			}

			$files[] = $localFile;

			ftp_delete($conn_id , $remoteFile);
		}

        @ftp_close($conn_id);

		return $files;
    }

	/**
	 * Check if given file is a directory.
	 * @return boolean
	 */
	public function getFile($prefix)
	{
		$dir = $this->getDownloadDir();
		if (!($handle = opendir($dir))) {
			throw new Exception(sprintf(
				"Unable to fetch files from %s dir. The dir could not be found.", $dir
			));
		}

		while (false !== ($file = readdir($handle))) {
			if ($file === "." || $file === "..") {
				continue;
			}

            if (stripos($file, $prefix) !== 0) {
            	continue;
            }

			return $file;
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
	* Move processed file to backup dir - every day a new backup dir,
	* old file name is preserved
	* Note - date is UTC
	*
	* @param String $sourceFilePath -
	* @param String $destinationDir
	* @return void
	*/
	public function backupFile($sourceFilePath, $destinationDir)
	{
        $fileName = basename($sourceFilePath);
        $backupFolderName = date('d.m.Y');
        $backupDirName = $destinationDir . DS . $backupFolderName;

        if (!is_dir($backupDirName)) {
            mkdir($backupDirName, 0777);
        }

        $baseFileName = basename($fileName);
        sleep(1);//to have unique names;
        $newBackupFileName = date('Y-m-d\TH-i-s\Z').'_'.$baseFileName;

        $target = $backupDirName . DS. $newBackupFileName;
		rename($sourceFilePath, $target);
	}
}