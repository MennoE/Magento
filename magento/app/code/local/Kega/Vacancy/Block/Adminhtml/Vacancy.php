<?php
class Kega_Vacancy_Block_Adminhtml_Vacancy extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_vacancy';
    $this->_blockGroup = 'vacancy';
    $this->_headerText = Mage::helper('vacancy')->__('Item Manager');
    $this->_addButtonLabel = Mage::helper('vacancy')->__('Add Item');
    parent::__construct();
  }

	protected function _prepareLayout()
	{
				
		$this->setChild('store_switcher',
			$this->getLayout()->createBlock('adminhtml/store_switcher')
				->setUseConfirm(false)
				->setSfeitchUrl($this->getUrl('*/*/*/', array('store'=>null)))
		);
		
		
		return parent::_prepareLayout();
	}
	
	public function getGridHtml()
    {
        $html = $this->getChildHtml('store_switcher').$this->getChildHtml('grid');    	
    	return $html;
    }
    
    public function getStoreSwitcherHtml()
    {
    	return $this->getChildHtml('store_switcher');
    }
}