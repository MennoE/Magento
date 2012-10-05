<?php

/**
 * Kega_Wizard_Model_Instorewizard
 * Extend of Wizard model for instore specific actions
 */
class Kega_Wizard_Model_Instorewizard extends Kega_Wizard_Model_Wizard
{
	/**
     * Kega_Wizard_Model_Instorewizard::_createDownpaymentInvoice()
     * Create invoice for 0 products and set all amounts to $amount
     *
     * @param Mage_Sales_Model_Order $order
     * @param Double $amount
     * @return Mage_Sales_Model_Order_Invoice
     */
    public function createDownpaymentInvoice($order, $amount)
    {
        $qtys = array();
        foreach($order->getAllItems() as $orderItem) {
            $qtys[$orderItem->getId()] = 0;
        }

		$invoice = $order->prepareInvoice();
        $invoice->setSubtotal($amount);
        $invoice->setBaseSubtotal($amount);
        $invoice->setGrandTotal($amount);
        $invoice->setBaseGrandTotal($amount);
        $invoice->setShippingAmount('0');
        $invoice->setBaseShippingAmount('0');
        $invoice->addComment(Mage::helper('wizard')->__('Down payment instore'));

        try {
            $invoice->register();//->capture();
        } catch(Exception $e) {
            die("failed: " . $e);
        }

        return $invoice;
    }

    /**
     * Create invoice for remaining products & amount.
     *
     * @param Mage_Sales_Model_Order $order
     * @return Mage_Sales_Model_Order_Invoice
     */
    public function createFinalpaymentInvoice($order)
    {
        $invoice = $order->prepareInvoice();
        $invoice->addComment(Mage::helper('wizard')->__('Remaining payment instore'));

        try {
            $invoice->register();//->capture();
        } catch(Exception $e) {
            die("failed: " . $e);
        }

        return $invoice;
    }
}