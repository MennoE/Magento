<?php
/**
 * Sales Order Invoice PDF model
 *
 * @category   Mage
 * @package    Mage_Sales
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Kega_Pdf_Model_Order_Pdf_Creditmemo extends Kega_Pdf_Model_Order_Pdf_Abstract
{
	public function getPdf($creditmemos = array())
    {
        $this->_beforeGetPdf();
        $this->_initRenderer('creditmemo');

        $pdf = new Zend_Pdf();
        $this->_setPdf($pdf);
        $style = new Zend_Pdf_Style();
        $this->_setFontBold($style, 10);

        foreach ($creditmemos as $creditmemo) {
            if ($creditmemo->getStoreId()) {
                Mage::app()->getLocale()->emulate($creditmemo->getStoreId());
            }
            $page = $this->newPage();

            $order = $creditmemo->getOrder();

            /* Add logo */
            $this->insertLogo($page, $order->getStore());

            /* Add header */
            $this->insertHeader($page, $order);

            /* Add table head */
            $this->_drawTableHeader($page);

            /* Add body */
            foreach ($creditmemo->getAllItems() as $item){
                if ($item->getOrderItem()->getParentItem()) {
                    continue;
                }

                if ($this->y<50) {
                    $page = $this->newPage(array('table_header' => true, 'store' => $creditmemo->getStore()));
                }

                /* Draw item */
                $page = $this->_drawItem($item, $page, $order);
            }

            /* Add totals */
            $page = $this->insertTotals($page, $creditmemo);

            /* Add comments */
            $comments = $creditmemo->getCommentsCollection();
            if ($comments->count()) {
            	$this->y -= 10;
				$page->drawText(Mage::helper('kega_pdf')->__('Comments'), 30, $this->y, 'UTF-8');
				$this->y -= 2;

				$this->_setFontRegular($page, 8);
				foreach($comments as $comment) {
					$this->y -= 10;
					$message = Mage::helper('core')->formatDate($comment->getUpdatedAt(), 'medium', false) .
							   ' - ' . Mage::helper('core')->formatTime($comment->getUpdatedAt(), 'short', false) . ': ' .
							   str_replace('\n', PHP_EOL, $comment->getComment());

					$page->drawText($message, 30, $this->y, 'UTF-8');
				}
            }
        }

        $this->_afterGetPdf();

        if ($creditmemo->getStoreId()) {
            Mage::app()->getLocale()->revert();
        }
        return $pdf;
    }

    protected function _drawHeader(Zend_Pdf_Page $page)
    {
        $font = $page->getFont();
        $size = $page->getFontSize();

        $page->drawText(Mage::helper('sales')->__('Products'), $x = 35, $this->y, 'UTF-8');
        $x += 220;

        $page->drawText(Mage::helper('sales')->__('SKU'), $x, $this->y, 'UTF-8');
        $x += 100;

        $text = Mage::helper('sales')->__('Total(ex)');
        $page->drawText($text, $this->getAlignRight($text, $x, 50, $font, $size), $this->y, 'UTF-8');
        $x += 50;

        $text = Mage::helper('sales')->__('Discount');
        $page->drawText($text, $this->getAlignRight($text, $x, 50, $font, $size), $this->y, 'UTF-8');
        $x += 50;

        $text = Mage::helper('sales')->__('QTY');
        $page->drawText($text, $this->getAlignCenter($text, $x, 30, $font, $size), $this->y, 'UTF-8');
        $x += 30;

        $text = Mage::helper('sales')->__('Tax');
        $page->drawText($text, $this->getAlignRight($text, $x, 45, $font, $size, 10), $this->y, 'UTF-8');
        $x += 45;

        $text = Mage::helper('sales')->__('Total(inc)');
        $page->drawText($text, $this->getAlignRight($text, $x, 570 - $x, $font, $size), $this->y, 'UTF-8');
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

        $page->drawText(Mage::helper('kega_pdf')->__('qty'), 430, $this->y, 'UTF-8');
        $page->drawText(Mage::helper('kega_pdf')->__('Price'), 475, $this->y, 'UTF-8');
        $page->drawText(Mage::helper('kega_pdf')->__('Subtotal'), 520, $this->y, 'UTF-8');

		$page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
		$this->y -=14;
    }

    /**
     * Create new page and assign to PDF object
     *
     * @param array $settings
     * @return Zend_Pdf_Page
     */
    public function newPage(array $settings = array())
    {
        $page = parent::newPage($settings);

        if (!empty($settings['table_header'])) {
            $this->_setFontRegular($page);
            $page->setFillColor(new Zend_Pdf_Color_RGB(0.93, 0.92, 0.92));
            $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.5));
            $page->setLineWidth(0.5);
            $page->drawRectangle(25, $this->y, 570, $this->y-15);
            $this->y -=10;
            $page->setFillColor(new Zend_Pdf_Color_RGB(0.4, 0.4, 0.4));
            $this->_drawHeader($page);
            $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
            $this->y -=20;
        }

        return $page;
    }
}