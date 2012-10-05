<?php
/**
 * Sales Order Creditmemo Pdf default items renderer
 *
 */
class Kega_Pdf_Model_Order_Pdf_Items_Creditmemo_Default extends Mage_Sales_Model_Order_Pdf_Items_Abstract //Mage_Sales_Model_Order_Pdf_Items_Invoice_Default //
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
        	'line-feed' => 25
        ));

        // draw Product name
        $lines[0][] = array(
            'text'  => $item->getName(),
            'feed'  => 90,
        	'line-feed' => 85
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

        // draw QTY
        $lines[0][] = array(
            'text'  => $item->getQty()*1,
            'feed'  => 430,
        	'line-feed' => 425
        );

        // draw Price
        $lines[0][] = array(
            'text'  => $order->formatPriceTxt($item->getPriceInclTax()),
            'feed'  => 510,
        	'line-feed' => 470,
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
            'height' => 14,
        	'font_size' => 9,
        );

        $page = $pdf->drawLineBlocks($page, array($lineBlock), array('table_header' => true));
        $this->setPage($page);
    }
}