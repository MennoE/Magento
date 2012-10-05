<?php

/**
 * Autoshipping controller
 *
 * @category   Kega
 * @package    Keg_Autoshipping
 */
class Kega_AutoShipping_Model_Observer
{
   /**
    * Kega_AutoShipping_Model_Observer::getQuote
	* Retrieve the current quote from the checkout session
	*
    * @param   void
    * @return  Mage_Sales_Model_Quote
    */
	public function getQuote()
    {
        if (empty($this->_quote)) {
            $this->_quote = Mage::getSingleton('checkout/session')->getQuote();
        }
        return $this->_quote;
    }

   /**
    * Kega_AutoShipping_Model_Observer::add_default_shipping
	* Creates a quote shipping address to directly display shippingcosts
	*
    * @param   Varien_Event_Observer $observer
    * @return  Kega_AutoShipping_Model_Observer
    */
    public function add_default_shipping($observer)
    {
		if (!Mage::getStoreConfig('autoshipping/settings/enabled')) {
			return $this;
		}

    	if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            $countryId = Mage::getSingleton('customer/session')->getCustomer()
            				->getDefaultShippingAddress()->getCountryId();
        } else {
            $countryId = Mage::getStoreConfig('autoshipping/settings/country_id');
        }

        // Retreive default method_code.
		$code = Mage::getStoreConfig('autoshipping/settings/method');

		if (Mage::getSingleton('checkout/session')->getKialaPointData()) {
			$code = 'kiala_kiala';
		}

		if (Mage::getSingleton('checkout/session')->getStorePickupData()) {
			$code = 'storepickup_store';
		}

    	try {
			if (!empty($code)) {
				$shipping = Mage::getSingleton('checkout/session')
					->getQuote()->getShippingAddress();

				$currentShippingMethod = $shipping->getShippingMethod();
				if (empty($currentShippingMethod)) {
					$shipping->setCountryId($countryId)
						->setShippingMethod($code)
						->setSameAsBilling(1)
						->setCollectShippingRates(true);

					$shipping->save();

					Mage::getSingleton('checkout/session')->getQuote()->save();
					Mage::getSingleton('checkout/session')->resetCheckout();
				}

			} else {
				throw new Mage_Core_Exception('Autoshipping activated but method is missing');
			}
		}
		catch (Mage_Core_Exception $e) {
			Mage::getSingleton('checkout/session')->addError($e->getMessage());
		}
		catch (Exception $e) {
			Mage::getSingleton('checkout/session')->addException($e);
		}

		return $this;
    }
}