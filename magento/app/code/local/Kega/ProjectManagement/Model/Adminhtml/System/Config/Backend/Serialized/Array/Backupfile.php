<?php
class Kega_ProjectManagement_Model_Adminhtml_System_Config_Backend_Serialized_Array_Backupfile
	extends Mage_Adminhtml_Model_System_Config_Backend_Serialized_Array
{
	const XML_CONFIG_PATH = 'kega_projectmanagement/backup_files/file_paths';


	public function getConfigData()
	{
		return Mage::getStoreConfig(self::XML_CONFIG_PATH);
	}

    /**
	 * Gets the backup file paths
	 * array key is the md5 of the file path, array value is the file path
	 * returns false if no config options found
	 *
	 * @return array|bool
	 */
	public function getBackupFiles()
    {
        $backupFilesPath = array();

        $backupFilesConf = $this->getConfigData();

		if (empty($backupFilesConf)) return false;

        $backupFilesConf = @unserialize($backupFilesConf);

        if (is_array($backupFilesConf)) {
            foreach ($backupFilesConf as $backupFilesConf) {

                $dir = DS . trim($backupFilesConf['file_path'], DS). DS;

                if (is_dir(Mage::getBaseDir().$dir)) {

                    if ($dh = opendir(Mage::getBaseDir().$dir)) {
                        while (($file = readdir($dh)) !== false) {
                            if ($file == '.' || $file == '..') continue;
                            $filePath = $dir . $file;

                            $backupFilesPath[md5($filePath)] = $filePath;
                        }
                        closedir($dh);
                    }
                } else {
                    $backupFilesPath[md5($backupFilesConf['file_path'])] = $backupFilesConf['file_path'];
                }

            }

            return $backupFilesPath;
        }
        return false;
    }


    /**
     * Gets real file path
     * @throws Mage_Core_Exception
     *
     * @param string $filePath
     * @return string
     */
    public function getRealFilePath($filePath)
    {
        $io = new Varien_Io_File();
        $realPath = $io->getCleanPath(Mage::getBaseDir() . DS . $filePath);

        // remove trailing / otherwise it throws an error if file has no extension
        $realPath = rtrim($realPath, DS);

        /**
         * Check path is allow
         */
        if (!$io->allowedPath($realPath, Mage::getBaseDir())) {
            $msg = Mage::helper('projectmanagement')->__('File access not allowed, please define correct path');
            Mage::throwException($msg);
        }
        /**
         * Check exist
         */
        if (!$io->fileExists($realPath, false)) {
            $msg = Mage::helper('projectmanagement')->__('File doesn\'t exits, please define correct path %s.', $filePath);
            Mage::throwException($msg);
        }

        return $realPath;
    }

}
