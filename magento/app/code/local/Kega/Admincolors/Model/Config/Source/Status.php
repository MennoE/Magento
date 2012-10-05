<?php
/**
 * Statusses for website.
 *
 */
class Kega_Admincolors_Model_Config_Source_Status
{

    /**
     * Get available statusses
     *
     * @param void
     * @return array
     */
    public function toOptionArray()
    {
		return array(
			array('value' => 'dev', 'label' => 'Development'),
			array('value' => 'staging', 'label' => 'Staging'),
			array('value' => 'stable', 'label' => 'Stable'),
			array('value' => 'live', 'label' => 'Live'),
		);
    }
}
