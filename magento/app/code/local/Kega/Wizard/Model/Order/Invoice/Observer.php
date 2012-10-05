<?php
class Kega_Wizard_Model_Order_Invoice_Observer
{
	/**
	 * Observers: sales_order_invoice_register
	 *
	 * When a new invoice is registred and selected payment method is instore,
	 * we force the capture case to 'not_capture' (if none is given).
	 * This because otherwise it is set to status 'paid' automatically.
	 *
	 * @param Varien_Event_Observer $observer
	*/
	public function setCaptureCase($observer)
	{
		$invoice = $observer->getInvoice();
		if ($invoice->isObjectNew()
			&&
			!$invoice->setRequestedCaptureCase()
			&&
			$invoice->getOrder()->getPayment()->getCode() == 'instore'
			) {
			$invoice->setRequestedCaptureCase('not_capture');
		}
	}
}