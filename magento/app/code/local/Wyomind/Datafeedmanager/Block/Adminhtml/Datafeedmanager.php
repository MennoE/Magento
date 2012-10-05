<?php
class Wyomind_Datafeedmanager_Block_Adminhtml_Datafeedmanager extends Mage_Adminhtml_Block_Widget_Grid_Container
{
	public function __construct()
	{
		$this->_controller = 'adminhtml_datafeedmanager';
		$this->_blockGroup = 'datafeedmanager';
		$this->_headerText = Mage::helper('datafeedmanager')->__('Data Feed Manager');
		$this->_addButtonLabel = Mage::helper('datafeedmanager')->__('Create new data feed');
		parent::__construct();
	}
}

