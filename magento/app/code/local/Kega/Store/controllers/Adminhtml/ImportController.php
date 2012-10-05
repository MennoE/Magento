<?php

class Kega_Store_Adminhtml_ImportController extends Mage_Adminhtml_Controller_Action
{
	/**
	 * Trigger adminhtml render process, highlight current navigation item
	 */
    public function indexAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('store/tax_importExport')
            ->_addContent($this->getLayout()->createBlock('store/adminhtml_import'))
            ->renderLayout();
    }

	/**
	 * Trigger business logic for importing store data
	 * @throws Exception when invalid file has been uploaded
	 */
    public function importPostAction()
    {

		if ($this->getRequest()->isPost() && !empty($_FILES['import_file']['tmp_name'])) {
			try {
				$this->_importStores();

				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('store')->__('Stores were successfully imported'));
			}
			catch (Mage_Core_Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			}
			catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage() . ' - ' . Mage::helper('store')->__('Invalid file upload attempt1'));
			}
		}
		else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('store')->__('Invalid file upload attempt2'));
		}
		$this->_redirect('*/adminhtml_store');
    }


	/**
	 * Initiliaze import helper and csv settings
	 */
    protected function _importStores()
    {
        $fileName   = $_FILES['import_file']['tmp_name'];
        $csvObject  = new Varien_File_Csv();
        $csvObject->setDelimiter($this->getRequest()->getParam('delimeter', ';'));
        $csvData = $csvObject->getData($fileName);

        Mage::helper('store/import')->importStores($csvData);
    }

	/**
	 * Trigger business logic for processing extra opening calender dates
	 * @throws Exception when invalid file has been uploaded
	 */
	public function importPostExtraOpeningAction()
    {
		if ($this->getRequest()->isPost() && !empty($_FILES['import_file_extraopening']['tmp_name'])) {
			try {
				$fileName   = $_FILES['import_file_extraopening']['tmp_name'];
				$csvObject  = new Varien_File_Csv();
				$csvObject->setDelimiter($this->getRequest()->getParam('delimeter', ';'));
				$csvData = $csvObject->getData($fileName);

				Mage::helper('store/import')->importExtraOpenings($csvData);

				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('store')->__('The extra openings were successfully imported'));
			}
			catch (Mage_Core_Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			}
			catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage() . ' - ' . Mage::helper('store')->__('Invalid file upload attempt1'));
			}
		}
		else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('store')->__('Invalid file upload attempt2'));
		}
		$this->_redirect('extraopening/adminhtml_extraopening');
    }
}