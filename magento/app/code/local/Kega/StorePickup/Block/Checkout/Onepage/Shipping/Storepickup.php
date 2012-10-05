<?php
/**
 * Store List
 *
 * @category   Kega
 * @package    Kega_StorePickup
 * @author     Kega
 */
class Kega_StorePickup_Block_Checkout_Onepage_Shipping_Storepickup extends Mage_Checkout_Block_Onepage_Abstract
{

	protected $_address;

	public function getAddress()
	{
		if (empty($this->_address)) {
			$this->_address = $this->getQuote()->getShippingAddress();
		}
		return $this->_address;
	}
}