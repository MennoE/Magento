<?php
/**
 * Sales Order Refund Pdf default items renderer
 *
 * @category   Mage
 * @package    Mage_Sales
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Kega_Pdf_Model_Order_Pdf_Items_Refund_Default extends Mage_Sales_Model_Order_Pdf_Items_Abstract
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

        // draw QTY field
        $lines[0][] = array(
            'text'  => '',
            'feed'  => 0,
        	'line-feed' => 375
        );

		// draw Return reason line
        $lines[0][] = array(
        	'text' => '',
        	'feed'  => 0,
            'line-feed'  => 435
        );

        // draw Exchange for size line
        $lines[0][] = array(
        	'text' => '',
        	'feed'  => 0,
            'line-feed'  => 485
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