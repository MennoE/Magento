<?php
class Kega_Vacancytype_Block_Adminhtml_Vacancytype extends Mage_Adminhtml_Block_Widget_Grid_Container
{
	public function __construct()
	{
		$this->_controller = 'adminhtml_vacancytype';
		$this->_blockGroup = 'vacancytype';
		$this->_headerText = Mage::helper('vacancytype')->__('Item Manager');
		$this->_addButtonLabel = Mage::helper('vacancytype')->__('Add Item');
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