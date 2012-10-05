<?php
class Kega_TntDirect_Model_Carrier_ShippingMethod extends Mage_Shipping_Model_Carrier_Abstract
{
    /**
	* unique internal shipping method identifier
	*
	* @var string [a-z0-9_]
	*/
	protected $_code = 'kega_tnt_direct';

	/**
	 * Collect rates for this shipping method based on information in $request
	 *
	 * @param Mage_Shipping_Model_Rate_Request $data
	 * @return Mage_Shipping_Model_Rate_Result
	 */
	public function collectRates(Mage_Shipping_Model_Rate_Request $request)
	{
        // skip if not enabled
		if (!$this->getConfigData('active')) {
			return false;
		}

		// this object will be returned as result of this method
		// containing all the shipping rates of this method
		$result = Mage::getModel('shipping/rate_result');

        $packageValue = $request->getPackageValueWithDiscount();
        $freeShipping = ($this->getConfigData('free_shipping_enable') &&
                         $packageValue >= $this->getConfigData('free_shipping_subtotal'));

        foreach ($this->getAllowedMethods() as $mCode=>$mTitle) {
            // create new instance of method rate
            $method = Mage::getModel('shipping/rate_result_method');

            // record carrier information
            $method->setCarrier($this->_code);
            $method->setCarrierTitle($this->getConfigData('title'));

            // record method information
            // TODO: retreive from config what method code and name we need to use?
            $method->setMethod($mCode);
            $method->setMethodTitle($mTitle);

            if ($freeShipping || $request->getFreeShipping()) {
                $method->setCost('0.00');
                $method->setPrice('0.00');
            } else {
                // rate cost is optional property to record how much it costs to vendor to ship
                $method->setCost($this->getConfigData('price'));

                // record price for this shipping method
                $method->setPrice($this->getConfigData('price'));
            }

			// save current costs into session for displaying on 'custom' places in the layout
			// this way we don't have to recollect the rates each time.
			Mage::getSingleton('checkout/session')
				->setTntDirectActiveRate($method->getCost());

			// add this rate to the result
            $result->append($method);
        }

		return $result;
	}

    /**
     * Get allowed shipping methods
     *
     * @return array
     */
    public function getAllowedMethods()
    {
        return array('03085' => Mage::helper('shipping')->__('AVG'),
                     '03086' => Mage::helper('shipping')->__('AVG + Rembours'),
                     '03087' => Mage::helper('shipping')->__('AVG + Verhoogd Aansprakelijk'),
                     '03089' => Mage::helper('shipping')->__('AVG + Handtekening voor Ontvangst'),
                     '03090' => Mage::helper('shipping')->__('AVG + Buren Belevering + Retour bij Geen Gehoor'),
                     '03091' => Mage::helper('shipping')->__('AVG + Rembours + Verhoogd Aansprakelijk'),
                     '03093' => Mage::helper('shipping')->__('AVG + Rembours + Retour bij Geen Gehoor'),
                     '03094' => Mage::helper('shipping')->__('AVG + Verhoogd Aansprakelijk + Retour bij Geen Gehoor'),
                     '03096' => Mage::helper('shipping')->__('AVG + Handtekening Voor Ontvangst + Retour bij Geen Gehoor'),
                     '03097' => Mage::helper('shipping')->__('AVG + Rembours + Verhoogd Aansprakelijk + Retour bij Geen Gehoor'),
                     '03385' => Mage::helper('shipping')->__('AVG + Alleen Huisadres'),
                     '03390' => Mage::helper('shipping')->__('AVG + Alleen Huisadres + Retour bij Geen Gehoor'),
                     '04940' => Mage::helper('shipping')->__('EPS'),
        			 '04944' => Mage::helper('shipping')->__('EPS-C')
                    );
    }

    public function isTrackingAvailable()
    {
        return true;
    }

    /**
     * Retreive last created matching tracking info and
     * add a track/trace link, so we can link to more specified info.
     *
     * @param string $trackingNumber
     */
	public function getTrackingInfo($trackingNumber)
	{
		if ($trackingNumber == 'envelope') {
			return false;
		}

		$tracks = Mage::getModel('sales/order_shipment_track')
					->getCollection()
					->addAttributeToSelect('*')
					->addFieldToFilter('carrier_code', array('eq' => $this->_code))
					->addFieldToFilter('number', array('eq' => $trackingNumber))
					->addAttributeToSort('entity_id', 'DESC')
					;

		if (!$track = $tracks->fetchItem()) {
			return false;
		}

		// Retreive postcode from shipment, so we can build up the link to PostNL.
		$postcode = $track->getShipment()->getShippingAddress()->getPostcode();
		$link = 'https://mijnpakket.postnl.nl/Claim?barcode=' . $trackingNumber .
				'&postalcode=' . str_replace(' ', '', $postcode) . '&Foreign=false';

		return array('title' => $track->getTitle(),
					 'description' => $track->getDescription(),
					 'number' => $track->getNumber(),
					 'link' => $link,
					 );
	}
}
