<?php

/**
 * Magento
 *
 * @category   Kega
 * @package    Kega_Checkout
 */
class Kega_Wizard_WizardController extends Mage_Core_Controller_Front_Action
{
	var $apiId = '314100796';
	var $apiPass = 'NkcMByWtI5Mdt2pXG4WDmK';

	/**
	 * Kega_Wizard_WizardController::preDispatch
	 *
	 *
	 * @param void
	 * @return void
	 */
    public function preDispatch()
    {
        parent::preDispatch();
        if (!$this->getRequest()->isDispatched()) {
            return;
        }

        Mage::register('isWizard', true);

		$this->verifyCartContent();
    }

	/**
	 * Kega_Wizard_WizardController::verifyCartContent
	 * Checks if the user has products in his cart
	 * Redirects back to the cart page when no products were found
	 *
	 * @param void
	 * @return void
	 */
	public function verifyCartContent()
	{
		if (!preg_match('/thanks/', $_SERVER['REQUEST_URI'])) {
			$quote = $this->_getWizard()->getQuote();
			if (!$quote->hasItems() || $quote->getHasError()) {
				$this->_redirect('checkout/cart');
				return;
			}
		}
	}
	
	protected function _addressReeksPostcodeSearch($username, $password, $postcode, $houseno)
	{
		/* Connect to the Address service at ws1.webservices.nl.
		 * Specify the path to the service:
		 *  /rpc/get-serialized  indicates that we are using GET
		 *                       requests and we want output in php
		 *                       serialized format
		 *  /rpc/get-xmlrpc      indicates that we are using GET requests
		 *                       and we want output in XML-RPC format
		 *  /rpc/get-simplexml   indicates that we are using GET requests
		 *                       and we want output in a simple xml format
		 *
		 * Specify the port for https
		 */
		$host = Mage::getStoreConfig('webservices/general/host');
		$path = Mage::getStoreConfig('webservices/general/path');
		$port = 443;


		// The function we want to call and all its arguments are specified
		// in the path. The function name should be the first parameter,
		// followed by the username & password and any method specific
		// parameters.
		// Use the addressReeksPostcodeSearch function to look up the
		// address with postcode 1234AB and house number 12.
		$path .= 'addressReeksPostcodeSearch/'.$username.
		  	   '/'.$password.'/'.$postcode.$houseno;

		$fp = fsockopen('ssl://'.$host, $port, $errno, $errstr, $timeout=10);

		if(!$fp)
		{
		  print('Error connecting to http webservice: '.$errstr);
		}
		else
		{
		  // send the GET request
		  fputs($fp, "GET ".$path." HTTP/1.1\r\n");
		  fputs($fp, "Host: $host\r\n\r\n");
			$output = '';

		  // get server response
		  while( !feof( $fp ) )
		    $output .= fread( $fp, 1024 );

		  fclose($fp);

		  // parse server response
		  if( !preg_match("~\r?\n\r?\n(.*)$~is", $output, $matches) )
		  {
		    print('Error parsing server response.');
		  }
		  else
		  {
			  if (strpos($matches[1], "not_enough_credits")) {
				error_log('Kega_Wizard_WizardController->_addressReeksPostcodeSearch() ~ Insufficient credits');
				mail(Mage::getStoreConfig('webservices/general/notificationemail'), Mage::app()->getStore()->getName() . ' Magento: postcode check', 'Credits zijn op.');
				return false;
			  }
		    // matches[1] contains the server response without HTTP headers
		    // After unserializing the response we obtain an array with the
		    // function results, or an object representing an error
		    // (with 'code' and 'message' variables).
		    // If successful $result will be an array:
		    //  print_r($result) =
		    //    Array
		    //      (
		    //          [reeksid] => 0
		    //          [huisnr_van] => 0
		    //          [huisnr_tm] => 0
		    //          [wijkcode] => 1000
		    //          [lettercombinatie] => AA
		    //          [reeksindicatie] => 0
		    //          [straatid] => 0
		    //          [straatnaam] => streetname
		    //          [straatnaam_nen] => streetname
		    //          [straatnaam_ptt] => STREETNAME
		    //          [straatnaam_extract] => STREE
		    //          [plaatsid] => 0
		    //          [plaatsnaam] => CITYNAME
		    //          [plaatsnaam_ptt] => CITYNAME
		    //          [plaatsnaam_extract] => CITY
		    //          [gemeenteid] => 0
		    //          [gemeentenaam] => DISTRICTNAME
		    //          [gemeentecode] => 0
		    //          [cebucocode] => 0
		    //          [provinciecode] => P
		    //          [provincienaam] => Province
		    //      )
		    $result = unserialize($matches[1]);
		  }
		}
		return $result;
	}

	protected function _validateAddressByPostcode($postcode, $houseno)
	{
		// username and password for webservices.nl
		$username = Mage::getStoreConfig('webservices/general/user');
		$password = Mage::getStoreConfig('webservices/general/password');

		$data = $this->_addressReeksPostcodeSearch($username, $password, $postcode, $houseno);
		if (!is_array($data)) {
			return false;
		}
		return array(
			'postcode' => $postcode,
			'number' => $houseno,
			'street' => $data['straatnaam'],
			'city' => $data['plaatsnaam'],
			'country_id' => 'NL'
		);
	}

	public function validateAddressAction()
	{
		$data = $this->getRequest()->getPost('address', array());

		if (empty($data['postcode']) || empty($data['number'])) {
			die('Invalid request');
		}

		try {
			$result = $this->_validateAddressByPostcode($data['postcode'], $data['number']);
		} catch (Exception $e) {
			 print_r($e);
			die('Fatal error');
		}
		echo(Zend_Json::encode($result));
		exit();
	}

	/**
	 * Kega_Wizard_WizardController::indexAction
	 *
	 * @param void
	 * @return void
	 */
    public function indexAction()
    {

        if(Mage::getSingleton('customer/session')->isLoggedIn()) {
            $this->_redirect('*/*/billing');
        }

    	$this->loadLayout();

    	$this->getLayout()->getBlock('head')->setTitle($this->__('Wizard'));
		$this->_initLayoutMessages('customer/session');
		$this->_initLayoutMessages('checkout/session');
		$this->renderLayout();
    }

    /**
     * Kega_Wizard_WizardController::billingAction
     *
     * @param void
     * @return void
     */
    public function billingAction()
    {
        $data = $this->_getCheckoutSession()->getCustomerFormData();
        if ($this->getRequest()->getParam('back') == 1) {
            $this->_getCheckoutSession()->setCustomerFormData($data);
        }

        $this->loadLayout();

        $this->getLayout()->getBlock('head')->setTitle($this->__('Wizard'));
        $this->_initLayoutMessages('customer/session');
        $this->_initLayoutMessages('checkout/session');
        $this->renderLayout();
    }


	/**
	 * Kega_Wizard_WizardController::registerAction
	 * Register user
	 *
	 * @param void
	 * @return void
	 */
	public function billingPostAction()
	{
		if (!$this->getRequest()->isPost()) {
			$this->getResponse()->setRedirect(
				Mage::getUrl('*/*')
			);
			return;
		}

		$data = $this->getRequest()->getPost();
		$customer = Mage::getSingleton('customer/session')->getCustomer();

        $addressData = array();
        $addressData['street'] = $data['street'];
        $addressData['postcode'] = $data['postcode'];
        $addressData['city'] = $data['city'];
        $addressData['telephone'] = $data['telephone'];
        $addressData['country_id'] = $data['country_id'];

		$addressData['prefix'] = $data['prefix'];
		$addressData['firstname'] = $data['firstname'];
		$addressData['lastname'] = $data['lastname'];
        $addressData['middlename'] = $data['middlename'];
		$addressData['region_id'] = 1; // Dummy region to skip validation

		// we use the customer address so we can also have the address on customer session updated
		$address = $customer->getDefaultBillingAddress();
		foreach($addressData as $key => $value) {
			$address[$key] = $value;
		}
		$address->save();

		$errors = $address->validate();

        // check if street number and name is filled - the validation only check if $data['street'] is empty
        if (!empty($addressData['street'][0]) || !empty($addressData['street'][1])) {
            if (empty($addressData['street'][0])) {
                $errors[] = $this->__('Please enter the street name');
            }

            if (empty($addressData['street'][1])) {
                $errors[] = $this->__('Please enter the street number');
            }
        }


		if (!is_array($errors)) {
			$errors = array();
		}

		$allData = $data;
		try {
			$validationCustomer = $customer->validate();
			if (is_array($validationCustomer)) {
				$errors = array_merge($validationCustomer, $errors);
			}

            if ($this->_customerEmailExists($address->getEmail(), Mage::app()->getWebsite()->getId())) {
				$errors['duplicate-email'] = true;

				$this->_getSession()->addError(
					$this->__('There is already a customer registered using this email address')
				);
            }

			if ((count($errors) == 0) === true) {
				$customer->save();

                if ($this->_getSession()->getNewRetailCustomerNo()) {
                    $retailCustomerNo = $this->_getSession()->getNewRetailCustomerNo();
                    $this->_getSession()->setNewRetailCustomerNo(false);
                    Mage::getModel('import/client')->markRetailCustomerNoAsUsed($retailCustomerNo, $customer->getId());
                }

				Mage::getSingleton('checkout/session')->getQuote()->getShippingAddress()
						->setCountryId($allData['country_id'])
						->setCollectShippingRates(true)->save();
				Mage::getSingleton('checkout/session')->getQuote()->save();

                // update billing address
                Mage::getSingleton('checkout/session')->getQuote()->getBillingAddress()
						->setData($address->getData())
						->setCollectShippingRates(true)->save();
				Mage::getSingleton('checkout/session')->getQuote()->save();

				$this->_getCheckoutSession()->setCustomerFormData($allData);

                //update customer session data
                $this->_getSession()->setCustomer($customer);

				$this->getResponse()->setRedirect(
					Mage::getUrl('*/*/delivery') . '#wizard-delivery-details'
				);
				return;

			} else {
				$this->_getCheckoutSession()->setCustomerFormData($allData);
				if (is_array($errors)) {
					foreach ($errors as $errorMessage) {
						$this->_getSession()->addError($errorMessage);
					}
				}
				else {
					$this->_getSession()->addError($this->__('Invalid customer data'));
				}
			}
		}
		catch (Mage_Core_Exception $e) {
			error_log($e);
			$this->_getSession()->addError($e->getMessage());
		}
		catch (Exception $e) {
			error_log($e);
			$this->_getSession()->addException($e, $this->__('Can\'t save customer').$e->getMessage());
		}
		$this->_getCheckoutSession()->setCustomerFormData($allData);
		$this->getResponse()->setRedirect(
			Mage::getUrl('*/*') . '#wizard-personal-details'
		);
	}

	/**
	 * Kega_Wizard_WizardController::checkGiftcardAction
	 * Validate the gift card, add gift card to the customer session
	 *
	 * @param void
	 * @return void
	 */
	public function checkGiftcardAction()
	{
		$data = $this->getRequest()->getPost('giftcard', array());
		$customer = Mage::getSingleton('customer/session')->getCustomer();

		$giftcard = Mage::getModel('intersolve/giftcard');
		$card = $giftcard->getCard($data['number'], $data['pin']);

		if (!$card) {
			$this->_getSession()->addError($this->__('No giftcard found with the given number'));
		} else if ($card && $card->Balance == 0) {
			$this->_getSession()->addError($this->__('The given giftcard does not have a positive balance'));
		}
		else {
			$this->_getCheckoutSession()->setGiftcardCardId($data['number']);
			$this->_getCheckoutSession()->setGiftcardCardPin($data['pin']);
			$this->_getCheckoutSession()->setGiftcardCustomerId($customer->getId());
		}

		$this->getResponse()->setRedirect(
			Mage::getUrl('*/*/payment') . '#wizard-payment-details'
		);
		return;
	}

	/**
	 * Kega_Wizard_WizardController::removeGiftcardAction
	 * Removes the active giftcard from the customer session
	 *
	 * @param void
	 * @return void
	 */
	public function removeGiftcardAction()
	{
		$this->_getCheckoutSession()->setGiftcardCardId(false);
		$this->_getCheckoutSession()->setGiftcardCardPin(false);
		$this->_getCheckoutSession()->setGiftcardCustomerId(false);

		$this->getResponse()->setRedirect(
			Mage::getUrl('*/*/payment') . '#wizard-payment-details'
		);
		return;
	}

	/**
	 * Kega_Wizard_WizardController::deliveryAction
	 *
	 *
	 * @param void
	 * @return void
	 */
    public function deliveryAction()
    {
		$customer = Mage::getSingleton('customer/session')->getCustomer();

		if (!$customer->getDefaultBilling()) {
			$this->getResponse()->setRedirect(
				Mage::getUrl('*/*') . '#wizard-personal-details'
			);
			return;
		}

		$this->_getWizard()->getQuote()->collectTotals();
		$this->_getWizard()->getQuote()->save();

		$this->_getCheckoutSession()->setFinishedRegistration(true);


		$this->_getWizard()->saveBillingAddress($customer->getDefaultBilling());

		$this->loadLayout();
		$this->getLayout()->getBlock('head')->setTitle($this->__('Wizard'));
		$this->_initLayoutMessages('customer/session');
		$this->_initLayoutMessages('checkout/session');
		$this->renderLayout();
    }

	/**
	 * Kega_Wizard_WizardController::fetchKialaAction
	 *
	 * @param void
	 * @return void
	 */
	public function fetchKialaAction()
	{
		$this->_getCheckoutSession()->getQuote()
			->getShippingAddress()->collectShippingRates()->save();

		$this->_getCheckoutSession()->setShipmentChoice('kiala-shipment');

		$this->_getCheckoutSession()->getQuote()
			->getShippingAddress()->setShippingMethod('kiala_kiala');
        $this->_getCheckoutSession()->getQuote()->collectTotals()->save();

		$this->_getCheckoutSession()->setKialaPointData(
			$this->getRequest()->getParams()
		);
		$this->getResponse()->setRedirect(
			Mage::getUrl('*/*/delivery') . '#wizard-delivery-details'
		);
		return;
	}

	/**
	 * Kega_Wizard_WizardController::paymentAction
	 * - Stores shipment data into checkout session
	 * - Overrides shipment address when needed
	 * - Set shipment to Autoshipping method when not chosen for Kiala
	 *
	 * @param void
	 * @return void
	 * @todo checks for "isInstoreorder()" need to be removed?
	 */
    public function paymentAction()
    {
        $customer = Mage::getSingleton('customer/session')->getCustomer();
		if (!$customer->getDefaultShipping()) {
			$this->getResponse()->setRedirect(
				Mage::getUrl('*/*/delivery') . '#wizard-delivery-details'
			);
			return;
		}

		// Go back to delivery step when chosen to search for a pickup store
		if ($criteria = $this->getRequest()->getPost('pickup-store-search', false)) {
			Mage::getSingleton('checkout/session')->setPickupStoreCriteria($criteria);
			$this->_getCheckoutSession()->setShipmentChoice('store-shipment');
			$this->getResponse()->setRedirect(
				Mage::getUrl('*/*/delivery') . '#wizard-delivery-details'
			);
			return;
		}

		if ($this->getRequest()->isPost()) {
			$shipment = $this->getRequest()->getPost('shipment', 'invoice-shipping');

			Mage::getSingleton('checkout/session')->setShipmentChoice($shipment);
			Mage::getSingleton('checkout/session')->setShipmentData(
				new Varien_Object($this->getRequest()->getPost('shipping', array()))
			);

			$errors = array();

			// update shipping address
			$customer = Mage::getSingleton('customer/session')->getCustomer();
			$errors = $this->_getWizard()->saveShippingAddress(
				$shipment,
				$customer->getDefaultShipping(),
				$this->getRequest()->getPost('shipping', array())
			);

			if ($shipment == 'store-shipment') {
				$this->setStorePickup();
			}
			else {
				$this->_getCheckoutSession()->setStorePickupData(false);
			}

			if (count($errors)) {
				foreach($errors as $error) {
					$this->_getSession()->addError($this->__($error));
				}

				$this->getResponse()->setRedirect(
					Mage::getUrl('*/*/delivery') . '#wizard-delivery-details'
				);
				return;
			}
		}

		$this->_getWizard()->getQuote()->collectTotals();
		$this->_getWizard()->getQuote()->save();

		$this->loadLayout();

		$this->getLayout()->getBlock('head')->setTitle($this->__('Wizard'));
		$this->_initLayoutMessages('customer/session');
		$this->_initLayoutMessages('checkout/session');
		$this->renderLayout();
    }

	/**
	 * Kega_Wizard_WizardController::setStorePickup
	 * - Save the chosen store to the storepickup data array
	 * -
	 *
	 * @param void
	 * @return void
	 */
	public function setStorePickup()
	{
		$this->_getCheckoutSession()->getQuote()
			->getShippingAddress()->collectShippingRates()->save();

		$this->_getCheckoutSession()->setShipmentChoice('store-shipment');

		$this->_getCheckoutSession()->getQuote()
			->getShippingAddress()->setShippingMethod('storepickup_store');
        $this->_getCheckoutSession()->getQuote()->collectTotals()->save();

		$this->_getCheckoutSession()->setStorePickupData(
			$this->getRequest()->getPost('pickup-store', false)
		);
	}

	/**
	 * Kega_Wizard_WizardController::orderAction
	 * - Saves the chosen payment
	 * - Creates an order from the session quote
	 *
	 * @param void
	 * @return void
	 */
    public function orderAction()
    {
		if (!$this->getRequest()->isPost()) {
			$this->getResponse()->setRedirect(
				Mage::getUrl('*/*/payment') . '#wizard-payment-details'
			);
			return;
		}

		$payment = $this->getRequest()->getPost('payment', array());
		if (!$payment || empty($payment['method'])) {
			$this->_getSession()->addError($this->__('You must select a paymentmethod to continue your order.'));
			$this->getResponse()->setRedirect(
				Mage::getUrl('*/*/payment') . '#wizard-payment-details'
			);
			return;
		}

		try {
		    $result = $this->_getWizard()->savePayment($payment);
		}
		catch (Mage_Payment_Exception $e) {
			if ($e->getFields()) {
				$result['fields'] = $e->getFields();
			}
			$result['error'] = $e->getMessage();
		}
		catch (Exception $e) {
			$result['error'] = $e->getMessage();
		}

		try {

		    $this->_getWizard()->saveOrder($payment);

			$redirectUrl = $this->_getWizard()->getCheckout()->getRedirectUrl();
			$result['success'] = true;
		}
		catch (Exception $e) {
			$result['success'] = false;
            $result['message'] = $e;
			$this->_getWizard()->getQuote()->save();
			$this->_getSession()->addError(
				$this->__('There was an error processing your order. Please contact us or try again later.')
			);
		}

		if ($result['success']) {
			$totals = $this->_getWizard()->getQuote()->getTotals();
			$grandTotal = $totals['grand_total']->getValue();

			if (floatval($grandTotal) === 0.00) {
				$this->_getWizard()->finalizeOrder();
				$redirectUrl = false;
			}

			if ($redirectUrl) {
				$this->_redirectUrl($redirectUrl);
			} else {
				$this->_redirect('checkout/wizard/thanks');
			}
		} else {
			$this->_getSession()->addError(
				$this->__('There was an error processing your order. Please contact us or try again later.')
			);
			$this->getResponse()->setRedirect(
				Mage::getUrl('*/*/payment') . '#wizard-payment-details'
			);
		}
		return;

		$this->loadLayout();
		$this->getLayout()->getBlock('head')->setTitle($this->__('Wizard'));
		$this->_initLayoutMessages('customer/session');
		$this->_initLayoutMessages('checkout/session');
		$this->renderLayout();
    }

	/**
	 * Kega_Wizard_WizardController::thanksAction
	 * - Clears the checkout session
	 *
	 * @param void
	 * @return void
	 */
    public function thanksAction()
    {
        $lastQuoteId = Mage::getSingleton('checkout/session')->getLastQuoteId();
        $lastOrderId = Mage::getSingleton('checkout/session')->getLastOrderId();

        $this->_getWizard()->cleanupOrderSession();

		$this->loadLayout();

		$this->getLayout()->getBlock('head')->setTitle($this->__('Wizard'));
		$this->_initLayoutMessages('customer/session');

        // we need this for google analytics observer - setGoogleAnalyticsOnOrderSuccessPageView
        // it has to be after the layout was loaded because the layout is used in the observer
        Mage::dispatchEvent('checkout_onepage_controller_success_action', array('order_ids' => array($lastOrderId)));

        $this->renderLayout();
	}

	/**
	 * Kega_Wizard_WizardController::couponPostAction
	 * - Add or remove coupon discount for quote
	 *
	 * @param void
	 * @return void
	 */
	public function couponPostAction()
    {
        if (!$this->_getCart()->getQuote()->getItemsCount()) {
			$this->_redirect('checkout/cart');
			return;
        }

        $couponCode = (string) $this->getRequest()->getParam('coupon_code');
        if ($this->getRequest()->getParam('remove') == 1) {
            $couponCode = '';
        }
        $oldCouponCode = $this->_getQuote()->getCouponCode();

        if (!strlen($couponCode) && !strlen($oldCouponCode)) {
			$this->_redirectReferer(null, '#wizard-payment-details');
			return;
        }

        try {
            $this->_getQuote()->getShippingAddress()->setCollectShippingRates(true);
            $this->_getQuote()->setCouponCode(strlen($couponCode) ? $couponCode : '')
                ->collectTotals()
                ->save();

            if ($couponCode) {
                if ($couponCode == $this->_getQuote()->getCouponCode()) {
                    $this->_getCheckoutSession()->addSuccess(
                        $this->__('Coupon code "%s" was applied successfully.', Mage::helper('core')->htmlEscape($couponCode))
                    );
                }
                else {
                    $this->_getCheckoutSession()->addError(
                        $this->__('Coupon code "%s" is not valid.', Mage::helper('core')->htmlEscape($couponCode))
                    );
                }
            } else {
                $this->_getCheckoutSession()->addSuccess($this->__('Coupon code was canceled successfully.'));
            }

        }
        catch (Mage_Core_Exception $e) {
            $this->_getCheckoutSession()->addError($e->getMessage());
        }
        catch (Exception $e) {
            $this->_getCheckoutSession()->addError($this->__('Can not apply coupon code.'));
        }

		$this->_redirectReferer(null, '#wizard-payment-details');
		return;
    }

    /**
     * Kega_Wizard_WizardController::_getWizard::_customerEmailExists
	 * Check if customer email exists
     *
     * @param string $email
     * @param int $websiteId
     * @return false|Mage_Customer_Model_Customer
     */
    protected function _customerEmailExists($email, $websiteId = null)
    {
        $customer = Mage::getModel('customer/customer');
        if ($websiteId) {
            $customer->setWebsiteId($websiteId);
        }
        $customer->loadByEmail($email);
        if ($customer->getId()) {
            return $customer;
        }
        return false;
    }
    
    /**
     * Redirects to customer account post login
     * The purpose of this function is to set the setBeforeRedirectUrl for customer login so the customer is redirected after login to 
     * checkout
     * 
     */
    public function loginPostAction()
    {
    	$loginData = $this->getRequest()->getParam('login');
    	
    	Mage::getSingleton('customer/session')->setBeforeAuthUrl(Mage::getUrl('checkout/wizard/index/'));    	
    	return $this->_forward('loginPost', 'account', 'customer');
    }

	/**
	 * Kega_Wizard_WizardController::_getWizard
	 *
	 *
	 * @param void
	 * @return void
	 */
	protected function _getWizard()
	{
		return Mage::getSingleton('wizard/wizard');
	}

	/**
	 * Kega_Wizard_WizardController::_getCheckoutSession
	 *
	 *
	 * @param void
	 * @return Mage_Checkout_Model_Session
	 */
    protected function _getCheckoutSession()
    {
        return Mage::getSingleton('checkout/session');
    }

	/**
	 * Kega_Wizard_WizardController::_getSession
	 * Retrieve shopping cart model object
	 *
	 * @param void
	 * @return Mage_Customer_Model_Session
	 */
    protected function _getSession()
    {
        return Mage::getSingleton('customer/session');
    }

	/**
	 * Kega_Wizard_WizardController::_getCart
	 * Retrieve shopping cart model object
	 *
	 * @param void
	 * @return Mage_Checkout_Model_Cart
	 */
    protected function _getCart()
    {
        return Mage::getSingleton('checkout/cart');
    }

	/**
	 * Kega_Wizard_WizardController::_getQuote
	 * Get current active quote instance
	 *
	 * @param void
	 * @return Mage_Sales_Model_Quote
	 */
    protected function _getQuote()
    {
        return $this->_getCart()->getQuote();
    }

	/**
	 * Kega_Wizard_WizardController::_getRefererUrl
	 * Use own getRefererUrl method for now
	 * Magento Core can't handle #anchor's in an url
	 *
     * @param   string $defaultUrl
     * @return  Mage_Core_Controller_Varien_Action
     */
    protected function _redirectReferer($defaultUrl=null, $hash=null)
    {
        $refererUrl = $this->_getRefererUrl();
        if (empty($refererUrl)) {
            $refererUrl = empty($defaultUrl) ? Mage::getBaseUrl() : $defaultUrl;
        }

		$this->getResponse()->setRedirect($refererUrl . $hash);
        return $this;
    }


    /**
     * Sets the client session data
     * @param array $session
     * @param array $clientData
     * @return array
     */
    private function mapClientDataSession($session, $clientData)
    {
        $translationsClientSession = array(
            'salutation_code' => 'prefix',
			'firstname' => 'firstname',
            'initial' => 'middlename',
			'last_name' => 'lastname',
            'phone_number' => 'telephone',
            'email_address' => 'email',
            'country_code' => 'country_id',
            'post_code' => 'postcode',
			'street_name' => 'street_name',
			'house_no' => 'house_no',
            'house_no_addition' => 'addition',
			'city' => 'city',
		);

		foreach($translationsClientSession as $key => $session_key) {
            if (isset($clientData[$key])) {
                $session[$session_key] = $clientData[$key];
            }
        }

        $session['prefix'] = Mage::helper('import/mapping')
                                        ->getMageSalutationCode($clientData['salutation_code']);
        $session['street'] = array(
            '0' => $clientData['street_name'],
            '1' => empty($clientData['house_no'])? $clientData['street_no']: $clientData['house_no'],
            '2' => $clientData['house_no_addition'],
        );

        return $session;
    }
}
