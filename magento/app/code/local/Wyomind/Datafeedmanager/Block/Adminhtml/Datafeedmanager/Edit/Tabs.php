 <?php

 class Wyomind_Datafeedmanager_Block_Adminhtml_Datafeedmanager_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
  {

     public function __construct()
     {
          parent::__construct();
          $this->setId('datafeedmanager_tabs');
          $this->setDestElementId('edit_form');
          $this->setTitle('Data Feed Manager');
      }

      protected function _beforeToHtml()
      {
          $this->addTab('form_configuration', array(
                   'label' => $this->__('Configuration'),
                   'title' => $this->__('Configuration'),
                   'content' => $this->getLayout()
		     ->createBlock('datafeedmanager/adminhtml_datafeedmanager_edit_tab_configuration')
		     ->toHtml()
         ));
         $this->addTab('form_filter', array(
                   'label' => $this->__('Filters'),
                   'title' => $this->__('Filters'),
                   'content' => $this->getLayout()
		     ->createBlock('datafeedmanager/adminhtml_datafeedmanager_edit_tab_filters')
		     ->toHtml()
         ));
          $this->addTab('form_cron', array(
                   'label' => $this->__('Scheduled task'),
                   'title' => $this->__('Scheduled task'),
                   'content' => $this->getLayout()
		     ->createBlock('datafeedmanager/adminhtml_datafeedmanager_edit_tab_cron')
		     ->toHtml()
         ));
         return parent::_beforeToHtml();
    }
}
