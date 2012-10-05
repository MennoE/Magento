<?php

class Kega_Vacancytype_Block_Adminhtml_Vacancytype_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('vacancytype_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('vacancytype')->__('Item Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('vacancytype')->__('Item Information'),
          'title'     => Mage::helper('vacancytype')->__('Item Information'),
          'content'   => $this->getLayout()->createBlock('vacancytype/adminhtml_vacancytype_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}