<?php
class Kega_Cron_Model_Observer extends Mage_Cron_Model_Observer
{
	/**
     * Process cron queue
     * Geterate tasks schedule
     * Cleanup tasks schedule
     *
     * @param Varien_Event_Observer $observer
     */
    public function dispatch($observer)
    {
        $schedules = $this->getPendingSchedules();
        $scheduleLifetime = Mage::getStoreConfig(self::XML_PATH_SCHEDULE_LIFETIME) * 60;
        $now = time();
        $jobsRoot = Mage::getConfig()->getNode('crontab/jobs');

        foreach ($schedules->getIterator() as $schedule) {
            $jobConfig = $jobsRoot->{$schedule->getJobCode()};
            if (!$jobConfig || !$jobConfig->run) {
                continue;
            }

            $runConfig = $jobConfig->run;
            $time = strtotime($schedule->getScheduledAt());
            if ($time > $now) {
                continue;
            }
            try {
            	if ($time < $now - $scheduleLifetime) {
                    $schedule->setStatus(Mage_Cron_Model_Schedule::STATUS_MISSED)
                    	->setMessages(Mage::helper('cron')->__('Too late for the schedule'));
                } else {
	                if ($runConfig->model) {
	                    if (!preg_match(self::REGEX_RUN_MODEL, (string)$runConfig->model, $run)) {
	                        Mage::throwException(Mage::helper('cron')->__('Invalid model/method definition, expecting "model/class::method".'));
	                    }
	                    if (!($model = Mage::getModel($run[1])) || !method_exists($model, $run[2])) {
	                        Mage::throwException(Mage::helper('cron')->__('Invalid callback: %s::%s does not exist', $run[1], $run[2]));
	                    }
	                    $callback = array($model, $run[2]);
	                    $arguments = array($schedule);
	                }
	                if (empty($callback)) {
	                    Mage::throwException(Mage::helper('cron')->__('No callbacks found'));
	                }
	
	                if (!$schedule->tryLockJob()) {
	                    // another cron started this job intermittently, so skip it
	                    continue;
	                }
	                $schedule->setStatus(Mage_Cron_Model_Schedule::STATUS_RUNNING)
	                	->setExecutedAt(strftime('%Y-%m-%d %H:%M:%S', time()))
	                    ->save();
	
	                // Kega adjustment: Use output buffering to log messages into cron schedule table.
					ob_start();
	                call_user_func_array($callback, $arguments);
	                $message = ob_get_contents();
					ob_end_clean();
	
	                $schedule->setStatus(Mage_Cron_Model_Schedule::STATUS_SUCCESS)
	                    ->setFinishedAt(strftime('%Y-%m-%d %H:%M:%S', time()));
	
	                if (!empty($message)) {
	                	$schedule->setMessages($message);
	                }
                }
            } catch (Exception $e) {
            	$message = 'Exception in ' . $e->getFile() . ' on line ' . $e->getLine() . PHP_EOL . $e->getMessage();
            	if (Mage::getStoreConfig('system/cron/exception_backtrace')) {
            		$message .= PHP_EOL . $e->getTraceAsString();
            	}

                $schedule->setStatus(Mage_Cron_Model_Schedule::STATUS_ERROR)
                    ->setMessages($message);

                $email = Mage::getStoreConfig('system/cron/exception_email');
                if (!empty($email)) {
                	$message = 'Website: ' . Mage::getStoreConfig('web/unsecure/base_url') . PHP_EOL . PHP_EOL . $message;
	                $mail = new Zend_Mail();
					$mail->setBodyText($message);
					$mail->setBodyHtml('<pre>' . $message . '</pre>');
					$mail->setFrom(Mage::getStoreConfig('trans_email/ident_general/email'));
					$mail->addTo($email);
					$mail->setSubject("Exception occured when running cron task '{$schedule->getJobCode()}'.");
					$mail->send();
                }
            }
            $schedule->save();
        }

        $this->generate();
        $this->cleanup();
    }
}