<?php
include("Kega/Wizard/controllers/WizardController.php");

/**
 * Kega_Wizard_InstoreController
 * Extend of Wizard controller to handle instore specific payment steps.
 */
class Kega_Wizard_InstoreController extends Kega_Wizard_WizardController
{
    /**
     * Kega_Wizard_InstoreController::registerAction()
     * do register step for instore orders (guest mode, so no customer will be created)
     *
     * @param void
     * @return void
     */
    public function registerAction()
    {
        $accountData = $this->getRequest()->getPost('account', array());

        foreach($accountData as $key => $value) {
            Mage::getSingleton('checkout/session')->getQuote()->setData(('customer_' . $key), $value);
        }

        // set method
        Mage::getSingleton('checkout/session')->getQuote()->setCheckoutMethod('guest');

        // Billing address
        $addressData = $this->getRequest()->getPost('address', array());
		$addressData['prefix'] = $accountData['prefix'];
		$addressData['firstname'] = $accountData['firstname'];
		$addressData['lastname'] = $accountData['lastname'];
		$addressData['region_id'] = 1; // Dummy region to skip validation
        $address = Mage::getSingleton('checkout/session')->getQuote()->getBillingAddress();
        $address->addData($addressData);

        if (($validateRes = $address->validate())!==true) {
            return array('error' => 1, 'message' => $validateRes);
        }

        $address->implodeStreetAddress();
        $address->save();

        // Shipping address
        $billing = clone $address;
        $billing->unsAddressId()->unsAddressType();

        $shipping = Mage::getSingleton('checkout/session')->getQuote()->getShippingAddress();
        $shippingMethod = $shipping->getShippingMethod();
        $shipping->addData($billing->getData())
            ->setSameAsBilling(1)
            ->setShippingMethod($shippingMethod)
            ->setCollectShippingRates(true);
        $shipping->implodeStreetAddress();
        $shipping->save();

        Mage::getSingleton('checkout/session')->getQuote()->collectTotals();

        Mage::getSingleton('checkout/session')->getQuote()->getShippingAddress()
						->setCountryId($addressData['country_id'])
						->setCollectShippingRates(true)->save();

        Mage::getSingleton('checkout/session')->getQuote()->save();

        $allData = array_merge(
			$this->getRequest()->getPost('account', array()),
			$this->getRequest()->getPost('address', array()),
			array('do' => $this->getRequest()->getPost('do'))
		);

        $this->_getCheckoutSession()->setCustomerFormData($allData);

		$this->getResponse()->setRedirect(
			Mage::getUrl('*/*/delivery') . '#wizard-delivery-details'
		);
		return;
    }

	/**
	 * Kega_Wizard_InstoreController::deliveryAction
	 * Extended: Default billing address not mandatory for instore payments
	 * because there is no customer
	 *
	 * @param void
	 * @return void
	 */
    public function deliveryAction()
    {
		$this->_getWizard()->getQuote()->collectTotals();
		$this->_getWizard()->getQuote()->save();

		$this->loadLayout();
		$this->getLayout()->getBlock('head')->setTitle($this->__('Wizard'));
		$this->_initLayoutMessages('customer/session');
		$this->_initLayoutMessages('checkout/session');
		$this->renderLayout();
    }

    /**
	 * Kega_Wizard_InstoreController::paymentAction
	 * Extended: Customer not mandatory so logic is al little different
	 *
	 * @param void
	 * @return void
	 */
    public function paymentAction()
    {
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

			// set shipping addres when it's not the billing address
		    if($shipment != 'invoice-shipping' && $shipment != 'store-shipment') {
                $data = $this->getRequest()->getPost('shipping');
                $shipping = Mage::getSingleton('checkout/session')->getQuote()->getShippingAddress();

                $shipping->addData($data);
                $shipping->implodeStreetAddress();
                $shipping->save();
		    }

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
     * Kega_Wizard_WizardController::guestOrder()
     * Handle saving guest orders.
     * Extended: Specific actions for instore order
     *
     * @param void
     * @return void
     */
    public function orderAction()
    {
        $payment = $this->getRequest()->getPost('payment', array());
        $payment['method'] = 'instore';
        $payment['cc_type'] = 'FILIAAL';

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

        // lookup order
		$order = $this->_getWizard()->getLastRealOrderFromSession();

		// set status to "placed in store"
		$order->setState('in_store_ordered', true);
        $order->setInstorecode(Mage::helper('wizard')->getInstorecode());
        $order->save();

		// find deposit amount
		$maxDepositAmount = $order->getGrandTotal();
		if(isset($payment['deposit-option']) && $payment['deposit-option'] == 'no-deposit') {
		    $depositAmount = 0;
		} else if(isset($payment['deposit-option']) && $payment['deposit-option'] == 'partial-deposit') {
		    $depositAmount = $payment['deposit-amount'];

		    if($depositAmount > $maxDepositAmount) {
		        $depositAmount = $maxDepositAmount;
		    }
		} else {
		    $depositAmount = $maxDepositAmount;
		}

		$invoice = $this->_getWizard()->createDownpaymentInvoice($order, $depositAmount);

        Mage::getModel('core/resource_transaction')
            ->addObject($invoice)
            ->addObject($invoice->getOrder())
            ->save();

		if ($result['success']) {
		    $this->_redirect('*/*/thanks');
		    return;
		} else {
			$this->_getSession()->addError(
				$this->__('There was an error processing your order. Please contact us or try again later.')
			);
			$this->getResponse()->setRedirect(
				Mage::getUrl('*/*/payment') . '#wizard-payment-details'
			);
			return;
		}

		$this->loadLayout();
		$this->getLayout()->getBlock('head')->setTitle($this->__('Wizard'));
		$this->_initLayoutMessages('customer/session');
		$this->_initLayoutMessages('checkout/session');
		$this->renderLayout();
    }

	/**
	 * Kega_Wizard_InstoreController::thanksAction
	 * Extend: Show printable PDF with order acceptation barcode
	 *
	 * @param void
	 * @return void
	 */
    public function thanksAction()
    {
		$this->_getWizard()->cleanupOrderSession();

		$this->loadLayout();
		$this->getLayout()->getBlock('head')->setTitle($this->__('Wizard'));
		$this->_initLayoutMessages('customer/session');
        $this->renderLayout();
	}

	/**
	 * Kega_Wizard_InstoreController::_getWizard
	 * Extend: Return instorewizard
	 *
	 * @param void
	 * @return void
	 */
	protected function _getWizard()
	{
		return Mage::getSingleton('wizard/instorewizard');
	}
}
