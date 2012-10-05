<?php
/**
 * This class is used to cleanup all old files in the directories set in the configuration
 *
 */
class Kega_URapidFlow_Model_Cleanup extends Mage_Core_Model_Abstract
{
	/**
	 * Cleanup all old files in the directories set in the configuration
	 *
	 * @param void
	 * @return void
	 */
	public function cleanupDirectories()
	{
		$rootDirectory = 'urapidflow';
		$directories = unserialize(Mage::getStoreConfig('urapidflow/directories_cleanup/directories'));

		foreach ($directories as $directory) {
			$directory = $rootDirectory . DS . $directory['directory'];
			$deleted = $this->_checkAllFiles($directory);
			if (is_numeric($deleted)) {
				echo 'Files deleted from ' . $directory . ': ' . $deleted . PHP_EOL;
			}
			else {
				echo 'Directory dont exists: ' . $directory . PHP_EOL;
			}
		}
	}

	/**
	 * Check all file ages in the given directory
	 * When a file is too old, delete it
	 *
	 * @param string $directory Directory name
	 * @return boolean|int False if directory dont exists or number of files removed
	 */
	private function _checkAllFiles($directory)
	{
		$directory = Mage::getBaseDir('var') . DS . $directory;

		$noFile = array('.', '..');
		$files = array();
		$deleted = 0;

		if (file_exists($directory)) {
			$dirPath  = opendir($directory);
		}
		else {
			return false;
		}

		while (false !== ($filename = readdir($dirPath))) {
		    if (!in_array($filename, $noFile) && !is_dir($filename)) {
		    	$file = $directory . DS . $filename;
		    	if ($this->_checkFileAge($file)) {
		    		$deleted++;
		    	}
		    }
		}

		return $deleted;
	}

	/**
	 * Check if file date exceeds the age set in the configuration
	 * Return true if file is removed
	 *
	 * @param string $file Full path and filename
	 * @return boolean
	 */
	private function _checkFileAge($file)
	{
		$weeks = Mage::getStoreConfig('urapidflow/directories_cleanup/file_age');
		$maxAge = strtotime('-' . $weeks . ' week');
		$fileAge = filemtime($file);

		if ($fileAge <= $maxAge) {
			// Delete file
			$this->_deleteFile($file);
			return true;
		}

		return false;
	}

	/**
	 * Delete file or directory
	 *
	 * @param string $file Full path and filename
	 * @return void
	 */
	private function _deleteFile($object)
	{
		$noFile = array('.', '..');

		// Remove directory
		if (is_dir($object)) {
			$files = scandir($object);

			foreach ($files as $file) {
				if (!in_array($file, $noFile)) {
					if (filetype($object . DS . $file) == 'dir') {
						$this->_deleteFile($object . DS . $file);
					}
					else {
						unlink($object . DS . $file);
					}
				}
			}

			reset($files);
			rmdir($object);
		}
		// Remove file
		else {
			unlink($object);
		}
	}

}