<?php
class Kega_Store_Block_Adminhtml_Store_Edit_Tab_Extraopening extends Mage_Adminhtml_Block_Widget_Form
{

	public function __construct()
	{
		parent::__construct();
		
		$this->setTemplate('kega_store/extraopening.phtml');
	}
	
	
	public function getFieldSuffix()
	{
		return 'extraopening_ids';
	}
	
	
	public function getExtraopeningList()
	{
		return Mage::getModel('extraopening/extraopening')->getCollection();
	}
	
	
	public function getExtraopeningIds()
	{
		return Mage::registry('store')->getExtraopeningIds();		
	}

}