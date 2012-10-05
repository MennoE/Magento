<?php
class Kega_Cron_ScheduleController extends Mage_Adminhtml_Controller_Action
{

	/**
     * Init actions
     *
     * @return Kega_Cron_ScheduleController
     */
    protected function _initAction()
    {
        // load layout, set active menu and breadcrumbs
        $this->loadLayout()
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('System'), Mage::helper('adminhtml')->__('System'))
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Configuration'), Mage::helper('adminhtml')->__('Configuration'))
        ;
        return $this;
    }


    /**
     * View action
     */
    public function viewAction()
    {
        $this->_initAction()
            ->_setActiveMenu('system/config/cron/')
            ->_addBreadcrumb(Mage::helper('cron')->__('Cron Schedule'), Mage::helper('cron')->__('Cron Schedule'))
            ->_addContent($this->getLayout()->createBlock('cron/schedule'))
            ->renderLayout();
    }

    /**
     * Schedule action
     * try schedule the task by job_code
     *
     */
	public function scheduleAction()
	{
		if ($jobCode = $this->getRequest()->getParam('job_code', null)) {
			if (Mage::getConfig()->getNode('crontab/jobs/' . $jobCode)) {
				$date = Mage::app()->getLocale()->date(time(), null, null, false);
				$sDate = Mage::app()->getLocale()->date(time() + 120, null, null, false);

				$schedule = Mage::getModel('cron/schedule');
				$schedule->setJobCode($jobCode)
					->setCronExpr('* * * * *')
					->setStatus(Mage_Cron_Model_Schedule::STATUS_PENDING)
					->setCreatedAt($date->toString('YYYY-MM-dd HH:mm:ss'))
					->setScheduledAt($sDate->toString('YYYY-MM-dd HH:mm:00')) // Run at next minute.
					->unsScheduleId()
					->save();

				$this->_getSession()->addSuccess(Mage::helper('cron')->__('Task "%s" has been successfully added for next run.', $jobCode));
			} else {
				Mage::getSingleton('adminhtml/session')->addError(Mage::helper('cron')->__('Task "%s" does not exists.', $jobCode));
			}
		}

		$this->_redirect('*/*/view');
	}

}