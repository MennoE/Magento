<?php
include("Kega/Pdf/controllers/DownloadController.php");

/**
 * Kega_Wizard_DownloadController
 * Handle downloads of wizard pdf's
 */
class Kega_Wizard_DownloadController extends Kega_Pdf_DownloadController
{
	/**
	 * Kega_Pdf_DownloadController::invoiceAction()
	 * Download instorepayment invoice pdf
	 */
    public function instoreinvoiceAction()
    {
        try {
        	$pdf = null;
        	$invoiceId = $this->getRequest()->getParam('id', null);

        	if ($invoiceId !== null) {

        		$invoiceId = Mage::helper('wizard')->decrypt($invoiceId);
        		$invoices = array(Mage::getModel('sales/order_invoice')->load($invoiceId));

        		$order = $invoices[0]->getOrder();

                if(!$order->getPayment()) {
                    die('no payment found for this order');
                }

        		$pdf = Mage::getModel('wizard/order_pdf_instorepayment')->getPdf($invoices);

        	} else {
    			die('Error, no id.');
    		}

    		if ($pdf) {
    			return $this->_prepareDownloadResponse('invoice'.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').'.pdf', $pdf->render(), 'application/pdf');
       		}
        } catch (Exception $e) {
            die('Cannon create pdf: ' . $e->getMessage());
        }
		die('Error, unknown.');
    }
}