<?php
/**
 * @category   Kega
 * @package    Kega_StorePickup
 *
 * @tutorial app/code/local/Kega/StorePickup/doc/usage.info
 *
 * Overrided to add store shipping address in emails instead of
 * the customer address if shipping method is store pickup
 *
 */
class Kega_StorePickup_Model_Order extends Mage_Sales_Model_Order
{
    /**
     * Sending email with order data
     *
     * @return Mage_Sales_Model_Order
     */
    public function sendNewOrderEmail()
    {
        Mage::helper('storepickup')->addStorePickupDataToShipping($this, $clone = false);
        return parent::sendNewOrderEmail();
    }
	
	/* Check if shipping method is store pickup, for use in transactional emails */
	public function getIsShippingMethodStorePickup()
	{
		return $this->getShippingMethod() == 'storepickup_store' ? true : false;
	}
}
