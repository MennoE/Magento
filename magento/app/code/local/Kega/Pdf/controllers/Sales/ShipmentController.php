<?php
include("Mage/Adminhtml/controllers/Sales/ShipmentController.php");
/**
 * Extended to add mass-action PDF refund download functionality in the same way as we serve the packslip.
 * @author mike.weerdenburg
 */
class Kega_Pdf_Sales_ShipmentController extends Mage_Adminhtml_Sales_ShipmentController
{
	/**
	 * Serve the mass-action refund PDF as downloadable PDF.
	 */
	public function pdfrefundsAction()
	{
        $shipmentIds = $this->getRequest()->getPost('shipment_ids');
        if (!empty($shipmentIds)) {
            $shipments = Mage::getResourceModel('sales/order_shipment_collection')
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('entity_id', array('in' => $shipmentIds))
                ->load();

            $pdf = Mage::getModel('sales/order_pdf_refund')->getPdf($shipments);

            return $this->_prepareDownloadResponse('refund'.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').'.pdf', $pdf->render(), 'application/pdf');
        }
        $this->_redirect('*/*/');
    }
}