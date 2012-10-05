<?php
/**
 * cronjob available task collection model
 *
 * @category   Kega
 * @package    Kega_Cron
 */
class Kega_Cron_Model_Mysql4_Schedule extends Mage_Cron_Model_Mysql4_Schedule
{
    /**
     * Retrieve available cron schedule task collection array
     *
     * @return array
     */
    public function getAvailableCollection()
    {
        $tasks = array();
        $tasksList = Mage::helper('cron')->getCronTaskNames();

        $lastRunList = $this->_getLastRunList();
        $nextRunList = $this->_getNextRunList();
        foreach ($tasksList as $taskName){
        	$taskData = array('job_code' => $taskName);
        	$taskData['last_run_at'] = isset($lastRunList[$taskName]) ? $lastRunList[$taskName] : null;
        	$taskData['next_run_at'] = isset($nextRunList[$taskName]) ? $nextRunList[$taskName] : null;
        	$tasks[] = $this->_prepareObject($taskData);
        }

        return $tasks;
    }

    /**
     * Retrieve the last task run data
     *
     * @return array
     */
    protected function _getLastRunList()
    {
    	$result = array();
		$select = $this->_getReadAdapter()->select()
            ->from(array('main_table' => $this->getMainTable()))
            ->where('main_table.finished_at != 0')
            ->group('main_table.job_code')
            ->order(array('main_table.finished_at desc'))
            ;
        $query = $this->_getReadAdapter()->query($select);

        while ($row = $query->fetch()) {
        	$result[$row['job_code']] = $row['finished_at'];
        }

        return $result;
    }

    /**
     * Retrieve the next task run data collection
     *
     * @return array
     */
    protected function _getNextRunList()
    {
    	$result = array();
		$select = $this->_getReadAdapter()->select()
            ->from(array('main_table' => $this->getMainTable()))
            ->where('main_table.scheduled_at != 0 AND main_table.status = "'
            	. Mage_Cron_Model_Schedule::STATUS_PENDING
            	. '"')// AND main_table.scheduled_at > UTC_TIMESTAMP()')
            ->order(array('main_table.scheduled_at asc'))
            ;

        $query = $this->_getReadAdapter()->query($select);
        while ($row = $query->fetch()) {
        	if (isset($result[$row['job_code']])) {
        		continue;
        	}
        	$result[$row['job_code']] = $row['scheduled_at'];
        }

        return $result;
    }

    /**
     * Prepare task object
     *
     * @param array $data
     * @return Varien_Object
     */
    protected function _prepareObject(array $data)
    {
        $object = new Varien_Object();
        $object->setJobCode($data['job_code']);
        $object->setLastRunAt($data['last_run_at']);
        $object->setNextRunAt($data['next_run_at']);

        return $object;
    }
}