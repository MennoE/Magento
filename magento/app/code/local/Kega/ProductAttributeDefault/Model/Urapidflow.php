<?php
class Kega_ProductAttributeDefault_Model_Urapidflow
{

    const URAPIDFLOW_PROFILE_NAME = 'Product Enricher - Product Update';
    const URAPIDFLOW_PROFILE_NAME_EXTRA = 'Product Enricher - Product Update Extra';

    /**
     * Runs the Product Enricher - Product Update for every file located in urapidflow product enricher dir.
     * The profile files with lower id are runned first
     * The file and store id is set for each file
     *
     */
    public function runUrapidFlowImport()
	{

		ini_set("memory_limit","3024M");


		$logDir = Mage::helper('kega_productattributedefault/urapidflow')->getLogDir();
		$logFilePath = $logDir . DS .'run-'.date('Y-m-d-h-i-s').'.log';

		// we have some echoes so we capture them in a log file
		ob_start();

		try {

			$logContent = ob_get_contents();
            $logContent .= "\n".'Start time: '.Mage::getModel('core/date')->gmtDate('Y-m-d h:i:s');
			$this->writeToLog($logFilePath, $logContent);
			ob_clean();

			$processedFilesBackupDir = Mage::helper('kega_productattributedefault/urapidflow')->getBackupDir();

            $files = $this->getUrapidflowFiles(Mage::helper('kega_productattributedefault/urapidflow')->getDir());

	        $hasRun = false;
			foreach ($files as $fileInfo) {
				$profile = Mage::getModel('urapidflow/profile')->load(self::URAPIDFLOW_PROFILE_NAME, 'title');
				$extraProfile = Mage::getModel('urapidflow/profile')->load(self::URAPIDFLOW_PROFILE_NAME_EXTRA, 'title');

				$filePath = Mage::helper('kega_productattributedefault/urapidflow')->getDir(). DS . $fileInfo['filename'];

				if (is_file($filePath)) {
					echo sprintf('Started URapifFlow processing for file %s', $filePath).PHP_EOL;

                    $updateData = array(
                                'filename' => $fileInfo['filename'],
                                'store_id' => $fileInfo['store_id'],
                                'base_dir' => Mage::helper('kega_productattributedefault/urapidflow')->getDir(),
                            );

					$runProfile = $fileInfo['products_extra'] ? $extraProfile : $profile;
					Mage::helper('urapidflow')->run($runProfile->getId(), $stopIfRunning=true, $updateData);

					echo sprintf('Profile URapifFlow %s was finished', $fileInfo['filename']).PHP_EOL;

					$logContent = ob_get_contents();
					$this->writeToLog($logFilePath, $logContent);
					ob_clean();

					Mage::helper('kega_productattributedefault')->backupFile($filePath, $processedFilesBackupDir);
					echo sprintf('Created Backup processed file %s', $filePath).PHP_EOL;
	                $hasRun = true;
				} else {
					echo sprintf('No %s preprocessed file found: %s', self::URAPIDFLOW_PROFILE_NAME, $filePath).PHP_EOL;
				}

				$logContent = ob_get_contents();
				$this->writeToLog($logFilePath, $logContent);
				ob_clean();

				if (is_file($filePath)) {
					$message = sprintf('The file %s was not processed by Urapidflow (or no backup was created)', $filePath).PHP_EOL;

					echo $message;

					$message .= sprintf('You can also check the log file %s', $logFilePath);
					$this->sendEmail($message);

					$logContent = ob_get_contents();
					$this->writeToLog($logFilePath, $logContent);
					ob_flush();//we want this message to be displayed so it can be caputured by cron
				}

				$logContent = ob_get_contents();
				$this->writeToLog($logFilePath, $logContent);
				ob_clean();
			}

	        if ($hasRun) {
				$logContent = ob_get_contents();
				$this->writeToLog($logFilePath, $logContent);
				ob_clean();

	            echo sprintf("Success: The import was run").PHP_EOL;
				ob_flush();//we want the message displayed  so it can be caputured by cron
	        } else {
	        	echo sprintf("No Errors: no file was processed/imported").PHP_EOL;
				ob_flush();//we want the message displayed  so it can be caputured by cron
	        }

	        echo  sprintf('Generated log file %s', $logFilePath).PHP_EOL;
	        ob_flush();//we want the message displayed  so it can be caputured by cron

	        $logContent = ob_get_contents();
			$this->writeToLog($logFilePath, $logContent);
			ob_clean();


		} catch (Exception $e) {

			$logContent = 'Error occured: ' . $e->getMessage() . PHP_EOL . $e->getTraceAsString();
			$this->writeToLog($logFilePath, $logContent);

			$message = 'Error occured in product enricher product update: ' . $logContent . PHP_EOL . PHP_EOL;
			$message .= sprintf('You can also check the log file %s', $logFilePath).PHP_EOL;

			echo $message;
			ob_flush();//we want the message displayed so it can be caputured by cron

			$this->sendEmail($message);
		}

        $logContent .= "\n".'End time: '.Mage::getModel('core/date')->gmtDate('Y-m-d h:i:s');
        $this->writeToLog($logFilePath, $logContent);

		ob_end_clean();

		return $this;
	}

	private function writeToLog($logFilePath, $logContent) {
		$handle = fopen($logFilePath, 'a');
		fwrite($handle, $logContent.PHP_EOL);
		fclose($handle);
	}


	private function sendEmail($message) {
        $email = Mage::getStoreConfig('kega_productattributedefault/general_settings/notification_email');

        if (empty($email)) {
            return;
        }

        $mail = new Zend_Mail('utf-8');
        $mail->setFrom($email);
        $mail->addTo($email);
        $mail->setBodyText($message);
        $mail->setSubject('Product Enricher Urapidflow Import');
        $result = $mail->send();
	}

    /**
     * Returns an array with files with pattern profile-<number>_store-<number>.txt
     * The array is sorted ASC by profile number
     *
     * @param string directory path where files are located
     * @return array
     */
    public function getUrapidflowFiles($dir)
    {
        $handle = opendir($dir);
        if (!$handle) return array();

        $files = array();
        while (false !== ($entry = readdir($handle))) {
            if ($entry == '.' || $entry == '..') continue;

            if (preg_match('@profile-(\d*)_store-(\d*).txt@', $entry, $matches)) {
                $files[$matches[1].''.$matches[2]] = array(
                    'filename' => $entry,
                    'store_id' => $matches[2],
                    'profile_id' => $matches[1],
					'products_extra' => false,
                );
            }
            elseif (preg_match('@profile_extra-(\d*)_store-(\d*).txt@', $entry, $matches)) {
                $files[$matches[1].''.$matches[2] . '-extra'] = array(
                    'filename' => $entry,
                    'store_id' => $matches[2],
                    'profile_id' => $matches[1],
					'products_extra' => true,
                );
            }
        }

        closedir($handle);

        // sorted asc by profile id
        ksort($files);

        return $files;
    }
}
