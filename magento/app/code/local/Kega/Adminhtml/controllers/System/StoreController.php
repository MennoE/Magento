<?php

require_once('app/code/core/Mage/Adminhtml/controllers/System/StoreController.php');

/**
 * Store controller
 *
 * @category    Kega
 * @package     Kega_Essentials
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Kega_Adminhtml_System_StoreController extends Mage_Adminhtml_System_StoreController
{
    /**
     * Init actions
     *
     * @return Mage_Adminhtml_Cms_PageController
     */
    protected function _initAction()
    {
        // load layout, set active menu and breadcrumbs
        $this->loadLayout()
            ->_setActiveMenu('system/store')
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('System'), Mage::helper('adminhtml')->__('System'))
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Manage Stores'), Mage::helper('adminhtml')->__('Manage Stores'))
        ;
        return $this;
    }

    /**
     * Kega_Essentials_System_StoreController::init
	 * Save website and trigger caching process
     *
     * @param void
     * @return void
     */
    public function saveAction()
    {
        if ($this->getRequest()->isPost() && $postData = $this->getRequest()->getPost()) {
            if (empty($postData['store_type']) || empty($postData['store_action'])) {
                $this->_redirect('*/*/');
                return;
            }
            $session = $this->_getSession();

			try {
                switch ($postData['store_type']) {
                    case 'website':
                        $websiteModel = Mage::getModel('core/website');
                        if ($postData['website']['website_id']) {
                            $websiteModel->load($postData['website']['website_id']);
                        }
                        $websiteModel->setData($postData['website']);
                        if ($postData['website']['website_id'] == '') {
                            $websiteModel->setId(null);
                        }

                        $websiteModel->save();
                        $session->addSuccess(Mage::helper('core')->__('Website was successfully saved'));

						$this->_createWebsiteCache();

                        break;

                    case 'group':
                        $groupModel = Mage::getModel('core/store_group');
                        if ($postData['group']['group_id']) {
                            $groupModel->load($postData['group']['group_id']);
                        }
                        $groupModel->setData($postData['group']);
                        if ($postData['group']['group_id'] == '') {
                            $groupModel->setId(null);
                        }

                        $groupModel->save();

                        Mage::dispatchEvent('store_group_save', array('group' => $groupModel));

                        $session->addSuccess(Mage::helper('core')->__('Store was successfully saved'));
                        break;

                    case 'store':
                        $eventName = 'store_edit';
                        $storeModel = Mage::getModel('core/store');
                        if ($postData['store']['store_id']) {
                            $storeModel->load($postData['store']['store_id']);
                        }
                        $storeModel->setData($postData['store']);
                        if ($postData['store']['store_id'] == '') {
                            $storeModel->setId(null);
                            $eventName = 'store_add';
                        }
                        $groupModel = Mage::getModel('core/store_group')->load($storeModel->getGroupId());
                        $storeModel->setWebsiteId($groupModel->getWebsiteId());
                        $storeModel->save();

                        Mage::app()->reinitStores();

                        Mage::dispatchEvent($eventName, array('store'=>$storeModel));

                        $session->addSuccess(Mage::helper('core')->__('Store View was successfully saved'));
                        break;
                    default:
                        $this->_redirect('*/*/');
                        return;
                }
                $this->_redirect('*/*/');
                return;
            }
            catch (Mage_Core_Exception $e) {
                $session->addError($e->getMessage());
                $session->setPostData($postData);
            }
            catch (Exception $e) {
                $session->addException($e, Mage::helper('core')->__('Error while saving. Please try again later.'));
                $session->setPostData($postData);
            }
            $this->_redirectReferer();
            return;
        }
        $this->_redirect('*/*/');
    }

    /**
     * Kega_Essentials_System_StoreController::_createWebsiteCache
	 * Save website and trigger caching process
     *
     * @param void
     * @return void
     */
	private function _createWebsiteCache()
	{
		$file = 'website.cache';

		$baseUrl = Mage::app()->getStore(Mage_Core_Model_App::ADMIN_STORE_ID)
			->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK);

		$io = new Varien_Io_File();
		$io->setAllowCreateFolders(true);
		$io->open(array('path' => Mage::getBaseDir() . '/var'));

		$io->streamOpen($file);
		$io->streamWrite(serialize($this->_fetchWebsites()));
		$io->streamClose();
	}

    /**
     * Kega_Essentials_System_StoreController::_fetchWebsites
	 * Save website and trigger caching process
     *
     * @param void
     * @return void
     */
	private function _fetchWebsites()
	{
		$websites = array();
		$read = Mage::getSingleton('core/resource')->getConnection('core_read');
		$result = $read->query("
			SELECT *
			FROM core_website
		");

		while ($row = $result->fetch(Zend_Db::FETCH_ASSOC)) {
			$row['host'] = array_map('trim', explode("\n", $row['host']));

			foreach($row['host'] as $host) {
				$websites[$host] = $row['code'];
			}
		}
		return $websites;
	}
}