<?php
/**
 * @category   Kega
 * @package    Kega_Sysload
 */
class Kega_Sysload_Helper_Data extends Mage_Core_Helper_Abstract
{
	/**
	 * Return the average server load of the last minute.
	 * Can be overwritten using test-load get parameter.
	 *
	 * @param void
	 * @return double
	 */
	public function getLoad()
	{
		if(!empty($_GET['test-load']) && is_numeric($_GET['test-load'])) {
			return $_GET['test-load'];
		}
		$load = sys_getloadavg();
		return $load[0];
	}

	/**
	 * Get all sysload settings from config.
	 *
	 * @param void
	 * @return Array
	 */
	public function getSettings()
	{
		return Mage::app()->getStore()->getConfig('system/kega_sysload');
	}
}
