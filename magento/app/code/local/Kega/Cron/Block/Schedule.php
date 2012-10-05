<?php
class Kega_Cron_Block_Schedule extends Mage_Adminhtml_Block_Template
{

    protected $_locale;

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('kega_cron/view.phtml');

    }

    protected function _prepareLayout()
    {

        $this->setChild('availableTask',
                $this->getLayout()->createBlock('cron/schedule_available_grid')
        );

        $this->setChild('taskOverview',
                $this->getLayout()->createBlock('cron/schedule_grid')
        );

        parent::_prepareLayout();
    }
}