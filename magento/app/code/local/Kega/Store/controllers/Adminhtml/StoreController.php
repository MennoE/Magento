<?php

class Kega_Store_Adminhtml_StoreController extends Mage_Adminhtml_Controller_Action
{

	protected function _initStore()
	{
		$storeId = (int) $this->getRequest()->getParam('id');

		$store = Mage::getModel('store/store')
			->setStoreId($this->getRequest()->getParam('store', 0));

		if (!$storeId)
		{
			if ($setId = (int) $this->getRequest()->getParam('set'))
			{
				$store->setAttributeSetId($setId);
			}
		}

		if ($storeId)
		{
			$store->load($storeId);

			$store->setStoreFilter($storeId);
		}

		Mage::register('store', $store);

		return $store;
	}



	protected function _initAction()
	{
		$this->loadLayout()
			->_setActiveMenu('store/items')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Items Manager'), Mage::helper('adminhtml')->__('Item Manager'));
		return $this;
	}



	public function indexAction()
	{
		$this->_initAction()->renderLayout();
	}



	public function editAction()
	{
		$storeId     = $this->getRequest()->getParam('id');//!!!delete

		$storeModel  = $this->_initStore();

		if ($storeModel->getId() || $storeId == 0)
		{
			$storeModel->getOpeningData();//!!!

			Mage::register('store_data', $storeModel);

			$this->loadLayout();

			$this->_setActiveMenu('store/items');

			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Manager'), Mage::helper('adminhtml')->__('Item Manager'));

			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Store'), Mage::helper('adminhtml')->__('Item Store'));

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

			$this->_addContent($this->getLayout()->createBlock('store/adminhtml_store_edit'))
				->_addLeft($this->getLayout()->createBlock('store/adminhtml_store_edit_tabs'));

			$this->renderLayout();
		}
		else
		{
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('store')->__('Item does not exist'));

			$this->_redirect('*/*/');
		}
	}



	public function newAction()
	{
		$this->_forward('edit');
	}

	/**
	* Kega_Store_Adminhtml_StoreController->handleFileUploads()
	* Handle all uploaded files, remove empty uploads from postdata
	*
	* @param $storeModel Kega_Store_Model_Store
	* @param $storeData array
	* @return array
	*/

	private function handleFileUploads($storeModel, $storeData)
	{
	       $path = Mage::getBaseDir('media') . DS . 'upload' . DS . 'stores' . DS;
	       foreach($_FILES['store_data']['name'] as $key => $file) {
	               if (empty($file)) {
	                       continue;
	               }

	               $filename = 'store-' . $storeModel->getEntityId() . '-' . $file;
	               move_uploaded_file(
	                      $_FILES['store_data']['tmp_name'][$key],
	                      $path . $filename
	               );
	               $storeData[$key] = $filename;
	       }

	       return $storeData;
	}

	/**
	* Save store action
	*
	*/
	public function saveAction()
	{
		$coreStoreId			= $this->getRequest()->getParam('store', 0);

		$isEdit				= (int)($this->getRequest()->getParam('id') != null);

		if ( $data = $this->getRequest()->getPost() )
		{
			try
			{
				$storeModel = $this->_initStore();

				$storeData = $data['store_data'];

				$storeData = $this->handleFileUploads($storeModel, $storeData);

				$storeModel->addData($storeData);

				if ($useDefaults = $this->getRequest()->getPost('use_default'))
				{
					foreach ($useDefaults as $attributeCode)
					{
						$storeModel->setData($attributeCode, null);
					}
				}

				/*****extra opening data************/

				$extraopenings = $this->getRequest()->getParam('extraopening_ids');

				$extraopeningIds = array();

				if (is_array($extraopenings))
				{

					$extraopeningIds = array();

					foreach ($extraopenings as $extraopeningId)
					{
						$extraopeningIds[] = $extraopeningId;
					}

				}

				$storeModel->setStoreExtraopeningIds($extraopeningIds);

				/******opening data time************/

				$opening_data = $data['opening'];

				if (is_array($opening_data))
				{

					$storeModel->setStoreOpeningData($opening_data);

				}


				$storeModel->save();

				/******************************/

				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Store was successfully saved'));

				Mage::getSingleton('adminhtml/session')->setStoreData(false);

				if ($this->getRequest()->getParam('back'))
				{
					$this->_redirect('*/*/edit', array('id' => $storeModel->getId(), 'store'=>$coreStoreId));

					return;
				}

				$this->_redirect('*/*/', array('store'=>$coreStoreId));

				return;

			}
			catch (Exception $e)
			{
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());

				Mage::getSingleton('adminhtml/session')->setStoreData($this->getRequest()->getPost());

				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id'), 'store'=>$coreStoreId));

				return;
			}
		}
		$this->_redirect('*/*/', array('store'=>$coreStoreId));
	}



	public function deleteAction()
	{
		if( $this->getRequest()->getParam('id') > 0 )
		{
			try
			{
				$storeModel = Mage::getModel('store/store');

				$storeModel->setId($this->getRequest()->getParam('id'))
					->delete();

				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Store was successfully deleted'));
				$this->_redirect('*/*/');
			}
			catch (Exception $e)
			{
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());

				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
			}
		}
		$this->_redirect('*/*/');
	}

	/**
     * Export retail store grid to CSV format
     */
    public function exportCsvAction()
    {
        $fileName   = 'stores.csv';
        $content    = $this->getLayout()->createBlock('store/adminhtml_store_grid')
            ->getCsv();

        $this->_prepareDownloadResponse($fileName, $content);
    }

}