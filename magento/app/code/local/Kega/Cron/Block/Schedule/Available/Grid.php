<?php
class Kega_Cron_Block_Schedule_Available_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct()
    {
        parent::__construct();

        $this->setTemplate('kega_cron/available.phtml');
    }

	public function getAvailableCollection()
    {
    	return Mage::getResourceModel('cron/schedule')->getAvailableCollection();
    }
}