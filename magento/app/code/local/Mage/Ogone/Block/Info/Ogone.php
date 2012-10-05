<?php
/**
 * Magento Ogone Payment extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Mage
 * @package    Mage_Ogone
 * @copyright  Copyright (c) 2008 ALTIC Charly Clairmont (CCH)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Ogone_Block_Info_Ogone extends Mage_Payment_Block_Info
//Mage_Payment_Block_Form
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('Ogone/info/Ogone.phtml');
    }

    public function getPaymentMethod(){
    	$list = Mage::getModel('ogone/source_paymentMethodsList')->getPMList();
	
	if(sizeof($list) <= 0){
		Mage::throwException("The list or payment methods is null");
	}

	$ccType = $this->getInfo()->getCcType();

	if(is_null($ccType)){
    		Mage::throwException("The card type can be null");
    	}

	for ($i = 0; $i < sizeof($list); $i++) {
		  $pm = $list[$i];
		  if($pm->getPmName() == $ccType){
			return $pm;
		  }
        }
	
       return false;

    }

    public function getPaymentTransactionId(){
	$order = Mage::getModel('sales/order');
	//TODO Still in development
	/*$pmId = $order->getPayment()->getOgonePayid();
	
	if(!empty($pmId)){
		return $pmId;
	}else{
		return false;
	}*/
	return false;
    }
}
?>
