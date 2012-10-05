<?php
class Kega_Faq_Block_Adminhtml_Category extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_category';
    $this->_blockGroup = 'faq';
    $this->_headerText = Mage::helper('faq')->__('Category Manager');
    $this->_addButtonLabel = Mage::helper('faq')->__('Add Category');
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