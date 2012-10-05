<?php
/**
 * @category   Kega
 * @package    Kega_Sysload
 */
class Kega_Sysload_Model_Backend_Load extends Mage_Core_Model_Config_Data
{
	public function save()
	{
		if(!is_numeric($this->getValue())) {
			Mage::throwException(
				Mage::helper('kega_sysload')->__('Load values (%s) should be numeric.', $this->getField())
			);
			return;
		}
		return parent::save();
	}
}