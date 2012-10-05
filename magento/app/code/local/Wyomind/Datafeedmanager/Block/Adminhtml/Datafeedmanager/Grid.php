<?php

class Wyomind_Datafeedmanager_Block_Adminhtml_Datafeedmanager_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct()
	{
		parent::__construct();
		$this->setId('datafeedmanagerGrid');
		$this->setDefaultSort('datafeedmanager_id');
		$this->setDefaultDir('ASC');
		$this->setSaveParametersInSession(true);
	}

	protected function _prepareCollection()
	{
		$collection = Mage::getModel('datafeedmanager/datafeedmanager')->getCollection();
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}

	protected function _prepareColumns()
	{
		$this->addColumn('feed_id',
		array(
                     'header'    => Mage::helper('datafeedmanager')->__('ID'),
                    'align' =>'right',
                    'width' => '50px',
                    'index' => 'feed_id',
              		'filter'    => false,
		));

		$this->addColumn('feed_name',
		array(
                    'header'    => Mage::helper('datafeedmanager')->__('Filename'),
                    'align' =>'left',
                    'index' => 'feed_name',
		));

		$this->addColumn('feed_type',
		array(
	          'header'    => Mage::helper('datafeedmanager')->__('File format'),
	          'align'     =>'left',
	          'index'     => 'feed_type',
	       	   'type'      => 'options',
	          'options'   => array(
		1 => 'xml',
		2 => 'txt',
		3 => 'csv',
		),
	         'renderer'  => 'Wyomind_Datafeedmanager_Block_Adminhtml_Datafeedmanager_Renderer_Type',
		));
		 
		$this->addColumn('feed_path', array(
                   'header'    => Mage::helper('datafeedmanager')->__('File path'),
                    'align' =>'left',
                    'index' => 'feed_path',
		));

		$this->addColumn('feed_link', array(
                    'header'    => Mage::helper('datafeedmanager')->__('File link'),
                     'align' =>'left',
                     'index' => 'feed_link',
        			 'type'      => 'options',
        			 'filter'    => false,
					 'renderer'  => 'Wyomind_Datafeedmanager_Block_Adminhtml_Datafeedmanager_Renderer_Link',
		));
		$this->addColumn('feed_updated_at', array(
                    'header'    => Mage::helper('datafeedmanager')->__('Last update'),
                     'align' =>'left',
                     'index' => 'feed_updated_at',
					 'type'      => 'datetime',
		 
		));
		if (!Mage::app()->isSingleStoreMode()) {
			$this->addColumn('store_id', array(
                'header'    => Mage::helper('datafeedmanager')->__('Store View'),
                'index'     => 'store_id',
                'type'      => 'store',
			));
		}

		$this->addColumn('feed_status', array(
	          'header'    => Mage::helper('datafeedmanager')->__('Status'),
	          'align'     => 'left',
	          'width'     => '80px',
	          'index'     => 'feed_status',
	          'type'      => 'options',
	          'options'   => array(
		1 => Mage::helper('datafeedmanager')->__('Enabled'),
		2 => Mage::helper('datafeedmanager')->__('Disabled'),
		),
	          'renderer'  => 'Wyomind_Datafeedmanager_Block_Adminhtml_Datafeedmanager_Renderer_Status',
		));

		$this->addColumn('action', array(
                     'header'    => Mage::helper('datafeedmanager')->__('Action'),
                     'align' =>'left',
                     'index' => 'action',
				     'filter'   => false,
			  		 'sortable' => false,
					 'renderer'  => 'Wyomind_Datafeedmanager_Block_Adminhtml_Datafeedmanager_Renderer_Action',
		));
		 
		 
		return parent::_prepareColumns();
	}

}