<?php
class Kega_Extraopening_Block_Adminhtml_Extraopening extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_extraopening';
    $this->_blockGroup = 'extraopening';
    $this->_headerText = Mage::helper('extraopening')->__('Item Manager');
    $this->_addButtonLabel = Mage::helper('extraopening')->__('Add Item');
    parent::__construct();
  }
}