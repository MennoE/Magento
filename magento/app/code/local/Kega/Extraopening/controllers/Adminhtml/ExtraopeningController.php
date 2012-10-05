<?php

class Kega_Extraopening_Adminhtml_ExtraopeningController extends Mage_Adminhtml_Controller_action
{

	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('extraopening/items')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Items Manager'), Mage::helper('adminhtml')->__('Item Manager'));

		return $this;
	}

	public function indexAction() {
		$this->_initAction()
			->renderLayout();
	}

	public function editAction() {
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('extraopening/extraopening')->load($id);

		if ($model->getId() || $id == 0) {
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data)) {
				$model->setData($data);
			}

			Mage::register('extraopening_data', $model);

			$this->loadLayout();
			$this->_setActiveMenu('extraopening/items');

			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Manager'), Mage::helper('adminhtml')->__('Item Manager'));
			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Extraopening'), Mage::helper('adminhtml')->__('Item Extraopening'));

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

			$this->_addContent($this->getLayout()->createBlock('extraopening/adminhtml_extraopening_edit'))
				->_addLeft($this->getLayout()->createBlock('extraopening/adminhtml_extraopening_edit_tabs'));

			$this->renderLayout();
		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('extraopening')->__('Item does not exist'));
			$this->_redirect('*/*/');
		}
	}

	public function newAction() {
		$this->_forward('edit');
	}

	public function saveAction() {

		if ($postData = $this->getRequest()->getPost()) {

			// convert to mysql format
			$dateFormatIso = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
			$_zendDate = new Zend_Date($postData['datetime_from'], $dateFormatIso);
			$datetimeFrom =  $_zendDate->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);

			// convert to mysql format
			$_zendDate = new Zend_Date($postData['datetime_to'], $dateFormatIso);
			$datetimeTo =  $_zendDate->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);

			$model = Mage::getModel('extraopening/extraopening');
			$model->setTitle($postData['title'])
				->setId($this->getRequest()->getParam('id'))
				->setDatetimeFrom($datetimeFrom)
				->setDatetimeTo($datetimeTo)
				->setStatus($postData['status']);

			try {
				$stores = $this->getRequest()->getParam('store_ids');

				if (!is_array($stores) || count($stores) == 0) {
					Mage::throwException(Mage::helper('adminhtml')->__('Please, select visible in stores to this extraopening first'));
				}

				if (is_array($stores)) {
					$storeIds = array();
					foreach ($stores as $storeId) {
						$storeIds[] = $storeId;
					}
					$model->setStoreIds($storeIds);
				}

				$model->save();

				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('extraopening')->__('Item was successfully saved'));
				Mage::getSingleton('adminhtml/session')->setFormData(false);

				if ($this->getRequest()->getParam('back')) {
					$this->_redirect('*/*/edit', array('id' => $model->getId()));
					return;
				}
				$this->_redirect('*/*/');
				return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($postData);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('extraopening')->__('Unable to find item to save'));
        $this->_redirect('*/*/');
	}

	public function deleteAction() {
		if( $this->getRequest()->getParam('id') > 0 ) {
			try {
				$model = Mage::getModel('extraopening/extraopening');

				$model->setId($this->getRequest()->getParam('id'))
					->delete();

				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Item was successfully deleted'));
				$this->_redirect('*/*/');
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
			}
		}
		$this->_redirect('*/*/');
	}

    public function massDeleteAction() {
        $extraopeningIds = $this->getRequest()->getParam('extraopening');
        if(!is_array($extraopeningIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                foreach ($extraopeningIds as $extraopeningId) {
                    $extraopening = Mage::getModel('extraopening/extraopening')->load($extraopeningId);
                    $extraopening->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($extraopeningIds)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function massStatusAction()
    {
        $extraopeningIds = $this->getRequest()->getParam('extraopening');
        if(!is_array($extraopeningIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
        } else {
            try {
                foreach ($extraopeningIds as $extraopeningId) {
                    $extraopening = Mage::getSingleton('extraopening/extraopening')
                        ->load($extraopeningId)
                        ->setStatus($this->getRequest()->getParam('status'))
                        ->setIsMassupdate(true)
                        ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully updated', count($extraopeningIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function exportCsvAction()
    {
        $fileName   = 'extraopening.csv';
        $content    = $this->getLayout()->createBlock('extraopening/adminhtml_extraopening_grid')
            ->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction()
    {
        $fileName   = 'extraopening.xml';
        $content    = $this->getLayout()->createBlock('extraopening/adminhtml_extraopening_grid')
            ->getXml();

        $this->_sendUploadResponse($fileName, $content);
    }

    protected function _sendUploadResponse($fileName, $content, $contentType='application/octet-stream')
    {
        $response = $this->getResponse();
        $response->setHeader('HTTP/1.1 200 OK','');
        $response->setHeader('Pragma', 'public', true);
        $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        $response->setHeader('Content-Disposition', 'attachment; filename='.$fileName);
        $response->setHeader('Last-Modified', date('r'));
        $response->setHeader('Accept-Ranges', 'bytes');
        $response->setHeader('Content-Length', strlen($content));
        $response->setHeader('Content-type', $contentType);
        $response->setBody($content);
        $response->sendResponse();
        die;
    }
}