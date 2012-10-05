<?php
/**
 * Sales Order Invoice Pdf default items renderer
 *
 * @category   Mage
 * @package    Mage_Sales
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Kega_Pdf_Model_Order_Pdf_Items_Invoice_Default extends Mage_Sales_Model_Order_Pdf_Items_Abstract //Mage_Sales_Model_Order_Pdf_Items_Invoice_Default //
{
	/**
     * Draw item line
     *
     */
    public function draw()
    {
        $order  = $this->getOrder();
        $item   = $this->getItem();
        $pdf    = $this->getPdf();
        $page   = $this->getPage();
        $sku    = $this->getSku($item);

        $color = null;
        $size = null;
    	$product = Mage::getModel('catalog/product');
	    if ($productId = $product->getIdBySku($sku)) {
	    	$product->load($productId);
		    if ($attribute = $product->getResource()->getAttribute('color')) {
				$color = @$attribute->getFrontend()->getValue($product);
			} else {
				$color = '';
			}
			// If simple product, then we already know the size.
	    	if ($attribute = $product->getResource()->getAttribute('size')) {
				$size = @$attribute->getFrontend()->getValue($product);
			} else {
				$size = '';
			}
	    }

        $lines  = array();

        // draw SKU
        $lines[0] = array(array(
            'text' => $sku,
            'feed' => 30,
        	'line-feed' => 25,
            'font_size' => 10,
     		'font'  => 'bold'
        ));

        // draw Product name
        $lines[0][] = array(
            'text'  => $item->getName(),
            'feed'  => 90,
        	'line-feed' => 85,
        	'font_size' => 10
        );

        // draw Color
        $lines[0][] = array(
            'text'  => $color,
            'feed'  => 240,
        	'line-feed' => 235
        );

        // draw Size
        $lines[0][] = array(
            'text'  => $size,
            'feed'  => 305,
        	'line-feed' => 300
        );

        ////

        // draw QTY
        $lines[0][] = array(
            'text'  => $item->getQty()*1,
            'feed'  => 380,
        	'line-feed' => 375
        );

        // draw Price
        $lines[0][] = array(
            'text'  => $order->formatPriceTxt($item->getPriceInclTax()),
            'feed'  => 460,
        	'line-feed' => 412,
            'font'  => 'bold',
            'align' => 'right',
        	'font_size' => 8,
        );

        // draw Discount
        $lines[0][] = array(
            'text'  => $order->formatPriceTxt($item->getRowDiscount()),
            'feed'  => 510,
        	'line-feed' => 465,
            'font'  => 'bold',
            'align' => 'right',
        	'font_size' => 8,
        );

        // draw Subtotal
        $lines[0][] = array(
            'text'  => $order->formatPriceTxt($item->getRowTotalInclTax()),
            'feed'  => 565,
        	'line-feed' => 515,
            'font'  => 'bold',
            'align' => 'right',
        	'font_size' => 8,
        );

        // draw line
        $lines[0][] = array(
        	'text' => '',
        	'feed'  => 0,
            'line-feed'  => 570
        );

        // Add lines and set line height.
        $lineBlock = array(
            'lines'  => $lines,
            'height' => 13,
        	'font_size' => 10,
        );

        $page = $pdf->drawLineBlocks($page, array($lineBlock), array('table_header' => true));
        $this->setPage($page);
    }
}