<?php
class Kega_Autoprint_Model_Shipment_Observer
{
	/**
	 * Observers: controller_action_postdispatch_adminhtml_sales_order_shipment_save
	 *
	 * When a shipment is created and saved in the admin, we want to print the PDF's automaticly.
	 * So we upload the shipment PDF to the FTP server, output the ActiveX script
	 * and redirect to the print refund controller / action.
	 *
	 * @param Varien_Event_Observer $observer
	 */
	public function printPackslipAndRefundPDF($observer)
	{
		$currentUser = Mage::getSingleton('admin/session')->getUser();
		$shipment = Mage::registry('current_shipment');
		if (!Mage::helper('kega_autoprint')->isActiveForUser($currentUser) ||
			!$shipment ||
			!$shipment->getId())
		{
			return;
		}

		$response = $observer->getControllerAction()->getResponse();

		// Place the PDF into subdir for current user.
		$adminId = 'user_' . Mage::getSingleton('admin/session')->getUser()->getId();

		// Generate PDF for current shipment.
		$pdf = Mage::getModel('sales/order_pdf_shipment')->getPdf(array($shipment));

		$tray = Mage::getStoreConfig('sales_pdf/shipment/autoprint_tray');

		// Upload the PDF to the FTP server.
		Mage::helper('kega_autoprint/ftp')
			->uploadContent(Mage::getStoreConfig('sales_pdf/autoprint'),
							$adminId . DS . $tray,
							'packslip'.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').'.pdf',
							$pdf->render());

		if (!$returnToUrl = $this->_getLocation($response)) {
			$route = Mage::getSingleton('admin/session')->getUser()->getStartupPageUrl();
			$returnToUrl = Mage::helper("adminhtml")->getUrl($route);
		}

		// Save url in session, so that Kega_Autoprint_Model_Observer::prepareDownloadResponse can redirect the user to the correct page.
		Mage::getSingleton('adminhtml/session')->setAutoprintReturnToUrl($returnToUrl);

		// After downloading the shipping PDF we redirect the user to the print refund controller / action.
		$printRefundUrl = Mage::helper("adminhtml")->getUrl('*/*/printrefund', array('invoice_id' => $shipment->getId()));

		$body = Mage::helper('kega_autoprint')
					->getHtmlScript(Mage::getStoreConfig('sales_pdf/autoprint'),
													 $adminId . DS . $tray,
													 'Autoprint/' . $tray,
													 "location.href='{$printRefundUrl}'");

		$response->clearHeader('Location') // Remove redirect.
				 ->setHttpResponseCode(200) // Set normal response code.
				 ->setBody($body) // Output download script.
				 ->sendResponse(); // Send response now!

		exit; // Do not continue with processing.
	}

	/**
	 * Retreive Location redirect header value.
	 *
	 * @param Mage_Core_Controller_Response_Http $response
	 */
	protected function _getLocation($response)
	{
		foreach ($response->getHeaders() as $header) {
			if ($header['name'] != 'Location') {
				continue;
			}

			return $header['value'];
		}

		return false;
	}
}