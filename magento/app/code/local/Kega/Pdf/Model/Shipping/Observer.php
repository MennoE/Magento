<?php
/**
 * @category   Kega
 * @package    Kega_Pdf
 */
class Kega_Pdf_Model_Shipping_Observer
{
	/**
	 * Observers: adminhtml_widget_container_html_before_sales_order_shipment_view
	 *
	 * Because we also have a refund PDF, we need to add a button for printing this PDF on the shipment view page.
	 *
	 * @param Varien_Event_Observer $observer
	 */
	public function addJustPrintButtons($observer)
	{
		$block = $observer->getBlock();

		if ($block->getShipment()->getId()) {
			// Adjust label of original print button.
            $block->updateButton('print', 'label', Mage::helper('sales')->__('Print packslip'));

            // Add extra button to print refund PDF.
            $block->addButton('printRefund', array(
                'label'     => Mage::helper('sales')->__('Print refund'),
                'class'     => 'save',
                'onclick'   => 'setLocation(\'' . $block->getUrl('*/*/printrefund', array('invoice_id' => $block->getShipment()->getId())) . '\')'
                )
            );
        }

    	return $this;
	}

	/**
	 * Observers: adminhtml_grid_prepare_massaction_block_after_sales_shipment_grid
	 * Because we also have a refund PDF, we add a mass action for printing this PDF.
	 *
	 * @param Varien_Event_Observer $observer
	*/
	public function addMassPrintRefundAction($observer)
	{
		$block = $observer->getEvent()->getBlock();
		$block->getMassactionBlock()->addItem('pdfrefunds_order', array(
			'label'=> Mage::helper('sales')->__('PDF Refunds'),
			'url'  => $block->getUrl('*/sales_shipment/pdfrefunds'),
			));

		return $this;
	}
}