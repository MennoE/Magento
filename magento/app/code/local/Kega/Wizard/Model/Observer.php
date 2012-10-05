<?php

/**
 * Kega_Wizard_Model_Observer
 * Wizard Observer
 */
class Kega_Wizard_Model_Observer
{
    /**
     * Kega_Wizard_Model_Observer::checkInStoreCode()
     * Check if the instorecode is set, and if so set code to cookie
     *
     */
    public function saveStoreCode()
    {
        Mage::helper('wizard')->saveStoreCode();
        return $this;
    }

    /**
     * Kega_Wizard_Model_Observer::processCashRegisterFeedback()
     * Process feedback from Cash register
     */
    public function processCashRegisterFeedback()
    {
        Mage::getModel('wizard/instorefeedback')->processInstoreFeedback();
        return $this;
    }

    /**
     * If customer is logged in on checkout page,
     * redirect back to checkout.
     * 
     * @param void
     * @return void
     */
	public function checkoutRedirect()
	{
	    if (!isset($_SERVER['HTTP_REFERER'])) {
            Mage::app()->getFrontController()->getResponse()->setRedirect(Mage::app()->getStore()->getBaseUrl());
        }
		$returnUrl = $_SERVER['HTTP_REFERER'];

		$isCheckout = strpos($returnUrl, 'checkout/wizard');

		if ($isCheckout !== false) {
			Mage::app()->getFrontController()->getResponse()->setRedirect(Mage::getUrl('checkout/wizard'));
		}
	}
}