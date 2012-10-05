<?php

class Kega_Extraopening_Block_Adminhtml_Extraopening_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('extraopening_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('extraopening')->__('Item Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('extraopening')->__('Item Information'),
          'title'     => Mage::helper('extraopening')->__('Item Information'),
          'content'   => $this->getLayout()->createBlock('extraopening/adminhtml_extraopening_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}