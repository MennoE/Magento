<?php
class Kega_Store_Block_Adminhtml_Import extends Mage_Adminhtml_Block_Widget
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('kega_store/import.phtml');
    }

    public function getImportUrl()
    {
        return $this->getUrl('*/*/import', array('store' => $this->getRequest()->getParam('store')));
    }
}