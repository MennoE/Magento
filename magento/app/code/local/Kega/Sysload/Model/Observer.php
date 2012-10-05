<?php
/**
 * @category   Kega
 * @package    Kega_Sysload
 */
class Kega_Sysload_Model_Observer
{
	/**
	 * Check system load and redirect to temp page if load is high
	 *
	 * @param $observer
	 * @return void
	 */
	public function check($observer)
	{
		try {
			Mage::getModel('kega_sysload/sysload')->check($observer);

		} catch(Exception $e) {
			error_log('Kega_Sysload: Check failed: ' . $e->getMessage());
		}
	}
}