<?php
class Kega_Autoprint_Model_Observer
{
	/**
	 * Pdf type matching array.
	 * @var array
	 */
	protected $_adjustPdfTypes = array('packingslip' => 'shipment');

	/**
	 * Observers: adminhtml_controller_action_prepare_download_response
	 *
	 * If the shipment is new and is a kega_tnt_direct shipment we need to add a tracking number.
	 *
	 * @param Varien_Event_Observer $observer
	 */
	public function prepareDownloadResponse($observer)
	{
		$currentUser = Mage::getSingleton('admin/session')->getUser();
		if (!Mage::helper('kega_autoprint')->isActiveForUser($currentUser)) {
			return;
		}

		$pdfType = $this->_retreivePdfType($observer->getFileName());
		if (!$tray = Mage::getStoreConfig('sales_pdf/' . $pdfType . '/autoprint_tray')) {
			return;
		}

		// Place the PDF into subdir for current user.
		$adminId = 'user_' . Mage::getSingleton('admin/session')->getUser()->getId();

		Mage::helper('kega_autoprint/ftp')
			->uploadContent(Mage::getStoreConfig('sales_pdf/autoprint'),
							$adminId . DS . $tray,
							$observer->getFileName(),
							$observer->getContent());

		// Retreive the return to url (from session, refferer, user startup page).
		if ($returnToUrl = Mage::getSingleton('adminhtml/session')->getAutoprintReturnToUrl()) {
			Mage::getSingleton('adminhtml/session')->setAutoprintReturnToUrl(null);
		} else if (!empty($_SERVER["HTTP_REFERER"])) {
			$returnToUrl = $_SERVER["HTTP_REFERER"];
		} else {
			$route = Mage::getSingleton('admin/session')->getUser()->getStartupPageUrl();
			$returnToUrl = Mage::helper("adminhtml")->getUrl($route);
		}

		$body = Mage::helper('kega_autoprint')
					->getHtmlScript(Mage::getStoreConfig('sales_pdf/autoprint'),
													 $adminId . DS . $tray,
													 'Autoprint/' . $tray,
													 "location.href='{$returnToUrl}'");

		$observer->getAction()->getResponse()
			->setBody($body)
			->sendResponse(); // Send response now!

		exit; // Do not continue with processing.
	}

	/**
	 * Retreive the current PDF type.
	 * $fileName is build up as: packingslip2012-03-20_13-53-49.pdf
	 * retreive the first part 'packingslip' and convert it (in this case to 'shipment') if needed.
	 *
	 *
	 * @param string $fileName
	 * @return string
	 */
	private function _retreivePdfType($fileName)
	{
		if (strrchr($fileName, '.') != '.pdf' || !preg_match('/[a-z]+/i', $fileName, $result)) {
			// No PDF or no correct filename to determine type.
			return false;
		}

		$pdfType = current($result);
		if (isset($this->_adjustPdfTypes[$pdfType])) {
			// Type found in matching array, adjust PDF type.
			$pdfType = $this->_adjustPdfTypes[$pdfType];
		}

		return $pdfType;
	}
}