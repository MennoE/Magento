<?php

class Kega_Faq_Block_Adminhtml_Question_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setStoreId($this->getRequest()->getParam('store'));
      $this->setId('faqGrid');
      $this->setDefaultSort('question_id');
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
			$collection = Mage::getModel('faq/question')->getCollection();
		} else {
			$collection = Mage::getModel('faq/question')->getCollection()
														->addStoreFilter($this->getStoreId());
		}
		$this->setCollection($collection);
		
		// add storeview names to collection
  		foreach ($collection as $item)
		{
			$storeViewNames = Mage::helper('faq')->getStoreViewNames($item->getCategoryId());
			$item['store_id'] = $storeViewNames;
		}	
		
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
      $this->addColumn('question_id', array(
          'header'    => Mage::helper('faq')->__('ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'question_id',
      ));

      $this->addColumn('question', array(
          'header'    => Mage::helper('faq')->__('Question'),
          'align'     =>'left',
          'index'     => 'question',
      ));

	  $category_values = array();
	  foreach(Mage::getModel('faq/category')->getCollection()->load() as $category)
	  {
		$category_values[$category->categoryId] = Mage::helper('faq')->__($category->name);
	  }

      $this->addColumn('category_id', array(
          'header'    => Mage::helper('faq')->__('Category'),
          'align'     => 'left',
          'width'     => '80px',
          'index'     => 'category_id',
          'type'      => 'options',
          'options'   => $category_values
      ));

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

      $this->addColumn('Storeviews', array(
          'header'    => Mage::helper('faq')->__('Storeview'),
          'align'     =>'left',
          'index'     => 'store_id',
          'width'     => '250px',
		  'filter'    => false,
	      'sortable'  => false,
      ));        
        
		$this->addExportType('*/*/exportCsv', Mage::helper('faq')->__('CSV'));
		$this->addExportType('*/*/exportXml', Mage::helper('faq')->__('XML'));

      return parent::_prepareColumns();
  }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('question_id');
        $this->getMassactionBlock()->setFormFieldName('faq');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('faq')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('faq')->__('Are you sure?')
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