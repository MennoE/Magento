<?php

class Kega_Wizard_Block_Wizard extends Mage_Core_Block_Template
{
	/**
	 * Kega_Wizard_Block_Wizard::getCheckout
     * Retrieve checkout session
     *
	 * @param void
     * @return Mage_Checkout_Model_session
     */
    public function getCheckout()
    {
        if ($this->_checkout === null) {
            $this->_checkout = Mage::getSingleton('checkout/session');
        }
        return $this->_checkout;
    }

	/**
	 * Kega_Wizard_Block_Wizard::getSession
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
	 * Kega_Wizard_Block_Wizard::getQuote
     * Retrieve quote from checkout session
     *
	 * @param void
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        if ($this->_quote === null) {
            $this->_quote = $this->getCheckout()->getQuote();
        }
        return $this->_quote;
    }

	/**
	 * Kega_Wizard_Block_Wizard::getCustomer
     * Retrieve quote from checkout session
     *
	 * @param void
     * @return Mage_Customer_Model_Customer
     */
    public function getCustomer()
    {
        if ($this->_customer === null) {
            $this->_customer = Mage::getSingleton('customer/session')->getCustomer();
        }
        return $this->_customer;
    }

	/**
	 * Kega_Wizard_Block_Wizard::getRegisterData
     * Retrieve register form data from checkout session
	 * Use defaultBilling when no checkout session is available and user is logged in
     *
     * @return Varien_Object
     */
    public function getRegisterData()
    {
        $data = Mage::getSingleton('checkout/session')->getCustomerFormData();
        if (is_null($data)) {
			if ($customer = Mage::getSingleton('customer/session')->getCustomer()) {
				$data = Mage::getModel('customer/address')->load($customer->getDefaultBilling());
			}
			else {
				$data = new Varien_Object();
			}
        }
		else {
			$data = new Varien_Object($data);
		}
        return $data;
    }

	/**
	 * Kega_Wizard_Block_Wizard::getShippingData
     * - Retrieve shipping form data from checkout session
	 * - Use defaultShipping when no checkout session is available,
	 *   user is logged in and defaultBilling != defaultShipping
     *
	 * NOTE: needs to be merged w/ $this->getReigsterData()
     *
     * @return Varien_Object
     */
	public function getShippingData()
	{
		$data = Mage::getSingleton('checkout/session')->getShipmentData();
		$data = null;

        if (is_null($data)) {
			if ($customer = Mage::getSingleton('customer/session')->getCustomer()) {
				$data = $customer->getDefaultShipping() != $customer->getDefaultBilling()
					? Mage::getModel('customer/address')->load($customer->getDefaultShipping())
					: null
				;
				$data = new Varien_Object($data);
			}
			else {
				$data = new Varien_Object();
			}
        }
		else {
			$data = new Varien_Object($data);
		}
        return $data;
	}

	/**
	 * Kega_Wizard_Block_Wizard::getBillingAddress
     * Fetch current billing address from quote
     *
	 * @param void
     * @return Varien_Object
     */
	public function getBillingAddress()
	{
		return $this->getQuote()->getBillingAddress();
	}

	/**
	 * Kega_Wizard_Block_Wizard::getShippingAddress
     * Fetch current shipping address from quote
     *
	 * @param void
     * @return Varien_Object
     */
	public function getShippingAddress()
	{
		return $this->getQuote()->getShippingAddress();
	}

	/**
	 * Kega_Wizard_Block_Wizard::getShippingCosts
     * Retrieve the calculated total shippingcosts
     *
	 * @param $carrier string
     * @return Varien_Object
     */
	public function getShippingCosts($carrier = false)
	{
		if (!$carrier) {
			$carrier = Mage::getStoreConfig('autoshipping/settings/method');
			if ($this->getCheckout()->getKialaPointData()) {
				$carrier = 'kiala_kiala';
			}
			if ($this->getCheckout()->getStorePickupData()) {
				$carrier = 'storepickup_store';
			}
		}

		// Temporary fix: somehow tablerate doesn't know in time which country it needs to use
		// In the interest of time, or lack of it, this will suffice for now
		if ($carrier == 'tablerate_bestway') {
			$totals = $this->getQuote()->getTotals();
			return Mage::helper('checkout')->formatPrice($totals['shipping']->getValue());
		}
		else {
			$rate = Mage::getSingleton('checkout/session')->getQuote()->getShippingAddress()->getShippingRateByCode($carrier);
			$rate = $this->getCheckout()
				->getQuote()
				->getShippingAddress()
				->getShippingRateByCode($carrier);

			if (!$rate) {
				return false;
			}

			return Mage::helper('checkout')->formatPrice($rate['price']);
		}
	}

	/**
	 * Kega_Wizard_Block_Wizard::getNearestStores
     * Retrieve nearests stores
     *
	 * @param void
     * @return Varien_Object
     */
	public function getNearestStores()
	{
		$billing = $this->getBillingAddress();
		$block = $this->getLayout()->createBlock('store/store');

		$criteria = $this->getCheckout()->getPickupStoreCriteria()
			? $this->getCheckout()->getPickupStoreCriteria()
			: $billing->getPostcode()
		;

		$geodata = $this->getGeoLocation($criteria . ', The Netherlands');

		if (isset($geodata[0]) && $geodata[0] == '200') {
			$exclude = array(
				'district' => array(
					'type' => 'neq',
					'val' => 'Ecco'
				)
			);
			return $block->getStoresByGoogleCode($geodata[3], $geodata[2], 25, $exclude, true);
		}
		return array();
	}

	/**
	 * Kega_Store_IndexController->getGeoLocation()
	 * Returns geodata for given location string
	 *
     * @param $location string
	 * @return array
	 */
	public function getGeoLocation($criteria)
	{
		$url = sprintf(
			"http://maps.google.com/maps/geo?q=%s&output=csv&oe=utf8&sensor=true&key=%s",
			urlencode($criteria),
			'ABQIAAAAP8cM0a5GerqBsj_3zIk-NBTb_3-TxUy1LBXWwG4HHxwJVbRkYRTfvOiGUncAvg3Dtbxd_cKUoOMO_w'
		);
		;$content = @file_get_contents($url);
		return explode(",", $content);
	}

	/**
	 * Kega_Wizard_Block_Wizard::getCountryCollection
     * Retrieve country collection from configuration
     *
	 * @param void
     * @return Varien_Object
     */
	public function getCountryCollection()
    {
        if (!$this->_countryCollection) {
            $this->_countryCollection = Mage::getSingleton('directory/country')
				->getResourceCollection()
                ->loadByStore();
        }
        return $this->_countryCollection;
	}

	/**
	 * Kega_Wizard_Block_Wizard::getCountryOptions
     * Retrieve counties from collection or from cache
	 * Write to cache when no cache was available
     *
	 * @param void
     * @return Varien_Object
     */
	public function subscribedToNewsletter()
	{
		$newsletter = Mage::getModel('newsletter/subscriber');
		$newsletter->loadByCustomer($this->getCustomer());

		return $newsletter->isSubscribed();
	}

	/**
	 * Kega_Wizard_Block_Wizard::getCountryOptions
     * Retrieve counties from collection or from cache
	 * Write to cache when no cache was available
     *
	 * @param void
     * @return Varien_Object
     */
	public function getCountryOptions()
    {
        $options = false;
        $useCache = Mage::app()->useCache('config');

        if ($useCache) {
            $cacheId = 'DIRECTORY_COUNTRY_SELECT_STORE_' . Mage::app()->getStore()->getCode();
            $cacheTags = array('config');
            if ($optionsCache = Mage::app()->loadCache($cacheId)) {
                $options = unserialize($optionsCache);
            }
        }

        if ($options == false) {
            $options = $this->getCountryCollection()->toOptionArray();
            if ($useCache) {
                Mage::app()->saveCache(
					serialize($options),
					$cacheId,
					$cacheTags
				);
            }
        }
        return $options;
    }

	/**
	 * Kega_Wizard_Block_Wizard::getCountryHtmlSelect
     * Retrieve counties from collection or from cache
	 * Write to cache when no cache was available
     *
	 * @param void
     * @return Varien_Object
     */
	public function getCountryHtmlSelect($type, $extra = '', $countryId = NULL)
	{
        if (is_null($countryId)) {
            $countryId = Mage::getStoreConfig('general/country/default');
        }
        $select = $this->getLayout()->createBlock('core/html_select')
            ->setName($type.'[country_id]')
            ->setId($type.'-country_id')
            ->setTitle(Mage::helper('wizard')->__('Country'))
            ->setValue($countryId)
            ->setOptions($this->getCountryOptions());
		$select->setExtraParams($extra);

        return $select->getHtml();
    }

	/**
	 * Kega_Wizard_Block_Wizard::calculatePoints
     * Calculate the points the customer will receive for its order
	 * Grand points for the following structure:
	 * 25, 47.50, 72.50, 97.50, 122.50 ect.
     *
	 * @param $total float
     * @return integer
     */
	public function calculatePoints($total)
	{
		$points = 0;
		if ($total > 47.50) {
			$points = floor(($total - 47.50) / 25) + 2;
		}
		else {
			$points = $total == 47.50
				? 2 : ($total > 25.00 ? 1 : 0);
		}

		return $points;
	}

	/**
	 * Kega_Wizard_Block_Wizard::getLastRealOrderFromSession()
	 * Get last order from session (uses same method on wizard model)
	 */
	public function getLastRealOrderFromSession()
	{
	    return Mage::getModel('wizard/wizard')->getLastRealOrderFromSession();
	}

	/** 
	 * Get prefix options 
	 * 
	 * @param void 
	 * @return array 
	 */ 
	public function getPrefixOptions() 
	{ 
		$block = $this->getLayout()->createBlock('customer/widget_name'); 
		return $block->getPrefixOptions(); 
	}

	/**
     * Generate name block html
     * Copied from customer/address_edit block
     *
     * This method is needed to load the name block with
     * the correct object to show the values in the wizard.
     *
     * @return string
     */
    public function getNameBlockHtml($object)
    {
        $nameBlock = $this->getLayout()
            ->createBlock('customer/widget_name')
            ->setObject($object);

        return $nameBlock->toHtml();
    }

    /**
     * Fetch the current rates for the home delivery options
     * For this project the data from the TNT Direct carrier is being used.
     *
     * NOTE: for future development we should have a closer look at the way
     * we use carriers at the moment. For belgium shops we should use something
     * like Taxi Post as default.
     *
     * @param void
     * @return array
     */
    public function getCurrentDeliveryRates()
    {
        return Mage::getSingleton('checkout/session')
            ->getTntDirectActiveRate();
    }
}