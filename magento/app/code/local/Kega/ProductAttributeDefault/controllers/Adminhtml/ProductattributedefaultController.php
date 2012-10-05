<?php
/**
 *
 * @category Kega
 * @package  Kega_ProductAttributeDefault
 */
?>
<?php
class Kega_ProductAttributeDefault_Adminhtml_ProductattributedefaultController extends Mage_Adminhtml_Controller_Action
{

    /**
     * Loads a product attribute model instance
     *
     * @return Kega_ProductAttributeDefault_Model_Layerednavseo
     */
    protected function _initProductAttributeDefaultEntry()
    {
        $productAttributeDefaultId  = (int) $this->getRequest()->getParam('id');
        $productAttributeDefaultModel = Mage::getModel('kega_productattributedefault/productattributedefault');

        if ($productAttributeDefaultId) {
            $productAttributeDefaultModel->load($productAttributeDefaultId);

            if (!$productAttributeDefaultModel->getId()) {
                Mage::throwException(Mage::helper('kega_productattributedefault')->__('Invalid record id %s', $productAttributeDefault->getId()));
            }
        }

        // maybe an error occured and we have the post data on session
        /**
         *@todo Anda B. 27.Jul.2011 make it work for Rule and Action tab
         */
        $productDefaultAttributeData = Mage::getSingleton('adminhtml/session')->getProductAttributeDefaultData();

        if ($productDefaultAttributeData) {
            foreach ($productDefaultAttributeData as $key => $value) {
                $productAttributeDefaultModel->setData($key, $value);
            }
        }

        return $productAttributeDefaultModel;
    }


    /**
     * Action: Index
     */
    public function indexAction()
    {
        $this->loadLayout();

        $this->_setActiveMenu('catalog/kega_productattributedefault');
        $this->_addBreadcrumb(Mage::helper('kega_productattributedefault')->__('Manage Product Attribute Default Values Entries'),
                              Mage::helper('kega_productattributedefault')->__('Manage Product Attribute Default Values Entries'));

        $this->_addContent($this->getLayout()->createBlock('kega_productattributedefault/adminhtml_productattributedefault_list'));

        //echo $this->getFullActionName();

        $this->renderLayout();

    }

    /**
     * Action: Mass Delete
     */
    public function massDeleteAction()
    {
        $productattributedefaultIds = $this->getRequest()->getParam('productattributedefault');
        if(!is_array($productattributedefaultIds)) {
             Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select at least one record'));
        } else {
            try {
                $productattributedefaultModel = Mage::getModel('kega_productattributedefault/productattributedefault');
                foreach ($productattributedefaultIds as $productattributedefaultId) {
                    $productattributedefaultModel->load($productattributedefaultId)
                        ->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($productattributedefaultIds)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }

        $this->_redirect('*/*/index');
    }


    /**
     * Action: Mass Update Status
     */
    public function massUpdateStatusAction()
    {
        $productattributedefaultIds = $this->getRequest()->getParam('productattributedefault');
        $status = $this->getRequest()->getParam('status');
        if(!is_array($productattributedefaultIds)) {
             Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select at least one record'));
        } else {
            try {
                $productattributedefaultModel = Mage::getModel('kega_productattributedefault/productattributedefault');
                foreach ($productattributedefaultIds as $productattributedefaultId) {
                    $productattributedefaultModel->load($productattributedefaultId)
                        ->setData('is_enabled', $status);
                    $productattributedefaultModel->save();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully updated', count($productattributedefaultIds)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }

        $this->_redirect('*/*/index');
    }

    /**
     * Action: Mass Update Dry Run
     */
    public function massUpdateDryRunAction()
    {
        $productattributedefaultIds = $this->getRequest()->getParam('productattributedefault');
        $dryRun = $this->getRequest()->getParam('dry_run');
        if(!is_array($productattributedefaultIds)) {
             Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select at least one record'));
        } else {
            try {
                $productattributedefaultModel = Mage::getModel('kega_productattributedefault/productattributedefault');
                foreach ($productattributedefaultIds as $productattributedefaultId) {
                    $productattributedefaultModel->load($productattributedefaultId)
                        ->setData('dry_run', $dryRun);
                    $productattributedefaultModel->save();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully updated', count($productattributedefaultIds)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }

        $this->_redirect('*/*/index');
    }


    /**
     * Action: New
     * forwarded to edit
     */
    public function newAction()
    {
        $this->_forward('edit');
    }


    public function duplicateProfileAction()
    {
    	$id = $this->getRequest()->getParam('id');

    	$productAttributeDefaultId  = (int) $this->getRequest()->getParam('id');
        $productAttributeDefaultModel = Mage::getModel('kega_productattributedefault/productattributedefault');

        if ($productAttributeDefaultId) {
            $productAttributeDefaultModel->load($productAttributeDefaultId);

            if (!$productAttributeDefaultModel->getId()) {
                Mage::throwException(Mage::helper('kega_productattributedefault')->__('Invalid record id %s', $productAttributeDefault->getId()));
            }
        }

        $newProductDefaultModel = clone $productAttributeDefaultModel;

        $newProductDefaultModel->save();

        $this->_redirect('*/*/edit', array('id' => $newProductDefaultModel->getId()));

        //Zend_Debug::dump($newProductDefaultModel->getData());
        //die;
    }

    /**
     * Run given profile in testrun mode.
     *
     * Testrun mode will generate a textfile with all upcomming
     * changes done by the rule.
     * No real changes will be done!
     *
     */
    public function testRunProfileAction()
    {
        $content = '';

        try {

            $logfile = Mage::helper('kega_productattributedefault')->getLogFile();

            $profileId = $this->getRequest()->getParam('id');
            $profile = Mage::getModel('kega_productattributedefault/productattributedefault')->load($profileId);
            $profile->setData('log_file', basename($logfile));
            $profile->save();

            $title = "Test run profile '{$profile->getRuleName()}'";
            $width = strlen($title) + 6;

            $content .= str_repeat('#', $width) . PHP_EOL;
            $content .= '## ' . $title . ' ##' . PHP_EOL;
            $content .= str_repeat('#', $width) . PHP_EOL . PHP_EOL;

            if(!$profileId || !is_numeric($profileId)) {
                Mage::throwException(Mage::helper('kega_productattributedefault')->__('Invalid record id %s', $profileId));
            }

            // running a profile in testmode will return a list of all upcomming changes.
            $content .= $profile->runProfile($logfile, true);

        } catch (Exception $e) {

            $content .= PHP_EOL . '###### Exception occured! ######' . PHP_EOL;
            $content .= $e->__toString();
            Mage::log($e->getMessage(), Zend_Log::ERR);
        }

        $this->_prepareDownloadResponse("testrun_profile-{$profile->getId()}.txt", $content);
        return;
    }

    /**
     * Action - Get actions
     * ajax, html output
     *
     */
    public function actionsAction()
    {
        $productAttributeDefaultModel = $this->_initProductAttributeDefaultEntry();

        $this->getResponse()->setBody(
            $this->getLayout()
                    ->createBlock('kega_productattributedefault/adminhtml_productattributedefault_edit_tab_actions',
                                  'kega_productattributedefault_adminhtml_productattributedefault_edit_tab_actions',
                                  array('product_attribute_default_model' => $productAttributeDefaultModel))
                    ->toHtml()
        );
    }



    /**
     * Get rules block
     *
     */
    public function rulesAction()
    {
        $productAttributeDefaultModel = $this->_initProductAttributeDefaultEntry();

        $this->getResponse()->setBody(
            $this->getLayout()
                ->createBlock('kega_productattributedefault/adminhtml_productattributedefault_edit_tab_rules')
                ->setProductAttributeDefaultModel($productAttributeDefaultModel)
                ->toHtml()
        );
    }

    public function categoriesJsonAction()
    {
        $productAttributeDefaultModel = $this->_initProductAttributeDefaultEntry();

        $this->getResponse()->setBody(
            $this->getLayout()
                ->createBlock('kega_productattributedefault/adminhtml_productattributedefault_edit_tab_categories')
                ->setProductAttributeDefaultModel($productAttributeDefaultModel)
                ->getCategoryChildrenJson($this->getRequest()->getParam('category'))
        );
    }

    /**
     * Action: Edit
     *
     */
    public function editAction()
    {
        $this->loadLayout();

        $productattributedefaultId  = (int) $this->getRequest()->getParam('id');
        $productattributedefaultModel = $this->_initProductAttributeDefaultEntry();

        if (Mage::getSingleton('adminhtml/session')->getProductAttributeDefaultData()) {
            $productattributedefaultModel->setData(Mage::getSingleton('adminhtml/session')->getProductAttributeDefaultData());
            Mage::getSingleton('adminhtml/session')->setProductAttributeDefaultData(false);
        }

        Mage::register('product_attribute_default', $productattributedefaultModel);

        $this->_setActiveMenu('catalog/kega_productattributedefault');
        $this->_addBreadcrumb(Mage::helper('kega_productattributedefault')->__('Manage Product Attribute Default Values Entries'),
                              Mage::helper('kega_productattributedefault')->__('Add/Edit Entry'));

        $this->_addContent($this->getLayout()->createBlock('kega_productattributedefault/adminhtml_productattributedefault_edit'))
            ->_addLeft($this->getLayout()->createBlock('kega_productattributedefault/adminhtml_productattributedefault_edit_tabs'));
        $this->renderLayout();
    }

    /**
     * Action: Delete
     *
     */
    public function deleteAction()
    {

        if( $this->getRequest()->getParam('id') > 0 ) {
            try {
                $productattributedefaultModel = $this->_initProductAttributeDefaultEntry();
                $productattributedefaultModel->delete();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('kega_productattributedefault')->__('The entry has been successfully deleted'));
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }


    /**
     * Action: Save
     *
     */
    public function saveAction()
    {
        if ( $this->getRequest()->getPost() ) {
            try {
                $productattributedefaultModel = $this->_initProductAttributeDefaultEntry();

                $productattributedefaultData = $this->getRequest()->getPost('product_attribute_default');

                foreach ($productattributedefaultData as $key => $value) {
                	if ($key == 'id') continue;
                    $productattributedefaultModel->setData($key, $value);
                }
                $productattributedefaultModel->save();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('kega_productattributedefault')->__('The entry has been successfully saved'));
                Mage::getSingleton('adminhtml/session')->setProductAttributeDefaultData(false);

                if ($this->getRequest()->getParam('continue')) {
                	$this->_redirect('*/*/edit', array('id' => $productattributedefaultModel->getId()));
                	return;
                }

                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setProductAttributeDefaultData($this->getRequest()->getPost('product_attribute_default'));
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        $this->_redirect('*/*/');
    }


    /**
     * Action download log file
     *
     */
    public function downloadLogAction()
    {
        $productattributedefaultModel = $this->_initProductAttributeDefaultEntry();

        $fileName = $productattributedefaultModel->getData('log_file');
        $filePath   = Mage::helper('kega_productattributedefault')->getLogDir(). DS . $fileName;

        if (!is_file($filePath)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('No log file found for the current profile'));
            $this->_redirect('*/*/');
            return;
        }
        $content    = file_get_contents($filePath);
        $this->_prepareDownloadResponse($fileName, $content);
    }


    /**
     * Check the permission to run it
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        //Mage::getResourceModel('admin/acl')->loadAcl();
        return Mage::getSingleton('admin/session')->isAllowed('catalog/kega_productattributedefault');
    }


}