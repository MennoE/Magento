<?php
/**
 * Sales Order Invoice PDF model
 *
 * @category   Mage
 * @package    Mage_Sales
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Kega_Pdf_Model_Order_Pdf_Invoice extends Kega_Pdf_Model_Order_Pdf_Abstract
{
	public function getPdf($invoices = array())
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
            $page = $this->newPage();

            // Debug grid:
            //$this->_addGrid($page, 5);

            $order = $invoice->getOrder();

            /* Add logo */
            $this->insertLogo($page, $order->getStore());

            /* Add header */
            $this->insertHeader($page, $order);

            /* Add table head */
            $this->_drawTableHeader($page);

            /* Add body */
            foreach ($invoice->getAllItems() as $item){
                if ($item->getOrderItem()->getParentItem()) {
                    continue;
                }
                if ($this->y < 100) {
                    $page = $this->newPage(array('table_header' => true, 'store' => $invoice->getStore()));
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

            $this->_setFontBold($page);
            $page->drawText(Mage::helper('kega_pdf')->__('footer_' . $this->_pdfType), 40, 15, 'UTF-8');
        }

        $this->_afterGetPdf();

        return $pdf;
    }

    protected function _drawTableHeader($page)
    {
    	$page->setFillColor(new Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
        $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineWidth(0.5);

        $page->drawRectangle(25, $this->y, 570, $this->y -15);
        $this->y -=10;

    	$this->_setFontRegular($page);
    	$page->setFillColor(new Zend_Pdf_Color_Rgb(0.4, 0.4, 0.4));

        $page->drawText(Mage::helper('kega_pdf')->__('SKU'), 30, $this->y, 'UTF-8');
        $page->drawText(Mage::helper('kega_pdf')->__('Product'), 90, $this->y, 'UTF-8');
        $page->drawText(Mage::helper('kega_pdf')->__('Color'), 240, $this->y, 'UTF-8');
        $page->drawText(Mage::helper('kega_pdf')->__('Maat'), 305, $this->y, 'UTF-8');
        $page->drawText(Mage::helper('kega_pdf')->__('qty'), 380, $this->y, 'UTF-8');
        $page->drawText(Mage::helper('kega_pdf')->__('Price'), 418, $this->y, 'UTF-8');
        $page->drawText(Mage::helper('kega_pdf')->__('Discount'), 470, $this->y, 'UTF-8');
        $page->drawText(Mage::helper('kega_pdf')->__('Subtotal'), 530, $this->y, 'UTF-8');

		$page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
		$this->y -=14;
    }
}