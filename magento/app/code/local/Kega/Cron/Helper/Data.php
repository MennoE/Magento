<?php
/**
 * Cron data helper
 */
class Kega_Cron_Helper_Data extends Mage_Cron_Helper_Data
{

	/**
     * Retrieve the available cron schedule task collection fron configs (which can be run by cron)
     *
     * @return array
     */
	public function getCronTaskNames()
	{
    	$result = array();

		/**
		 *  global crontab jobs
		 */
		$config = Mage::getConfig()->getNode('crontab/jobs');
		if ($config instanceof Mage_Core_Model_Config_Element) {
			$config = $config->children();
			foreach ($config as $jobConfig) {
				if ($jobConfig->schedule->config_path || $jobConfig->schedule->cron_expr) {
					$result[$jobConfig->getName()] = $jobConfig->getName();
				}
			}
		}

		return $result;
	}
}