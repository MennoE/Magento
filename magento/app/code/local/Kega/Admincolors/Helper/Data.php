<?php
class Kega_Admincolors_Helper_Data extends Mage_Core_Helper_Abstract
{
	/**
	 * Get admincolors message
	 *
	 * @param void
	 * @return String
	 */
	public function getMessage()
	{
		return Mage::app()->getStore()->getConfig('general/admincolors/message');
	}

	/**
	 * Get admincolors status
	 *
	 * @param void
	 * @return String
	 */
	public function getStatus()
	{
		return Mage::app()->getStore()->getConfig('general/admincolors/status');
	}
	
	/**
	 * Get admincolors store logo
	 *
	 * @param void
	 * @return String
	 */
	public function getStoreLogo()
	{
		return Mage::app()->getStore()->getConfig('general/admincolors/store_logo');
	}
}
