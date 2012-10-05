<?php
/**
 * @category   Kega
 * @package    Kega_TntDirect
 */
class Kega_TntDirect_Model_Order_Pdf_Refund_Observer
{
	/**
	 * Observers: kega_pdf_refund_insert_shipment_track_sticker_kega_tnt_direct
	 *
	 * When the Kega_Pdf module creates a refund PDF, it dispatch an event to insert the tracking sticker.
	 * We hook into this event and generate and insert a PostNL tracking sticker.
	 *
	 * @param Varien_Event_Observer $observer
	 */
	public function addTrackingSticker($observer)
	{
		$track	= $observer->getTrack();
		$order	= $observer->getTrack()->getShipment()->getOrder();
		$page 	= $observer->getPage();

		$stickerX = intval(Mage::getStoreConfig('sales_pdf/refund/kega_tnt_direct_sticker_xpos', $order->getStoreId()));
		$stickerY = intval(Mage::getStoreConfig('sales_pdf/refund/kega_tnt_direct_sticker_ypos', $order->getStoreId()));
		$leftPadding = intval(Mage::getStoreConfig('sales_pdf/refund/kega_tnt_direct_sticker_left_padding', $order->getStoreId()));

		// In case shipping method is storepickup, we show the telephone number as refference.
		$showTelephone = ($order->getShippingMethod() == 'storepickup_store');

		// Generate sticker.
		$sticker = Mage::helper('kega_tntdirect_sticker')
					->create($order,
							 $track->getNumber(),
							 4,
							 Mage::getStoreConfigFlag('sales_pdf/refund/kega_tnt_direct_sticker_border', $order->getStoreId()),
							 $leftPadding,
							 $showTelephone);

		// Save in temp file.
		$tmpfname = tempnam("/tmp", "sticker") . '.png';
		imagepng($sticker, $tmpfname);
		imagedestroy($sticker);
		if (is_file($tmpfname)) {
			// Insert into pdf (page).
			$image = Zend_Pdf_Image::imageWithPath($tmpfname);
			unlink($tmpfname);
			$page->drawImage($image, $stickerX, $stickerY - 295, $stickerX + 442, $stickerY);
		}
	}
}