<?php
/**
 * Kega_Wizard_Model_Order_Pdf_Instorepayment
 * Create pdf for instore payments
 * Extend: add barcode to be scanned at cash register
 *
 */
class Kega_Wizard_Model_Order_Pdf_Instorepayment extends Kega_Pdf_Model_Order_Pdf_Invoice
{
    public function getPdf($invoices = array(), $logo = false)
    {
        $this->_beforeGetPdf();
        $this->_initRenderer('invoice');

        $pdf = new Zend_Pdf();
        $this->_setPdf($pdf);
        $style = new Zend_Pdf_Style();

        foreach ($invoices as $invoice) {
            if ($invoice->getStoreId()) {
                Mage::app()->getLocale()->emulate($invoice->getStoreId());
            }
            $page = $pdf->newPage(Zend_Pdf_Page::SIZE_A4);
            $pdf->pages[] = $page;

            // Debug grid:
            //$this->_addGrid($page, 5);

            $order = $invoice->getOrder();

            /* Add image */
            if ($logo) {
            	$this->insertLogo($page, $invoice->getStore());
            } else {
            	/* Add spacing for logo on the paper */
            	$this->y = $page->getHeight() - 100;
            }

            /* Add head */
            $this->insertHeader($page, $order, 'Invoice');

            /* Add table head */
            $this->_drawTableHeader($page);

            /* Add body */
            foreach ($order->getAllItems() as $orderItem){

                $item = Mage::getModel('sales/order_invoice_item')->setOrderItem($orderItem);
                $item->addData($orderItem->toArray());
                $item->setData('qty', $orderItem->getQtyOrdered());

                if ($item->getOrderItem()->getParentItem()) {
                    continue;
                }

                if ($this->y < 150) {
                    $page = $this->newPage(array('table_header' => true));
                }

                /* Draw item */
                $page = $this->_drawItem($item, $page, $order);
            }

            $this->y -=10;

            /* Add totals */
            $page = $this->insertTotals($page, $invoice);

            if ($invoice->getStoreId()) {
                Mage::app()->getLocale()->revert();
            }

            /* Add instore payment shipping barcode section */
            $page->setLineColor(new Zend_Pdf_Color_GrayScale(0));
            $page->setLineWidth(0.5);
            $page->drawLine(20, 160, 570, 160);

            $barcode = Mage::helper('wizard')->getInstoreBarcode($invoice, 'A');
            $barcodeImage = Mage::helper('barcode_code128')->create($barcode, 45, 7);
            $this->_drawBarcodeImage($page, 120, 150, $barcodeImage);

            $this->_setFontBold($page);
            $page->drawText(Mage::helper('pdf')->__('Terms and Conditions'), 30, 15, 'UTF-8');
        }

        $this->_afterGetPdf();

        return $pdf;
    }
    protected function _drawBarcodeImage($page, $x, $y, $barcodeImage)
    {
        $tmpfname = tempnam("/tmp", "barcode") . '.png';
        imagepng($barcodeImage, $tmpfname);
        imagedestroy($barcodeImage);
        if (is_file($tmpfname)) {
            $image = Zend_Pdf_Image::imageWithPath($tmpfname);
            unlink($tmpfname);

            $page->drawImage($image, $x, $y - floor($image->getPixelHeight()/6), $x + floor($image->getPixelWidth()/6), $y);
        }
    }
    /**
     * Kega_Wizard_Model_Order_Pdf_Instorepayment::insertTotals($page, $source)
     * Extended: show totals from order. Not from invoice
     *
     */
    protected function insertTotals($page, $source){

        $order = $source->getOrder();
        $totals = $this->_getTotalsList($source);
        $lineBlock = array(
            'lines'  => array(),
            'height' => 15
        );

        $totals = $this->_getTotalsList($source);
        foreach ($totals as $total) {

            $amount = $total['amount'] ?
                Mage::helper('core')->formatPrice($total['amount'], false) :
                '';

            $lineBlock['lines'][] = array(
                array(
                    'text'      => $total['label'],
                    'feed'      => 475,
                    'align'     => 'right',
                    'font_size' => $total['size'],
                    'font'      => 'bold'
                ),
                array(
                    'text'      => $amount,
                    'feed'      => 565,
                    'align'     => 'right',
                    'font_size' => $total['size'],
                    'font'      => 'bold'
                ),
            );
        }

        $page = $this->drawLineBlocks($page, array($lineBlock));
        return $page;
    }

    /**
     *  Kega_Wizard_Model_Order_Pdf_Instorepayment::::_getTotalsList($source)
     *  Extended: generate array which can be used by insertTotals()
     *
     * @param Mage_Sales_Model_Invoice $source
     * @return Array $totals
     */
    protected function _getTotalsList($source)
    {
        $order = $source->getOrder();
        $totals = array(
            array(
            	'label' => Mage::helper('wizard')->__('Subtotal'),
                'amount' => $order->getSubtotal(),
                'size' => 7,
            ),
            array(
            	'label' => Mage::helper('wizard')->__('Shipping & Handling'),
                'amount' => $order->getShippingAmount(),
            	'size' => 7,
            ),
            array(
            	'label' => Mage::helper('wizard')->__('Grand Total'),
                'amount' => $order->getGrandTotal(),
                'size' => 8,
            )
        );
        $totals = array_merge($totals, $this->_getDownpaymentTotals($source));

        return $totals;
    }

    /**
     * Kega_Wizard_Model_Order_Pdf_Instorepayment::_getDownpaymentTotals()
     * Get total rows with downpayment information
     *
     * @param Mage_Sales_Model_Invoice $source
     * @return Array $totals
     */
    protected function _getDownpaymentTotals($source)
    {
        $totals = array();
        $order = $source->getOrder();
        if($source->getGrandTotal() == $order->getGrandTotal()) {
            return $totals;
        }

        $totals[] = array(
			'label' => '',
            'amount' => '',
            'size' => 7,
        );
        $totals[] = array(
			'label' => Mage::helper('wizard')->__('Down payment amount'),
            'amount' => $source->getGrandTotal(),
            'size' => 7,
        );
        $totals[] = array(
			'label' => Mage::helper('wizard')->__('Down payment remainder'),
            'amount' => ($order->getGrandTotal() - $source->getGrandTotal()),
            'size' => 7,
        );

        return $totals;
    }
}