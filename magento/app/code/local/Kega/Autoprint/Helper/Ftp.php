<?php
class Kega_Autoprint_Helper_Ftp extends Mage_Core_Helper_Abstract
{
	/**
	 * Check if all needed credentials are filled out
	 *
	 * @throws Exception when on of the mandatory credentials is missing
	 * @param array $credentials
	 * @return void
	 */
	private function _validateCredentials($credentials)
	{
		$whitelist = array(
			'host',
			'user',
			'password',
		);

		foreach($whitelist as $key) {
			if (!empty($credentials[$key])) {
				continue;
			}
			Mage::throwException(sprintf(
				'Cant connect to FTP: missing credential: "%s"',
				$key
			));
		}
	}

	/**
	 * Upload provided content as a file to the server (in the specified sub-dir).
	 *
	 * @param array  $credentials
	 * @param string $serverDir
	 * @param string $fileName
	 * @param string $content
	 * @return boolean
	 *
	 * @throws Mage_Core_Exception
	 */
	public function uploadContent($credentials, $serverDir, $fileName, $content)
	{
		// Save content into temp file.
		$localFile = '/tmp/' . $fileName;
		file_put_contents($localFile, $content);

		try {
			$this->uploadFile($credentials, $localFile, $serverDir, $fileName);
		} catch (Mage_Core_Exception $e) {
			// Delete temp content.
			@unlink($localFile);
			throw $e;
		}

		// Delete temp content.
		@unlink($localFile);

		return true;
	}

	/**
	 * Upload a file to the server (in the specified sub-dir).
	 *
	 * @param array  $credentials
	 * @param string $localFile
	 * @param string $serverDir
	 * @param string $fileName
	 * @return boolean
	 *
	 * @throws Mage_Core_Exception
	 */
	public function uploadFile($credentials, $localFile, $serverDir, $fileName)
	{
		$connection = $this->_connect($credentials);
		if ($this->_changeDir($connection, $serverDir)) {
			// Try to upload the content to the FTP server.
			if (!@ftp_put($connection, $fileName, $localFile, FTP_BINARY)) {
				Mage::throwException(sprintf(
					'There was a problem while uploading "%s" to "%s"',
					$localFile, $serverDir . DS . $fileName
				));
			}

			return true;
		}
	}

	/**
	 * Connect (and login) to FTP server.
	 *
	 * @param array $credentials
	 */
	protected function _connect($credentials)
	{
		$this->_validateCredentials($credentials);

		$connection = ftp_connect($credentials['host']);
		if (!$connection) {
			Mage::throwException(sprintf(
				'Could not connect to host ""',
				$credentials['host']
			));
		}

		$login_result = ftp_login($connection, $credentials['user'], $credentials['password']);

		if ((!$connection) || (!$login_result)) {
            Mage::throwException(sprintf(
				'Could not login to host "%s" with username "%s"',
				$credentials['host'], $credentials['user']
			));
			return false;
		}

		ftp_pasv($connection, $credentials['passive']);

		return $connection;
	}

	/**
	 * Change to the specified (sub or sub-sub-sub) dir on the FTP server.
	 * If sub-dir does not exists, we try to create it.
	 *
	 * @param FTP Buffer $connection
	 * @param string $serverDir
	 */
	protected function _changeDir($connection, $serverDir)
	{
		$dir = trim($serverDir, DS);
		if (empty($dir)) {
			return true;
		}

		// Make sure that the given dir exists.
		foreach (explode(DS, $serverDir) as $createDir) {
			@ftp_mkdir($connection, $createDir);
			@ftp_chdir($connection, $createDir);
		}

		// Check if current path is the path we wanted to create.
		$pwd = ftp_pwd($connection);
		if (!$pwd || trim($pwd, DS) != $serverDir) {
			Mage::throwException(sprintf(
				'Could not create dir "%s" on the FTP server.',
				"$pwd != $serverDir"
			));
		}

		return true;
	}
}
