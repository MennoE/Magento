<?php

class Kega_StorePickup_Model_Carrier_ShippingMethod
	extends Mage_Shipping_Model_Carrier_Abstract
	implements Mage_Shipping_Model_Carrier_Interface
{
	/**
	 * shipping method identifier
	 */
	protected $_code = 'storepickup';

	/**
	 * Collect rates for this shipping method based on information in $request
	 *
	 * @param Mage_Shipping_Model_Rate_Request $data
	 * @return Mage_Shipping_Model_Rate_Result
	 */
	public function collectRates(Mage_Shipping_Model_Rate_Request $request)
	{
		if (!$this->getConfigFlag('active')) {
			return false;
		}

		$result = Mage::getModel('shipping/rate_result');

		$price = 0;

		if($this->getConfigData('handling') >0)
		{
			$price = $this->getConfigData('handling');
		}

		if($this->getConfigData('handling_type') == 'P' && $request->getPackageValue() > 0)
		{
			$price = $request->getPackageValue()*$price;
		}

		$method = Mage::getModel('shipping/rate_result_method');

		$method->setCarrier($this->_code);

		$method->setCarrierTitle($this->getConfigData('title'));

		$method->setMethod('store');

		$method->setMethodTitle($this->getConfigData('methodtitle'));

		$method->setCost($price);

		$method->setPrice($price);

		$result->append($method);

		return $result;
	}


	/**
	 * Get allowed shipping methods
	 *
	 * @return array
	 */
	public function getAllowedMethods()
	{
		return array('storepickup'=>$this->getConfigData('methodtitle'));
	}
}