<?php

class Kega_Faq_Block_Adminhtml_Category_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setStoreId($this->getRequest()->getParam('store'));
      $this->setId('faqGrid');
      $this->setDefaultSort('category_id');
      $this->setDefaultDir('ASC');
      $this->setSaveParametersInSession(true);
  }
  
	public function setStoreId($storeId)
    {
    	$this->storeId = $storeId;
    	return $this;
    }
    
	public function getStoreId()
    {
    	return $this->storeId;
    }
    
	protected function _prepareCollection()
	{
		if ($this->getStoreId() === null) {
			$collection = Mage::getModel('faq/category')->getCollection();
		} else {
			$collection = Mage::getModel('faq/category')->getCollection()
														->addStoreFilter($this->getStoreId());
		}
		$this->setCollection($collection);
		
		// add storeview names to collection
		foreach ($collection as $item)
		{
			$storeViewNames = Mage::helper('faq')->getStoreViewNames($item->getCategoryId());
			$item->setStoreId($storeViewNames);
		}
		return parent::_prepareCollection();
	}

  protected function _prepareColumns()
  {
      $this->addColumn('category_id', array(
          'header'    => Mage::helper('faq')->__('ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'category_id',
      ));

      $this->addColumn('name', array(
          'header'    => Mage::helper('faq')->__('Name'),
          'align'     =>'left',
          'index'     => 'name',
      ));
      
      $this->addColumn('Storeviews', array(
          'header'    => Mage::helper('faq')->__('Storeview'),
          'align'     =>'left',
          'index'     => 'store_id',
          'width'     => '250px',
		  'filter'    => false,
	      'sortable'  => false,
      ));

/*      $this->addColumn('order', array(
          'header'    => Mage::helper('faq')->__('Order'),
          'align'     =>'left',
          'index'     => 'order',
      ));*/

/*      $this->addColumn('status', array(
          'header'    => Mage::helper('faq')->__('Status'),
          'align'     => 'left',
          'width'     => '80px',
          'index'     => 'status',
          'type'      => 'options',
          'options'   => array(
              1 => 'Enabled',
              2 => 'Disabled',
          ),
      ));*/

        $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('faq')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('faq')->__('Edit'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));

		$this->addExportType('*/*/exportCsv', Mage::helper('faq')->__('CSV'));
		$this->addExportType('*/*/exportXml', Mage::helper('faq')->__('XML'));

      return parent::_prepareColumns();
  }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('category_id');
        $this->getMassactionBlock()->setFormFieldName('faq');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('faq')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('faq')->__('Are you sure?')
        ));

        $statuses = Mage::getSingleton('faq/status')->getOptionArray();

        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
             'label'=> Mage::helper('faq')->__('Change status'),
             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('faq')->__('Status'),
                         'values' => $statuses
                     )
             )
        ));
        return $this;
    }

  public function getRowUrl($row)
  {
      return $this->getUrl('*/*/edit', array(
			'store'=>$this->getRequest()->getParam('store'),
			'id'=>$row->getId())
		);
  }

}