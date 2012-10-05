<?php
/**
 * In the system.xml we used this model for providing the available tray options.
 * <source_model>kega_autoprint/system_config_source_tray</source_model>
 *
 */
class Kega_Autoprint_Model_System_Config_Source_Tray
{
	/**
	 * Retreive all tray options.
	 *
	 * @return array
	*/
	public function toOptionArray()
	{
		return array(
			array('value' => 'normal', 'label' => Mage::helper('kega_autoprint')->__('Tray 1 (normal)')),
			array('value' => 'sticker', 'label'=>Mage::helper('kega_autoprint')->__('Tray 2 (sticker)')),
		);
	}
}