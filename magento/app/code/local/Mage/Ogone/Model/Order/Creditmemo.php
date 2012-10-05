<?php
class Mage_Ogone_Model_Order_Creditmemo extends Mage_Sales_Model_Order_Creditmemo
{
	const STATE_REFUND_REQUESTED = 4;

	/**
	 * Retrieve Creditmemo states array
	 *
     * Extended: We want to add extra creditmemo status.
     *
     * @return array
     */
    public static function getStates()
    {
        if (is_null(self::$_states)) {
            // Retreive original states.
        	parent::getStates();

        	// Add extra state.
        	self::$_states[self::STATE_REFUND_REQUESTED] = Mage::helper('ogone')->__('Refund Requested');
        }
        return self::$_states;
    }
}