<?php

class Kega_Vacancytype_Block_Adminhtml_Vacancytype_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('vacancytypeGrid');
      $this->setDefaultSort('vacancytype_id');
      $this->setDefaultDir('ASC');
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('vacancytype/vacancytype')->getCollection();
      $storeId = $this->getRequest()->getParam('store', 0);
      $collection->setStoreId($storeId);
      $collection->addStoreView();
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
      $this->addColumn('vacancytype_id', array(
          'header'    => Mage::helper('vacancytype')->__('ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'vacancytype_id',
      ));

      $this->addColumn('title', array(
          'header'    => Mage::helper('vacancytype')->__('Title'),
          'align'     =>'left',
          'index'     => 'title',
      ));

	  /*
      $this->addColumn('content', array(
			'header'    => Mage::helper('vacancytype')->__('Item Content'),
			'width'     => '150px',
			'index'     => 'content',
      ));
	  */

      $this->addColumn('status', array(
          'header'    => Mage::helper('vacancytype')->__('Status'),
          'align'     => 'left',
          'width'     => '80px',
          'index'     => 'status',
          'type'      => 'options',
          'options'   => array(
              1 => 'Enabled',
              2 => 'Disabled',
          ),
      ));
	  
        $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('vacancytype')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('vacancytype')->__('Edit'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));
		
		$this->addExportType('*/*/exportCsv', Mage::helper('vacancytype')->__('CSV'));
		$this->addExportType('*/*/exportXml', Mage::helper('vacancytype')->__('XML'));
	  
      return parent::_prepareColumns();
  }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('vacancytype_id');
        $this->getMassactionBlock()->setFormFieldName('vacancytype');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('vacancytype')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('vacancytype')->__("Are you sure?\n If you delete this vacancy type(s) all vacancies with this type(s) will be deleted too.")
        ));

        $statuses = Mage::getSingleton('vacancytype/status')->getOptionArray();

        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
             'label'=> Mage::helper('vacancytype')->__('Change status'),
             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('vacancytype')->__('Status'),
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