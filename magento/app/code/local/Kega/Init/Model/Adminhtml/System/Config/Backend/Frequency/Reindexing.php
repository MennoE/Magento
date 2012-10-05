<?php
class Kega_Init_Model_Adminhtml_System_Config_Backend_Frequency_Reindexing extends Kega_Init_Model_Adminhtml_System_Config_Backend_Frequency_Abstract
{
	/**
	 * Set paths
	 * @see Kega_Init_Model_Adminhtml_System_Config_Backend_Frequency_Abstract::_setPaths()
	 */
	protected function _setPaths()
	{
		$this->_cronExpressionConfigPath = 'crontab/jobs/kega_reindex_catalog_data/schedule/cron_expr';
		$this->_cronModelConfigPath = 'crontab/jobs/kega_reindex_catalog_data/run/model';

	    $this->_enabledDataPath = 'groups/reindexing/fields/enabled/value';
		$this->_frequencyDataPath = 'groups/reindexing/fields/frequency/value';
		$this->_timeDataPath = 'groups/reindexing/fields/time/value';
	}
}