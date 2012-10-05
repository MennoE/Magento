<?php

class Kega_Extraopening_Block_Adminhtml_Extraopening_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('extraopeningGrid');
      $this->setDefaultSort('extraopening_id');
      $this->setDefaultDir('ASC');
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('extraopening/extraopening')->getCollection();
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
      $this->addColumn('extraopening_id', array(
          'header'    => Mage::helper('extraopening')->__('ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'extraopening_id',
      ));

      $this->addColumn('title', array(
          'header'    => Mage::helper('extraopening')->__('Title'),
          'align'     =>'left',
          'index'     => 'title',
      ));

	  /*
      $this->addColumn('content', array(
			'header'    => Mage::helper('extraopening')->__('Item Content'),
			'width'     => '150px',
			'index'     => 'content',
      ));
	  */

	  $this->addColumn('datetime_from', array(
			  'header'    => Mage::helper('store')->__('Date From'),
			  'align'     => 'left',
			  'width'     => '120px',
			  'type'      => 'date',
			  'default'   => '--',
			  'index'     => 'datetime_from',
		  )
	  );

	  $this->addColumn('datetime_to', array(
			  'header'    => Mage::helper('store')->__('Date To'),
			  'align'     => 'left',
			  'width'     => '120px',
			  'type'      => 'date',
			  'default'   => '--',
			  'index'     => 'datetime_to',
		  )
	  );

	  $this->addColumn('status', array(
		'header'    => Mage::helper('extraopening')->__('Status'),
		'align'     => 'left',
		'width'     => '80px',
		'index'     => 'status',
		'type'      => 'options',
		'options'   => Mage::getModel('extraopening/extraopening')->getExtraOpeningStatusesOptions(),
	  ));


        $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('extraopening')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('extraopening')->__('Edit'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));

		//$this->addExportType('*/*/exportCsv', Mage::helper('extraopening')->__('CSV'));
		//$this->addExportType('*/*/exportXml', Mage::helper('extraopening')->__('XML'));

      return parent::_prepareColumns();
  }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('extraopening_id');
        $this->getMassactionBlock()->setFormFieldName('extraopening');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('extraopening')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('extraopening')->__('Are you sure?')
        ));

        $statuses = Mage::getSingleton('extraopening/status')->getOptionArray();

        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
             'label'=> Mage::helper('extraopening')->__('Change status'),
             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('extraopening')->__('Status'),
                         'values' => $statuses
                     )
             )
        ));
        return $this;
    }

  public function getRowUrl($row)
  {
      return $this->getUrl('*/*/edit', array('id' => $row->getId()));
  }

}