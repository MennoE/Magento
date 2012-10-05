<?php

class Kega_Vacancy_Block_Adminhtml_Vacancy_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('vacancyGrid');
      $this->setDefaultSort('vacancy_id');
      $this->setDefaultDir('ASC');
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('vacancy/vacancy')->getCollection();
      $storeId = $this->getRequest()->getParam('store', 0);
      $collection->setStoreId($storeId);
      $collection->addStoreView();
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
      $this->addColumn('vacancy_id', array(
          'header'    => Mage::helper('vacancy')->__('ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'vacancy_id',
      ));

      $resource = Mage::getSingleton('core/resource');
      $read = $resource->getConnection('core_read');
      $select = $read->select();
      $select->from(array('vt' => 'vacancytype'),
                      array('id' => 'vacancytype_id', 'title'));

	  $types = array();
	  $get_types = $read->fetchAll($select);
      foreach($get_types as $type) {
		  $types[$type['id']] = $type['title'];
      }

      $this->addColumn('vacancytype_id', array(
			'header'    => Mage::helper('vacancy')->__('Type'),
			'width'     => '250px',
			'index'     => 'vacancytype_id',
			'type'      => 'options',
 		    'options'   => $types,
      ));

	  $stores = array();
      $get_stores = Mage::getModel('store/store')->getCollection()
			->addAttributeToSelect('name')
			->addAttributeToSelect('shop_id');
	  foreach($get_stores as $store) {
		  $stores[$store->getId()] = $store->getName();
 	  }
	  $this->addColumn('shop_id', array(
			'header'    => Mage::helper('vacancy')->__('Store'),
			'width'     => '450px',
			'index'     => 'shop_id',
 		    'type'      => 'options',
 		    'options'   => $stores,
      ));

      $this->addColumn('title', array(
          'header'    => Mage::helper('vacancy')->__('Title'),
          'align'     =>'left',
          'index'     => 'title',
      ));

      $this->addColumn('number', array(
          'header'    => Mage::helper('vacancy')->__('Number'),
          'align'     =>'left',
          'index'     => 'number',
      ));
/*
	$this->addColumn('text', array(
          'header'    => Mage::helper('vacancy')->__('Text'),
          'align'     =>'left',
          'index'     => 'text',
      ));
*/




      $this->addColumn('status', array(
          'header'    => Mage::helper('vacancy')->__('Status'),
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
                'header'    =>  Mage::helper('vacancy')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('vacancy')->__('Edit'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));

//		$this->addExportType('*/*/exportCsv', Mage::helper('vacancy')->__('CSV'));
//		$this->addExportType('*/*/exportXml', Mage::helper('vacancy')->__('XML'));

      return parent::_prepareColumns();
  }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('vacancy_id');
        $this->getMassactionBlock()->setFormFieldName('vacancy');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('vacancy')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('vacancy')->__('Are you sure?')
        ));

        $statuses = Mage::getSingleton('vacancy/status')->getOptionArray();

        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
             'label'=> Mage::helper('vacancy')->__('Change status'),
             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('vacancy')->__('Status'),
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