<?php

/**
 * Customer (default) helper
 *
 * Various methods for customer data
 * 
 */
class Kega_Customer_Helper_Data extends Mage_Core_Helper_Abstract
{
    
    /**
     * Function to set the required form data in the template.
     */
    public function getCustomerFormData($object)
    {
    	$session = Mage::getSingleton('customer/session');
    	if ($session->getCustomerFormData() && !Mage::helper('customer')->isLoggedIn()) {
    		return new Varien_Object($session->getCustomerFormData());
    	}
        if ($object->getRegisterData() && !$object->getAddress()) {
            return $object->getRegisterData();
        }
        elseif($object->getAddress() && !$object->getRegisterData()) {
            return $object->getAddress();
        }
    }
}
    