<?php

class Wyomind_Datafeedmanager_Adminhtml_DatafeedmanagerController extends Mage_Adminhtml_Controller_Action
{
	protected function check_activation(){
		
		
		
		$activation_key=Mage::getStoreConfig("datafeedmanager/license/activation_key");
      	$get_online_license=Mage::getStoreConfig("datafeedmanager/license/get_online_license");
		$activation_code=Mage::getStoreConfig("datafeedmanager/license/activation_code");
		$base_url=Mage::getStoreConfig("web/secure/base_url");
		
		$registered_version=Mage::getStoreConfig("datafeedmanager/license/version");
		$current_version = Mage::getConfig()->getNode("modules/Wyomind_Datafeedmanager")->version;

		
		$license_request="&rv=".$registered_version."&cv=".$current_version."&activation_key=".$activation_key."&domain=".$base_url."&store_code=".Mage::app()->getStore()->getCode();
		if($registered_version!=$current_version && ($activation_code || !empty($activation_code))){
			Mage::getSingleton("adminhtml/session")->addError(Mage::helper("datafeedmanager")->__("<u>Extension upgrade from v".$registered_version." to v".$current_version."</u>.<br> Your license must be updated.<br>Please, reload this page."));
			Mage::getConfig()->saveConfig("datafeedmanager/license/activation_code", "", "default", "0");
		}
		elseif(!$activation_key){
			
			Mage::getConfig()->saveConfig("datafeedmanager/license/activation_code", "", "default", "0");
			Mage::getSingleton("adminhtml/session")->addError(Mage::helper("datafeedmanager")->__("Your activation key is not yet registered.<br>
										Go to system/configuration/Wyomind/Data Feed Manager."));
			
			Mage::getConfig()->saveConfig("datafeedmanager/license/activation_code", "", "default", "0");
		}
		elseif($activation_key && (!$activation_code || empty($activation_code)) && !$get_online_license){
       		Mage::getSingleton("adminhtml/session")->addError(Mage::helper("datafeedmanager")->__("Your license is not yet activated.<br>
										<a target='_blank' href='http://www.wyomind.com/license_activation/?method=post".$license_request."'>Go to http://www.wyomind.com/license_activation/</a>"));
			
			Mage::getConfig()->saveConfig("datafeedmanager/license/activation_code", "", "default", "0");
		}
		elseif($activation_key && (!$activation_code ||empty($activation_code)) && $get_online_license){
		
			try {
				$activation_code = file_get_contents("http://www.wyomind.com/license_activation/index.php?method=get&rv=".$license_request);
				
				$result=json_decode($activation_code);
				switch($result->status){
					case "success" : 
						 Mage::getConfig()->saveConfig("datafeedmanager/license/version", $result->version, "default", "0");
						 Mage::getConfig()->saveConfig("datafeedmanager/license/activation_code", $result->activation, "default", "0");
						 Mage::getConfig()->cleanCache();						 
						 Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("datafeedmanager")->__($result->message));
						 
					break;
					case "error" : 
						 Mage::getSingleton("adminhtml/session")->addError(Mage::helper("datafeedmanager")->__($result->message));
						 Mage::getConfig()->saveConfig("datafeedmanager/license/activation_code", "", "default", "0");
						Mage::getConfig()->cleanCache();
					break;
					default :
						 Mage::getSingleton("adminhtml/session")->addError(Mage::helper("datafeedmanager")->__("An error occurs while connecting wyomind license server (500).<br>
							<a target='_blank' href='http://www.wyomind.com/license_activation/?method=post".$license_request."'>Go to http://www.wyomind.com/license_activation/</a>"));
						 Mage::getConfig()->saveConfig("datafeedmanager/license/activation_code", "", "default", "0");
						Mage::getConfig()->cleanCache();
					break;
				}
			} 
			catch (Exception $e) {
				Mage::getSingleton("adminhtml/session")->addError(Mage::helper("datafeedmanager")->__("An error occurs while connecting wyomind license server (404).<br>
										<a target='_blank' href='http://www.wyomind.com/license_activation/?method=post".$license_request."'>Go to http://www.wyomind.com/license_activation/</a>"));
				 Mage::getConfig()->saveConfig("datafeedmanager/license/activation_code", "", "default", "0");
					 Mage::getConfig()->cleanCache();
				return;
			}
			
		}
	}
	
	protected function _initAction() {

		$this->loadLayout()
		->_setActiveMenu('catalog/datafeedmanager')
		->_addBreadcrumb($this->__('Data feed Manager'),('Data feed Manager'));

		return $this;
	}

	public function indexAction() {
			$this->check_activation();
		$this->_initAction()
		->renderLayout();
			
	}


	public function editAction() {
	$this->check_activation();
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('datafeedmanager/datafeedmanager')->load($id);

		if ($model->getId() || $id == 0) {
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data)) {
				$model->setData($data);
			}

			Mage::register('datafeedmanager_data', $model);

			$this->loadLayout();
			$this->_setActiveMenu('catalog/datafeedmanager') ->_addBreadcrumb(Mage::helper('datafeedmanager')->__('Data Feed Manager'),('Data Feed Manager'));
			$this->_addBreadcrumb(Mage::helper('datafeedmanager')->__('Data Feed Manager'),('Data Feed Manager'));
			 
			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

			$this->_addContent($this->getLayout()
          	->createBlock('datafeedmanager/adminhtml_datafeedmanager_edit'))
            ->_addLeft($this->getLayout()
            ->createBlock('datafeedmanager/adminhtml_datafeedmanager_edit_tabs'));
            
           $this->renderLayout();

		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('datafeedmanager')->__('Item does not exist'));
			$this->_redirect('*/*/');
		}
	}

	public function newAction() {
		$this->check_activation();
		$this->_forward('edit');
	}



	public function saveAction()
	{
				$this->check_activation();
		// check if data sent
		if ($data = $this->getRequest()->getPost()) {
				
			 
			 
			// init model and set data
			$model = Mage::getModel('datafeedmanager/datafeedmanager');

			if ($this->getRequest()->getParam('id')) {
				$model ->load($this->getRequest()->getParam('id'));

				if ($model->getFeedName() && file_exists($model->getPreparedFilename())){
					unlink($model->getPreparedFilename());
				}
			}


			$model->setData($data);

			// try to save it
			try {
				// save the data
				$model->save();
				// display success message
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('datafeedmanager')->__('The data feed configuration has been saved.'));
				// clear previously saved data from session
				Mage::getSingleton('adminhtml/session')->setFormData(false);
			
				if ($this->getRequest()->getParam('continue')) {
                    $this->getRequest()->setParam('id', $model->getId());
                    $this->_forward('edit');
                    return;
                }
            	
                
                // go to grid or forward to generate action
                if ($this->getRequest()->getParam('generate')) {
                    $this->getRequest()->setParam('feed_id', $model->getId());
                    $this->_forward('generate');
                    return;
                }
                $this->_redirect('*/*/');
                return;
				

			} catch (Exception $e) {
				// display error message
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				// save data in session
				Mage::getSingleton('adminhtml/session')->setFormData($data);
				// redirect to edit form
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
				return;
			}
		}
		$this->_redirect('*/*/');

	}

	/**
	 * Delete action
	 */
	public function deleteAction()
	{
			$this->check_activation();
		// check if we know what should be deleted
		if ($id = $this->getRequest()->getParam('id')) {
			try {
				// init model and delete
				$model = Mage::getModel('datafeedmanager/datafeedmanager');
				$model->setId($id);
				// init and load datafeedmanager model


				$model->load($id);
				// delete file
				if ($model->getFeedName() && file_exists($model->getPreparedFilename())){
					unlink($model->getPreparedFilename());
				}
				$model->delete();
				// display success message
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('datafeedmanager')->__('The data feed configuration has been deleted.'));
				// go to grid
				$this->_redirect('*/*/');
				return;

			} catch (Exception $e) {
				// display error message
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				 
				$this->_redirect('*/*/');
				return;
			}
		}
		// display error message
		Mage::getSingleton('adminhtml/session')->addError(Mage::helper('datafeedmanager')->__('Unable to find the data feed configuration to delete.'));
		// go to grid
		$this->_redirect('*/*/');
	}

	public function sampleAction()
	{

		// init and load datafeedmanager model
		$id = $this->getRequest()->getParam('feed_id');
		$limit = $this->getRequest()->getParam('limit');

		$datafeedmanager = Mage::getModel('datafeedmanager/datafeedmanager');
		$datafeedmanager->setId($id);
		$datafeedmanager->_limit=$limit;

		$datafeedmanager->_display=true;

		// if datafeedmanager record exists
		if ($datafeedmanager->load($id)) {
			 
			try {
				$content=$datafeedmanager->generateFile();
				if($datafeedmanager->_demo){
					 $this->_getSession()->addError(Mage::helper('datafeedmanager')->__("Invalid license."));
					 Mage::getConfig()->saveConfig('datafeedmanager/license/activation_code', '', 'default', '0');
					 Mage::getConfig()->cleanCache();
					 $this->_redirect('*/*/');
				}
				else print($content);
			}
			catch (Mage_Core_Exception $e) {
				$this->_getSession()->addError($e->getMessage());
				$this->_redirect('*/*/');
			}
			catch (Exception $e) {
					
				$this->_getSession()->addException($e, Mage::helper('datafeedmanager')->__('Unable to generate the data feed.'));
				$this->_redirect('*/*/');
			}
		}
		else {
			$this->_getSession()->addError(Mage::helper('datafeedmanager')->__('Unable to find a data feed to generate.'));
		}



	}

	public function generateAction()
	{

		// init and load datafeedmanager model
		$id = $this->getRequest()->getParam('feed_id');

		$datafeedmanager = Mage::getModel('datafeedmanager/datafeedmanager');
		$datafeedmanager->setId($id);
		$limit = $this->getRequest()->getParam('limit');
		$datafeedmanager->_limit=$limit;


		// if datafeedmanager record exists
		if ($datafeedmanager->load($id)) {
			 
			 
			try {
				$datafeedmanager->generateFile();
				$ext=array(1=>'xml',2=>'txt',3=>'csv');
				if($datafeedmanager->_demo){
					 $this->_getSession()->addError(Mage::helper('datafeedmanager')->__("Invalid license."));
					 Mage::getConfig()->saveConfig('datafeedmanager/license/activation_code', '', 'default', '0');
					 Mage::getConfig()->cleanCache();
				}
				else $this->_getSession()->addSuccess(Mage::helper('datafeedmanager')->__('The data feed "%s" has been generated.', $datafeedmanager->getFeedName().'.'.$ext[$datafeedmanager->getFeedType()]));
			}
			catch (Mage_Core_Exception $e) {
				$this->_getSession()->addError($e->getMessage());
			}
			catch (Exception $e) {
					
				$this->_getSession()->addException($e, Mage::helper('datafeedmanager')->__('Unable to generate the daat feed.'));
			}
		}
		else {
			$this->_getSession()->addError(Mage::helper('datafeedmanager')->__('Unable to find a data feed to generate.'));
		}

		// go to grid
		$this->_redirect('*/*/');
	}

	 
	 
}
