<?php
class Kega_Cron_Block_Schedule_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Initialize Grid block
     *
     */
    public function __construct()
    {
        parent::__construct();

        $this->setId('cronGrid');
        $this->setSaveParametersInSession(true);
        $this->setDefaultSort('schedule_id');
        $this->setDefaultDir('DESC');
    }

    /**
     * Prepare collection for grid
     *
     * @return Kega_Cron_Block_Schedule_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('cron/schedule')
            ->getCollection();

        //change the date value to null when it is 0000-00-00 00:00:00
        $collection->getSelect()
            ->from(null, array('IF(`main_table`.executed_at = \'0000-00-00 00:00:00\', null, `main_table`.executed_at) as valid_executed_at'))
            ->from(null, array('IF(`main_table`.finished_at = \'0000-00-00 00:00:00\', null, `main_table`.finished_at) as valid_finished_at'))
            ;

       $this->setCollection($collection);
        parent::_prepareCollection();

        return $this;
    }

    /**
     * Prepare columns
     *
     * @return Kega_Cron_Block_Schedule_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('schedule_id', array(
            'header'    => Mage::helper('cron')->__('ID'),
            'align'     => 'right',
            //'type'      => 'number',
            'filter'	=> false,
            'index'     => 'schedule_id',
            'width'     => '40px',
        ));

        $this->addColumn('job_code', array(
            'header'    => Mage::helper('cron')->__('Job Code'),
            'index'     => 'job_code',
			'type'      => 'options',
			'options'   => Mage::helper('cron')->getCronTaskNames(),
        ));

		$this->addColumn('status', array(
			'header'    => Mage::helper('cron')->__('Status'),
			'index'     => 'status',
			'width'     => '80px',
			'type'      => 'options',
			'options'   => array(
				Mage_Cron_Model_Schedule::STATUS_ERROR=>Mage_Cron_Model_Schedule::STATUS_ERROR,
				Mage_Cron_Model_Schedule::STATUS_MISSED=>Mage_Cron_Model_Schedule::STATUS_MISSED,
				Mage_Cron_Model_Schedule::STATUS_PENDING=>Mage_Cron_Model_Schedule::STATUS_PENDING,
				Mage_Cron_Model_Schedule::STATUS_RUNNING=>Mage_Cron_Model_Schedule::STATUS_RUNNING,
				Mage_Cron_Model_Schedule::STATUS_SUCCESS=>Mage_Cron_Model_Schedule::STATUS_SUCCESS
			),
		));

        $this->addColumn('messages', array(
            'header'    => Mage::helper('cron')->__('Messages'),
            'default'   => Mage::helper('cron')->__('n/a'),
            'index'     => 'messages'
        ));

        $this->addColumn('created_at', array(
            'header'    => Mage::helper('cron')->__('Created At'),
            'align'     => 'left',
            'width'     => '150px',
            'type'      => 'datetime',
            'index'     =>'created_at'
        ));

        $this->addColumn('scheduled_at', array(
            'header'    => Mage::helper('cron')->__('Scheduled At'),
            'align'     => 'left',
            'width'     => '150px',
            'type'      => 'datetime',
        	'type'      => 'datetime',
            'index'     =>'scheduled_at'
        ));

        $this->addColumn('valid_executed_at', array(
            'header'    => Mage::helper('cron')->__('Executed At'),
            'align'     => 'left',
            'width'     => '150px',
            'filter'    => false,
        	'type'      => 'datetime',
        	'default'   => Mage::helper('cron')->__('n/a'),
            'index'     => 'valid_executed_at'
        ));

        $this->addColumn('valid_finished_at', array(
            'header'    => Mage::helper('cron')->__('Finished At'),
            'align'     => 'left',
            'width'     => '150px',
            'filter'    => false,
            'type'      => 'datetime',
            'default'   => Mage::helper('cron')->__('n/a'),
            'index'     => 'valid_finished_at',
        ));

        return parent::_prepareColumns();
    }

}