<?php

/**
 * @category    Kega
 * @package     Kega_Wizard
 */
class Kega_Wizard_Model_Quote_Address_Total_Shipping extends Mage_Sales_Model_Quote_Address_Total_Shipping
{
    /**
     * Add shipping totals information to address object
     * Extended: display 'free shipping' as total text when amount is zero
     *
     * @param   Mage_Sales_Model_Quote_Address $address
     * @return  Mage_Sales_Model_Quote_Address_Total_Shipping
     */
    public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
        $amount = $address->getShippingAmount();
        if ($address->getShippingDescription()) {
            $title = Mage::helper('sales')->__('Shipping & Handling');
            if ($amount == 0) {
                $title = Mage::helper('sales')->__('Free shipping');
            }
            $address->addTotal(array(
                'code' => $this->getCode(),
                'title' => $title,
                'value' => $address->getShippingAmount()
            ));
        }
        return $this;
    }
}