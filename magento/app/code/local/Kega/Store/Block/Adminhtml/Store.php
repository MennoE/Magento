<?php
class Kega_Store_Block_Adminhtml_Store extends Mage_Adminhtml_Block_Template
{
	public function __construct()
	{

		parent::__construct();
		
        $this->setTemplate('store/store.phtml');
	}
	
	protected function _prepareLayout()
	{
		$this->setChild('add_new_button',
			$this->getLayout()->createBlock('adminhtml/widget_button')
			->setData(array(
				'label'     => Mage::helper('store')->__('Add Store'),
				'onclick'   => "setLocation('".$this->getUrl('*/*/new')."')",
				'class'   => 'add'
			))
		);
		
		$this->setChild('store_switcher',
			$this->getLayout()->createBlock('adminhtml/store_switcher')
				->setUseConfirm(false)
				->setSfeitchUrl($this->getUrl('*/*/*/', array('store'=>null)))
		);
		
		$this->setChild('grid', $this->getLayout()->createBlock('store/adminhtml_store_grid', 'store.grid'));
		return parent::_prepareLayout();
	}



	public function getAddNewButtonHtml()
	{
		if( $this->_enabledAddNewButton() )
		{
			return $this->getChildHtml('add_new_button');
		}
		
		return '';
	}
	
	
	
	protected function _enabledAddNewButton()
	{
		return true;
	}
	
	
	
	public function isSingleStoreMode()
	{
		if (!Mage::app()->isSingleStoreMode())
		{
			return false;
		}
		
		return true;
	}
	
	
	
	public function getGridHtml()
	{
		return $this->getChildHtml('grid');
	}
}