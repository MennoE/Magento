<?php
/**
 * @category   Kega
 * @package    Kega_TntDirect
 */
class Kega_TntDirect_Model_Shipping_Observer
{
	/**
	 * Observers: sales_order_shipment_save_before
	 *
	 * If the shipment is new and is a kega_tnt_direct shipment we need to add a tracking number.
	 *
	 * @param Varien_Event_Observer $observer
	 */
	public function addTrack($observer)
	{
		$shipment = $observer->getShipment();
		$order = $shipment->getOrder();

		// If shipment is not new, or not a kega_tnt_direct shipment or already has tracking info.
		if ($shipment->getId() || !$this->_isTntShipping($order) || $shipment->getAllTracks()) {
			return;
		}

		/**
		 * We need the shipment increment id to create the barcode.
		 * This increment id is only available if shipment is saved.
		 * Do not use $shipment->save(), this will result in endless loop.
		 * Because it triggers 'sales_order_shipment_save_before',
		 * instead you need to save the shipment with use of the reaource model.
		 */
		$shipment->getResource()->save($shipment);

		/**
		 * Build up the barcode:
		 * Prefix: 3S
		 * + 4 pos partycode from config (@see: Documentation field record A030)
		 * + shipment increment id
		 */
		$barcode = '3S' .
				   Mage::getStoreConfig('carriers/kega_tnt_direct/a030') .
				   str_pad($shipment->getIncrementId(), 7, '0', STR_PAD_LEFT);

		// Add track to shipment.
		$track = Mage::getModel('sales/order_shipment_track')
			->setNumber($barcode)
			->setCarrierCode('kega_tnt_direct')
			->setTitle(Mage::getStoreConfig('carriers/kega_tnt_direct/title'));

		$shipment->addTrack($track);
	}

	/**
	 * Observers: adminhtml_block_html_before
	 *
	 * When creating a shipment use another template for tracking info if it is a kega_tnt_direct shipment.
	 *
	 * @param Varien_Event_Observer $observer
	 */
	public function setCreateTrackingHtml($observer)
	{
		$block = $observer->getEvent()->getBlock();

		if ($block->getTemplate() == 'sales/order/shipment/create/tracking.phtml') {
			// Only redirect template if it is a kega_tnt_direct shipment.
			if (!$this->_isTntShipping($block->getShipment()->getOrder())) {
				return;
			}

			// Use the kega template.
			$block->setTemplate('../../kega_tnt_direct/template/' . $block->getTemplate());
		}
	}

	/**
	 * Should we handle the given order as kega_tnt_direct (PostNL) shipping?
	 *
	 * @param Mage_Sales_Model_Order $order
	 */
	private function _isTntShipping(Mage_Sales_Model_Order $order)
	{
		// Retgreive all carriers we need to handle as kega_tnt_direct_shipping.
		$handleAsTntDirect = Mage::getStoreConfig('carriers/kega_tnt_direct/handle_as_tnt_direct', $order->getStore());

		// Make sure we always test for kega_tnt_direct.
		$handleAsTntDirect = 'kega_tnt_direct' . (empty($handleAsTntDirect) ? '' : ',' . $handleAsTntDirect);

		// Check if order shipping method matches one of the carrier codes.
		foreach (explode(',', $handleAsTntDirect) as $carrierCode) {
			if (substr($order->getShippingMethod(), 0, strlen($carrierCode) + 1) == $carrierCode . '_') {
				return true;
			}
		}

		return false;
	}

	/**
	 * Retrieve helper
	 *
	 * @return Kega_Bpost_Helper_Data
	 */
	protected function _getHelper()
	{
		return Mage::helper('kega_tnt_direct');
	}
}