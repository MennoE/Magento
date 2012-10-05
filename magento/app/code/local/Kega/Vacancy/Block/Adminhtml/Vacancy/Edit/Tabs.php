<?php

class Kega_Vacancy_Block_Adminhtml_Vacancy_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('vacancy_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('vacancy')->__('Item Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('vacancy')->__('Item Information'),
          'title'     => Mage::helper('vacancy')->__('Item Information'),
          'content'   => $this->getLayout()->createBlock('vacancy/adminhtml_vacancy_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}