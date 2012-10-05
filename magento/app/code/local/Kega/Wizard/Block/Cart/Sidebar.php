<?php

/**
 * @category   Kega
 * @package    Kega_Wizard
 */
class Kega_Wizard_Block_Cart_Sidebar extends Mage_Checkout_Block_Cart_Sidebar
{
    /**
     * Kega_Wizard_Block_Cart_Sidebar::getCheckoutUrl
     * Extended so we can route the checkout url to the wizard cart
     *
	 * @param void
     * @return string
     */
    public function getCheckoutUrl()
    {
        return Mage::getUrl('checkout/cart');
    }
}