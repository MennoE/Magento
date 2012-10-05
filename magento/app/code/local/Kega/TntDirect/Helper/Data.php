<?php
class Kega_TntDirect_Helper_Data extends Mage_Core_Helper_Abstract
{
	public static function uploadFile($localFile, $remoteFile) {
        $host = Mage::getStoreConfig('carriers/kega_tnt_direct/ftp_host');
        $user = Mage::getStoreConfig('carriers/kega_tnt_direct/ftp_user');
        $pass = Mage::getStoreConfig('carriers/kega_tnt_direct/ftp_pass');

		//connect to ftp
		$conn_id = @ftp_connect($host);

    	if (!$conn_id) {
			throw new Kega_TntDirect_Exception("Could not connect to host '{$host}'.\n");
		}

		// login with username and password
		$login_result = @ftp_login($conn_id, $user, $pass);

    	// check connection
		if ((!$conn_id) || (!$login_result)) {
            throw new Kega_TntDirect_Exception("Could not login to host '{$host}' with username '{$user}'.\n");
		}

		// Passive mode on/off
        ftp_pasv($conn_id, Mage::getStoreConfig('carriers/kega_tnt_direct/ftp_passive'));

        // try to change the directory
        if (!@ftp_chdir($conn_id, 'workc2s')) {
            throw new Kega_TntDirect_Exception("Could not change directory to workc2s.\n");
        }

        // try to upload the file
        if (!@ftp_put($conn_id, $remoteFile, $localFile, FTP_BINARY)) {
            throw new Kega_TntDirect_Exception("There was a problem while uploading {$localFile} to {$remoteFile}\n");
        }

        // try to rename the file
        if (!ftp_rename($conn_id, $remoteFile, '../c2s/' . $remoteFile)) {
            throw new Kega_TntDirect_Exception("There was a problem while renaming {$remoteFile} to ../c2s/{$remoteFile}\n");
        }

        // close the FTP stream
        ftp_close($conn_id);
    }

    public function backupFile($file, $exception = true)
	{
		$backupDir = Mage::app()->getConfig()->getTempVarDir() . '/kega_tnt_direct/backups/';
		// If backup dir does not exists, create it.
		if (!file_exists($backupDir)) {
			mkdir($backupDir, 0777, true);
		}
        if (!rename($file, $backupDir . basename($file))) {
            if ($exception) {
            	throw new Kega_TntDirect_Exception('Unable to move file: ' . $file . ' to ' . $backupDir . basename($file) . PHP_EOL);
            }
        }
        echo 'Moved export file to backup-dir: ' . basename($file) . PHP_EOL;
	}
}
