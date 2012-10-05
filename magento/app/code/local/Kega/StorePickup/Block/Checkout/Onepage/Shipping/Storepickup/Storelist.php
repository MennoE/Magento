<?php
/**
 * Store List
 *
 * @category   Kega
 * @package    Kega_StorePickup
 * @author     Kega
 */
class Kega_StorePickup_Block_Checkout_Onepage_Shipping_Storepickup_Storelist extends Mage_Checkout_Block_Onepage_Abstract
{

	public function getStoreList()
	{
		return Mage::registry('pickup_store_list');
	}
}
