<?php
/**
 * Mage_Payment_Model_Method_Free
 * Instore Payment method
 *
 */
class Kega_Wizard_Model_Method_Instore extends Mage_Payment_Model_Method_Abstract
{
    /**
     * Payment code name
     *
     * @var string
     */
    protected $_code = 'instore';

    /**
     * Availability options
     */
    //protected $_isGateway               = true;
    //protected $_canAuthorize            = true;
    protected $_canCapture              = true;
    protected $_canCapturePartial       = true;
    //protected $_canRefund               = true;
    //protected $_canVoid                 = false;
    //protected $_canUseInternal          = false;
    //protected $_canUseCheckout          = true;
    //protected $_canUseForMultishipping  = true;

}
