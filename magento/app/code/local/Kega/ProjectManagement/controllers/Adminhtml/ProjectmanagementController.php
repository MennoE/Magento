<?php
/**
 * Adminhtml controller
 *
 * @category   Kega
 * @package    Kega_ProjectManagement
 */
class Kega_ProjectManagement_Adminhtml_ProjectmanagementController extends Mage_Adminhtml_Controller_Action
{

    public function backupfilesListAction()
    {
        $this->loadLayout();

        $this->_setActiveMenu('projectmanagement/backupfiles');
        $this->_addBreadcrumb(Mage::helper('projectmanagement')->__('View Backup Files'),
                              Mage::helper('projectmanagement')->__('View Backup Files'));
        $this->_addContent($this->getLayout()->createBlock('projectmanagement/adminhtml_backupfiles_list'));

        $this->renderLayout();
    }


    public function backupfilesViewAction()
    {
        $fileId = $this->getRequest()->getParam('id', null);

        if (!$fileId) {
            $this->_forward('404');
            return;
        }

        $backupFilesCollection = Mage::getModel('projectmanagement/backupfile_collection')->load();

        $record = $backupFilesCollection->getRecordById($fileId);

        if (!$record) {
            $this->_forward('404');
            return;
        }

        try {
            $backupfileConfigModel = Mage::getModel('projectmanagement/adminhtml_system_config_backend_serialized_array_backupfile');
            $realFilePath = $backupfileConfigModel->getRealFilePath($record['filename']);

            Mage::register('real_file_path', $realFilePath);
            Mage::register('file_record_data', $record);

        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }



        $this->loadLayout();

        $this->_setActiveMenu('projectmanagement/backupfiles');
        $this->_addBreadcrumb(Mage::helper('projectmanagement')->__('View Backup File'),
                              Mage::helper('projectmanagement')->__('View Backup File'));
        $this->_addContent($this->getLayout()->createBlock('projectmanagement/adminhtml_backupfiles_view'));

        $this->renderLayout();
    }


    public function searchOrderAction()
    {
        $this->loadLayout();

        $productSku = $this->getRequest()->getParam('product_sku');

        if ($productSku) {
            Mage::register('product_sku', $productSku);
        }

        $this->_setActiveMenu('projectmanagement/backupfiles');
        $this->_addBreadcrumb(Mage::helper('projectmanagement')->__('Search Order by Product SKU'),
                              Mage::helper('projectmanagement')->__('Search Order by Product SKU'));
        $this->_addContent($this->getLayout()->createBlock('projectmanagement/adminhtml_searchorder_list'));

        $this->renderLayout();
    }


}
