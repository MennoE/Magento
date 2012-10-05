<?php
class Kega_Pdf_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getAutoprint($ftpDir = null, $localDir = null)
    {
        $host = Mage::getStoreConfig('pdf/ftp/host');
        $user = Mage::getStoreConfig('pdf/ftp/user');
        $pass = Mage::getStoreConfig('pdf/ftp/password');

        return new Kega_Pdf_Helper_Autoprint($host, $user, $pass, $ftpDir, $localDir);
    }

	/**
     * Upload PDF to the FTP print dir's.
     */
	public function uploadPDF($pdf, $dir, $prefix = 'magento-pdf')
	{
		$host = Mage::getStoreConfig('pdf/ftp/host');
		$user = Mage::getStoreConfig('pdf/ftp/user');
		$password = Mage::getStoreConfig('pdf/ftp/password');

		//connect to ftp
		$conn_id = @ftp_connect($host);

		if (!$conn_id) {
			throw new Kega_Pdf_Exception("Could not connect to host '{$host}'.\n");
		}

		// login with username and password
		$login_result = @ftp_login($conn_id, $user, $password);

		// check connection
		if ((!$conn_id) || (!$login_result)) {
            throw new Kega_Pdf_Exception("Could not login to host '{$host}' with username '{$user}'.\n");
		}

		// Passive mode on/off
        ftp_pasv($conn_id, Mage::getStoreConfig('pdf/ftp/passive'));

        if (!empty($dir)) {
	        // try to change the directory
	        if (!@ftp_chdir($conn_id, $dir)) {
		        if (!@ftp_mkdir($conn_id, $dir)) {
		            throw new Kega_Pdf_Exception("Could not change directory to {$dir} and also not possible to create it.\n");
		        }

		        // Change to the created dir.
		        @ftp_chdir($conn_id, $dir);
	        }
		}

		$fileName = tempnam("/tmp", $prefix . '-') . '.pdf';
		$pdf->save($fileName);
        if (!file_exists($fileName)) {
        	throw new Kega_Pdf_Exception("Could not create PDF {$fileName}.\n");;
        }

        $remoteFile = basename($fileName);
		if (!@ftp_put($conn_id, $remoteFile, $fileName, FTP_BINARY)) {
			@unlink($fileName);
			throw new Kega_Pdf_Exception("There was a problem while uploading {$fileName} to {$remoteFile}.\n");
		} else {
			@unlink($fileName);
		}

        // close the FTP stream
        @ftp_close($conn_id);
    }
}
