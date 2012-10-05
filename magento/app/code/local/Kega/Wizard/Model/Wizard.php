<?php

/**
 * @category   Kega
 * @package    Kega_Wizard
 */
class Kega_Wizard_Model_Wizard extends Varien_Object
{
	/**
	 * Kega_Wizard_Model_Wizard::getCheckout
	 *
	 *
	 * @param void
	 * @return void
	 */
	public function getCheckout()
    {
        return Mage::getSingleton('checkout/session');
    }

	/**
	 * Kega_Wizard_Model_Wizard::getSession
     * Retrieve customer session
     *
	 * @param void
     * @return Mage_Customer_Model_session
     */
    public function getSession()
    {
        if ($this->_session === null) {
            $this->_session = Mage::getSingleton('customer/session');
        }
        return $this->_session;
    }

	/**
	 * Kega_Wizard_Model_Wizard::getQuote
	 *
	 *
	 * @param void
	 * @return void
	 */
	public function getQuote()
    {
        return $this->getCheckout()->getQuote();
    }

	/**
	 * Kega_Wizard_Block_Wizard::saveBillingAddress
     * Save the users billing address to the quote
	 * NOTE: No need to validate address, already during registration
	 *
	 * @param $billingAddressId integer
     * @return void
     */
	public function saveBillingAddress($billingAddressId)
	{
		$billing = $this->getQuote()->getBillingAddress();
		$address = Mage::getModel('customer/address')->load($billingAddressId);

		if ($address->getId()) {
			if ($address->getCustomerId() != $this->getQuote()->getCustomerId()) {
				die('address customerid does not match quote customerid');
			}
			$billing->importCustomerAddress($address);
		}
		$billing->save();

		if (!$this->getQuote()->isVirtual()) {
			// Retreive default shipping method/code.
			$shipping = $this->getQuote()->getShippingAddress();
			$shipping->setSameAsBilling(1)
				->setCollectShippingRates(true);
			$shipping->save();
		}
	}

	/**
	 * Kega_Wizard_Block_Wizard::saveShippingAddress
     * - Saves the users shipping address to the quote
	 * - Creates or update a seperate shipping address for the user account
	     when there is chosen for a different shipping address but defaultShipping == defaultBilling
	 * - Recalculates quote totals
	 * NOTE: validates address when using formdata, from billing is already done during registration
	 *
	 * @param $type string
	 * @param $shippingAddressId integer
	 * @param $data array
     * @return array
     */
	public function saveShippingAddress($type, $shippingAddressId, $data = array())
	{
		$shipping = $this->getQuote()->getShippingAddress();
		$customer = Mage::getSingleton('customer/session')->getCustomer();

		if ($type == 'invoice-shipping' || $type == 'kiala-shipment' || $type == 'store-shipment') {
			$address = Mage::getModel('customer/address')->load($shippingAddressId);

			if ($customer->getDefaultBilling() != $shippingAddressId) {
				$address = Mage::getModel('customer/address')->load(
					$customer->getDefaultBilling()
				);
				$address->setIsDefaultShipping(true);
				$address->save();
			}

			if ($address->getCustomerId() != $this->getQuote()->getCustomerId()) {
				die('address customerid does not match quote customerid');
			}
			$shipping->importCustomerAddress($address);
		}
		else if ($type == 'new-shipping') {

			# not asked for shipping but is required, use billing phone for now
			$data['telephone'] = $this->getQuote()->getBillingAddress()->getTelephone();

			if ($customer->getDefaultBilling() == $shippingAddressId) {
				$address = Mage::getModel('customer/address')
					->setData($data)
					->setIsDefaultBilling(false)
					->setIsDefaultShipping(true)
					->setId(null);
				$customer->addAddress($address);
				$customer->save();
			}
			else {
				$address = Mage::getModel('customer/address')->load(
					$customer->getDefaultShipping()
				);
				foreach($data as $key => $value) {
					$address[$key] = $value;
				}

				if (($validate = $address->validate()) !== true) {
					return $validate;
				}
				$address->save();
			}

			$shipping->addData($data);
			if (($validate = $shipping->validate()) !== true) {
				return $validate;
			}
		}

		$code = $type != 'kiala-shipment'
			? Mage::getStoreConfig('autoshipping/settings/method')
			: 'kiala_kiala'
		;
		if ($type == 'store-shipment') {
			$code = 'storepickup_store';
		}

		if ($code != 'kiala_kiala') {
			$this->getCheckout()->setKialaPointData(false);
		}
		if ($code != 'storepick_store') {
			$this->getCheckout()->setStorePickupData(false);
		}

		$shipping->collectShippingRates()->save();

		$shipping->setShippingMethod($code);
		$shipping->setCollectShippingRates(true);
		$shipping->save();

		# note: wonder if this is needed, looks like twice the same call
		# will investigate further in future commits
		$this->getCheckout()->getQuote()->collectTotals()->save();
		$this->getQuote()->collectTotals()->save();

		return array();
	}

	/**
	 * Kega_Wizard_Block_Wizard::savePayment
     * - Saves the chosen payment data to the quote
	 * - Stores the payment id in the checkout session
	 *
	 * @param $data array
     * @return void
     */
	public function savePayment($data)
	{

        $payment = $this->getQuote()->getPayment();
        $payment->importData($data);

		Mage::getSingleton('checkout/session')->setPaymentId($payment->getId());
        $this->getQuote()->getShippingAddress()->setPaymentMethod($payment->getMethod());
	}

	/**
	 * Kega_Wizard_Block_Wizard::saveOrder
     * - Creates an order from the the quote
	 * - Save additional payment data
	 * - Stores the payment id in the checkout session
	 *
	 * @param $data array
     * @return void
     */
	public function saveOrder($paymentData)
	{
		$service = Mage::getModel('sales/service_quote', $this->getQuote());
        $service->submitAll();

        $this->getCheckout()->setLastQuoteId($this->getQuote()->getId())
            ->setLastSuccessQuoteId($this->getQuote()->getId())
            ->clearHelperData();

        $order = $service->getOrder();
        if ($order) {
            Mage::dispatchEvent('checkout_type_onepage_save_order_after', array('order'=>$order, 'quote'=>$this->getQuote()));

            /**
             * a flag to set that there will be redirect to third party after confirmation
             * eg: paypal standard ipn
             */
            $redirectUrl = $this->getQuote()->getPayment()->getOrderPlaceRedirectUrl();
            /**
             * we only want to send to customer about new order when there is no redirect to third party
             */
            if (!$redirectUrl) {
                try {
                    $order->sendNewOrderEmail();
                } catch (Exception $e) {
                    Mage::logException($e);
                }
            }

			$this->saveAdditionalPaymentData($order, $paymentData);

            // add order information to the session
            $this->getCheckout()->setLastOrderId($order->getId())
                ->setRedirectUrl($redirectUrl)
                ->setLastRealOrderId($order->getIncrementId());

            // as well a billing agreement can be created
            $agreement = $order->getPayment()->getBillingAgreement();
            if ($agreement) {
                $this->getCheckout()->setLastBillingAgreementId($agreement->getId());
            }
        }

        // add recurring profiles information to the session
        $profiles = $service->getRecurringPaymentProfiles();
        if ($profiles) {
            $ids = array();
            foreach ($profiles as $profile) {
                $ids[] = $profile->getId();
            }
            $this->getCheckout()->setLastRecurringProfileIds($ids);
            // TODO: send recurring profile emails
        }

        Mage::dispatchEvent(
            'checkout_submit_all_after',
            array('order' => $order, 'quote' => $this->getQuote(), 'recurring_profiles' => $profiles)
        );
	}

	/**
	 * Kega_Wizard_Block_Wizard::saveAdditionalPaymentData
	 * - Saves giftcard and servicepoint data
	 *   as additional payment data when available
	 *
	 * @param $order Mage_Sales_Model_Order
     * @return void
     */
	public function saveAdditionalPaymentData($order, $paymentData)
	{
		$customer = Mage::getSingleton('customer/session')->getCustomer();

		$this->getQuote()->collectTotals();
		$totals = $this->getQuote()->getTotals();

		$paymentAdditional = $order->getPayment();
		$additionalData = $paymentAdditional->getAdditionalData()
			? unserialize($paymentAdditional->getAdditionalData())
			: array()
		;

		if (Mage::getSingleton('checkout/session')->getGiftcardCardId() && isset($totals['giftcard'])) {
			$additionalData['giftcard']['number'] = Mage::getSingleton('checkout/session')->getGiftcardCardId();
			$additionalData['giftcard']['amount'] = $totals['giftcard']->getValue();
		}

		if ($kiala = $this->getCheckout()->getKialaPointData()) {
			$additionalData['servicepoint']['id'] = $kiala['shortkpid'];
			foreach(array('kpname', 'zip', 'city', 'street') as $key) {
				if (!empty($kiala[$key])) {
					$additionalData['servicepoint'][$key] = $kiala[$key];
				}
			}
		}

		if (!empty($paymentData['cc_bank'])) {
			$additionalData['issuerId'] = $paymentData['cc_bank'];
		}

		if ($storeId = $this->getCheckout()->getStorePickupData()) {
			$store = Mage::getModel('store/store')->load($storeId);

			if ($store->getId()) {
				// Put a copy of store data into additionalData store_pickup field.
				$additionalData['store_pickup'] = $store->toArray();
			}
		}

		if (count($additionalData)) {
			$paymentAdditional->setAdditionalData(serialize($additionalData))->save();
		}

        $order->save();
	}

	/**
	 * Kega_Wizard_Block_Wizard::finalizeOrder
	 * - Called when the whole order is payed by giftcard
     * - Creates an invoice for the given order
	 * - Updates the status of the order to payed
	 *
	 * @param void
     * @return void
     */
	public function finalizeOrder()
	{
		$order = Mage::getModel('sales/order')->loadByIncrementId(
			$this->getCheckout()->getLastRealOrderId()
		);

		$paymentInst = $order->getPayment()->getMethodInstance();
		$_mail = $paymentInst->getConfigData('mail_authorized');

		if ($order->canInvoice()) {
			$invoice = $order->prepareInvoice();
			$invoice->register()->capture();
			Mage::getModel('core/resource_transaction')
				->addObject($invoice)
				->addObject($invoice->getOrder())
				->save();
		}
		$status = $paymentInst->getConfigData('payment_authorized');

		if ($status == '') {
			$status = $order->getStatus();
		}
		$order->setState($status, $status, '', $_mail);

		$this->saveAdditionalPaymentData($order);
		$order->sendNewOrderEmail();
	}

	/**
	 * Kega_Wizard_Model_Wizard::redeemCards()
	 * Redeem the purchased amount from the giftcard
	 *
	 * @param void
	 * @return void
	 */
	public function redeemCards()
	{
		$this->getQuote()->collectTotals();
		$customer = Mage::getSingleton('customer/session')->getCustomer();

		$totals = $this->getQuote()->getTotals();
		$totals = Mage::getSingleton('checkout/session')->getQuote()->getTotals();

		$grandTotal = $totals['grand_total']->getValue();

		if (isset($totals['giftcard'])) {
			if ($cardId = Mage::getSingleton('checkout/session')->getGiftcardCardId()) {
				$giftcard = Mage::getSingleton('intersolve/giftcard');

				$value = str_replace('-', '', $totals['giftcard']->getValue());
				$transId = $giftcard->purchase($cardId,  $value * 100);
				if ($transId) {
					Mage::getSingleton('checkout/session')->setGiftcardUseForPayment(false);
					Mage::getSingleton('checkout/session')->setGiftcardCardId(false);
				}
			}
		}
	}

	/**
	 * Kega_Wizard_Model_Wizard::cleanupOrderSession()
	 * Cleanup Order data in session.
	 */
	public function cleanupOrderSession()
	{
	    Mage::getSingleton('checkout/session')->clear();
		Mage::getSingleton('checkout/session')->setKialaPointData(false);
		Mage::getSingleton('checkout/session')->setGiftcardCardId(false);
		Mage::getSingleton('checkout/session')->setShipmentChoice(false);
		Mage::getSingleton('checkout/session')->setShipmentData(false);
		Mage::getSingleton('checkout/session')->setStorePickupData(false);

		$this->getQuote()->setIsActive(false);
		$this->getQuote()->delete();

		return true;
	}

	/**
	 * Kega_Wizard_Model_Wizard::getLastRealOrderFromSession()
	 * Get the last placed order from the session
	 * Only works before cleanupOrderSession() is called.
	 *
	 */
	public function getLastRealOrderFromSession()
	{
	    $order = $order = Mage::getModel('sales/order')->loadByIncrementId(
		    Mage::getSingleton('checkout/session')->getLastRealOrderId()
		);
		return $order;
	}

    /**
     * Involve new customer to system
     *
     * @return Kega_Wizard_Model_Wizard
     */
    protected function _involveNewCustomer()
    {
        $customer = $this->getQuote()->getCustomer();
        if ($customer->isConfirmationRequired()) {
            $customer->sendNewAccountEmail('confirmation');
            $url = Mage::helper('customer')->getEmailConfirmationUrl($customer->getEmail());
            $this->getSession()->addSuccess(
                Mage::helper('customer')->__('Account confirmation is required. Please, check your e-mail for confirmation link. To resend confirmation email please <a href="%s">click here</a>.', $url)
            );
        } else {
            $customer->sendNewAccountEmail();
        }
        return $this;
    }
}