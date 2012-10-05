<?php

abstract class Kega_Init_Model_Adminhtml_System_Config_Backend_Frequency_Abstract extends Mage_Core_Model_Config_Data
{
	protected $_cronExpressionConfigPath; // 'crontab/jobs/kega_reindex_catalog_data/schedule/cron_expr';
	protected $_cronModelConfigPath; // 'crontab/jobs/kega_reindex_catalog_data/run/model';

    protected $_enabledDataPath; // 'groups/reindexing/fields/enabled/value';
	protected $_frequencyDataPath; // 'groups/reindexing/fields/frequency/value';
	protected $_timeDataPath; // 'groups/reindexing/fields/time/value';

	/**
	 * Force extends on this class to have a _setPaths() method.
	 *
	 * This method should set all protected variables in this class:
	 *
	 * protected function _setPaths() {
	 * 	$this->_enabledDataPath = 'groups/reindexing/fields/enabled/value';
	 * 	$this->_timeDataPath = 'groups/reindexing/fields/time/value';
	 *  ..
	 * }
	 *
	 */
	abstract protected function _setPaths();

	/**
	 * Cron settings after save
	 *
	 * @return Mage_Adminhtml_Model_System_Config_Backend_Log_Cron
	 */
	protected function _afterSave()
	{
		$this->_setPaths();

		$enabled 	= $this->getData($this->_enabledDataPath);
		$frequency 	= $this->getData($this->_frequencyDataPath);
		$time 		= $this->getData($this->_timeDataPath);

		$frequencyDaily     = Mage_Adminhtml_Model_System_Config_Source_Cron_Frequency::CRON_DAILY;
		$frequencyWeekly    = Mage_Adminhtml_Model_System_Config_Source_Cron_Frequency::CRON_WEEKLY;
		$frequencyMonthly   = Mage_Adminhtml_Model_System_Config_Source_Cron_Frequency::CRON_MONTHLY;

		if ($enabled) {
			$cronDayOfWeek = date('N');
			$cronExprArray = array(
				intval($time[1]),                                   # Minute
				intval($time[0]),                                   # Hour
				($frequncy == $frequencyMonthly) ? '1' : '*',       # Day of the Month
				'*',                                                # Month of the Year
				($frequncy == $frequencyWeekly) ? '1' : '*',        # Day of the Week
			);
			$cronExprString = join(' ', $cronExprArray);
		}
		else {
			$cronExprString = '';
		}

		try {
			Mage::getModel('core/config_data')
				->load($this->_cronExpressionConfigPath, 'path')
				->setValue($cronExprString)
				->setPath($this->_cronExpressionConfigPath)
				->save();

			Mage::getModel('core/config_data')
				->load($this->_cronModelConfigPath, 'path')
				->setValue((string) Mage::getConfig()->getNode($this->_cronModelConfigPath))
				->setPath($this->_cronModelConfigPath)
				->save();
		}
		catch (Exception $e) {
			Mage::throwException('Unable to save the cron expression.');
		}
	}
}