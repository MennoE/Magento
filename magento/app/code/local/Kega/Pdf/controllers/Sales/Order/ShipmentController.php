<?php
include("Mage/Adminhtml/controllers/Sales/Order/ShipmentController.php");
/**
 * Extended to add PDF refund download functionality in the same way as we serve the packslip.
 * @author mike.weerdenburg
 */
class Kega_Pdf_Sales_Order_ShipmentController extends Mage_Adminhtml_Sales_Order_ShipmentController
{
	/**
	 * Serve the refund PDF as downloadable PDF.
	 */
	public function printrefundAction()
    {
        /** @see Mage_Adminhtml_Sales_Order_InvoiceController */
        if ($shipmentId = $this->getRequest()->getParam('invoice_id')) { // invoice_id o_0
            if ($shipment = Mage::getModel('sales/order_shipment')->load($shipmentId)) {
                $pdf = Mage::getModel('sales/order_pdf_refund')->getPdf(array($shipment));
                $this->_prepareDownloadResponse('refund'.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').'.pdf', $pdf->render(), 'application/pdf');
            }
        }
        else {
            $this->_forward('noRoute');
        }
    }
}