<?php
/**
 * PaymentMethod model
 */

class Mage_Ogone_Model_Source_IdealBanks
{

	public function toOptionArray(){

	$banksArrayOption = array(
	//array('value' => 'test', 'label' => 'test')
	);

	$list = Mage::getModel('ogone/source_paymentMethodsList')->getIdealBanks();

	for ($i = 0; $i < sizeof($list); $i++) {
		$pm = $list[$i];
		$banksArrayOption[] = array('value' => $pm->getPmValue(), 'label' => $pm->getPmName());
	}

	return $banksArrayOption;
	}
}

?>