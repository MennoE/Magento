<?php
class Kega_Pdf_Model_Order_Pdf_Refund extends Kega_Pdf_Model_Order_Pdf_Abstract
{
    public function getPdf($shipments = array())
    {
    	$this->_beforeGetPdf();
        $this->_initRenderer('refund');

        $pdf = new Zend_Pdf();
        $this->_setPdf($pdf);
        $style = new Zend_Pdf_Style();

        foreach ($shipments as $shipment) {
            if ($shipment->getStoreId()) {
                Mage::app()->getLocale()->emulate($shipment->getStoreId());
            }
            $page = $this->newPage();

            // Debug grid:
            //$this->_addGrid($page, 5);

            $order = $shipment->getOrder();

			/* Add logo */
            $this->insertLogo($page, $order->getStore());

            /* Add header */
            $this->insertHeader($page, $order);

            /* Add refund steps */
            $this->drawRefundSteps($page);

        	/* Add table head */
            $this->_drawTableHeader($page);

            /* Add body */
            foreach ($shipment->getAllItems() as $item){
                if ($item->getOrderItem()->getParentItem()) {
                    continue;
                }

                if ($this->y<315) {
                    $page = $this->newPage(array('table_header' => true, 'store' => $shipment->getStore()));
                }

                /* Draw item */
                $page = $this->_drawItem($item, $page, $order);
            }

            $this->y -= 20;

            /* Refund reasons */
            $this->drawRefundReasons($page);

            /* Draw shipment tracking stickers */
            Mage::dispatchEvent('kega_pdf_refund_insert_shipment_track_stickers_before',
								array('model' => $this, 'page' => $page, 'shipment' => $shipment));

            foreach ($shipment->getAllTracks() as $track) {
				Mage::dispatchEvent('kega_pdf_refund_insert_shipment_track_sticker_' . $track->getCarrierCode(),
									array('model' => $this, 'page' => $page, 'track' => $track));
			}

			Mage::dispatchEvent('kega_pdf_refund_insert_shipment_track_stickers_after',
								array('model' => $this, 'page' => $page, 'shipment' => $shipment));

			/* Draw return sticker */
            $this->drawReturnSticker($page, $order);
        }

        $this->_afterGetPdf();

        return $pdf;
    }

	protected function insertHeader(&$page, $order)
    {
		/* Add margin (from config) */
		$this->y -= intval(Mage::getStoreConfig('sales_pdf/' . $this->_pdfType . '/top_margin', $order->getStoreId()));

		// Add barcode
        if (Mage::getStoreConfigFlag('sales_pdf/' . $this->_pdfType . '/put_order_id', $order->getStoreId())) {
			$this->_drawBarcode($page, 450, $this->y, $order->getRealOrderId());
        }

        $this->y -=10;
        $this->_setFontBold($page, 14);
        $page->drawText(Mage::helper('kega_pdf')->__('header_' . $this->_pdfType), 25, $this->y, 'UTF-8');
        $this->y -=10;
        $this->_setFontRegular($page, 6);
        $page->drawText(Mage::helper('kega_pdf')->__('header_' . $this->_pdfType . '_info'), 25, $this->y, 'UTF-8');

        // Add spacing between header text and order data.
		$this->y -=20;

		$payment = $order->getPayment();
		$additionalData = ($payment->getAdditionalData() ? unserialize($payment->getAdditionalData()) : array());

		$this->_setFontBold($page);
        $page->drawText(Mage::helper('kega_pdf')->__('Order number'), 25, $this->y, 'UTF-8');
        $page->drawText(Mage::helper('kega_pdf')->__('Order date'), 160, $this->y, 'UTF-8');
        $page->drawText(Mage::helper('kega_pdf')->__('Billing address'), 295, $this->y, 'UTF-8');
        if (!empty($additionalData['loyalty']['number'])) {
            $page->drawText(Mage::helper('kega_pdf')->__('Vipcard number'), 430, $this->y, 'UTF-8');
        }
        $this->y-=10;

        $this->_setFontRegular($page);
        $page->drawText($order->getRealOrderId(), 25, $this->y, 'UTF-8');
        $page->drawText(Mage::helper('core')->formatDate($order->getCreatedAtStoreDate(), 'medium', false), 160, $this->y, 'UTF-8');
        $address = $order->getBillingAddress();
        // Remove some not needed items.
        $address->setPrefix(null)
        		->setTelephone(null);
        if ($address->getCountryId() != 'nl') {
        	$address->setCountryId(null);
        }
        $billingAddress = $this->_formatAddress($address->format('pdf'));
        $topY = $this->y;
		$minY = $this->y;
        $y = $this->y;
    	foreach ($billingAddress as $value){
            if ($value !== '') {
            	if ($y < $minY) {
            		$minY = $y;
            	}
                $page->drawText(strip_tags(trim($value)), 295, $y, 'UTF-8');
                $y-=10;
            }
        }

        if (!empty($additionalData['loyalty']['number'])) {
            $page->drawText($additionalData['loyalty']['number'], 430, $topY, 'UTF-8');
        }

        $this->y = $minY - 15;
        $text = Mage::helper('kega_pdf')->__($this->_pdfType . '_intro');
	    foreach (explode("\n", $text) as $value){
			$page->drawText(strip_tags(trim($value)), 25, $this->y, 'UTF-8');
			$this->y-=10;
		}
		$this->y-=10;
    }

    protected function drawRefundSteps(&$page)
    {
        $topY = $this->y;
        $minY = $this->y;
        $positions = array(1 => 25,
        				   2 => 160,
        				   3 => 320,
        				   4 => 460
        				  );

    	for ($pos = 1; $pos <5; $pos++) {
    		$this->y = $topY;
			$text = Mage::helper('kega_pdf')->__($this->_pdfType . '_step_' . $pos);
    		if (!empty($text)) {
		    	$this->_setFontBold($page);
		        $page->drawText(chr(64 + $pos) . '.', $positions[$pos], $this->y, 'UTF-8');
		        $this->_setFontRegular($page);
		    	foreach (explode("\n", $text) as $value){
		            if ($value !== '') {
				        if ($this->y < $minY) {
		            		$minY = $this->y;
		            	}
		                $page->drawText(strip_tags(trim($value)), $positions[$pos] + 10, $this->y, 'UTF-8');
		                $this->y-=10;
		            }
		        }
    		}
		}

		$this->y = $minY-20;
    }

	protected function _drawTableHeader($page)
    {
    	$this->_setFontBold($page);
        $page->drawText(Mage::helper('kega_pdf')->__($this->_pdfType . '_table_header'), 25, $this->y, 'UTF-8');
		$this->y -=10;

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
        $page->drawText(Mage::helper('kega_pdf')->__('qty returned'), 380, $this->y, 'UTF-8');
        $page->drawText(Mage::helper('kega_pdf')->__('refund_reason'), 440, $this->y, 'UTF-8');
		$page->drawText(Mage::helper('kega_pdf')->__('Exchange for size'), 490, $this->y, 'UTF-8');

		$page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
		$this->y -=14;
    }

	protected function drawRefundReasons(&$page)
    {
    	$this->x = 25;
        $topY = $this->y;
        $minY = $this->y;
        $positions = array(1 => 25,
        				   2 => 160,
        				   3 => 320,
        				   4 => 460
        				  );

		$this->_setFontBold($page);
        $page->drawText(Mage::helper('kega_pdf')->__($this->_pdfType . '_reasons'), 25, $this->y, 'UTF-8');
		$this->y -=10;

		$text = Mage::helper('kega_pdf')->__($this->_pdfType . '_reasons_1');
   		if (!empty($text)) {
	        $this->_setFontRegular($page);
	    	foreach (explode("\n", $text) as $value){
	            if ($value !== '') {
			        if ($this->y < $minY) {
	            		$minY = $this->y;
	            	}
	                $page->drawText(strip_tags(trim($value)), $this->x, $this->y, 'UTF-8');
	                $this->y-=10;
	            }
	        }
   		}

   		$this->x += 145;
		$this->y = $topY;
		$text = Mage::helper('kega_pdf')->__($this->_pdfType . '_reasons_2');
   		if (!empty($text)) {
	        $this->_setFontRegular($page);
	    	foreach (explode("\n", $text) as $value){
	            if ($value !== '') {
			        if ($this->y < $minY) {
	            		$minY = $this->y;
	            	}
	                $page->drawText(strip_tags(trim($value)), $this->x, $this->y, 'UTF-8');
	                $this->y-=10;
	            }
	        }
   		}

        $this->x += 150;
        $maxY = $this->y;
        $this->y = $topY;
        $page->drawText(Mage::helper('kega_pdf')->__($this->_pdfType . '_reason_comment'), $this->x, $this->y, 'UTF-8');

        // Draw vertical lines.
        $page->setLineColor(new Zend_Pdf_Color_GrayScale(0));
        $page->setLineWidth(0.5);
        $page->drawLine($this->x+70, $this->y, $this->x+250, $this->y);
        $page->drawLine($this->x, $this->y-15, $this->x+250, $this->y-15);
        $page->drawLine($this->x, $this->y-30, $this->x+250, $this->y-30);
        $this->y = $maxY;
    }

    /**
     * Draw return sticker (Antwoordnummer sticker).
     *
     * @param Zend_Pdf_Page $page
     * @param Kega_StorePickup_Model_Order $order
     */
    protected function drawReturnSticker(&$page, $order)
    {
		if (!Mage::getStoreConfigFlag('sales_pdf/' . $this->_pdfType . '/return_sticker_active', $order->getStore())) {
			return;
		}

		$x = Mage::getStoreConfig('sales_pdf/' . $this->_pdfType . '/return_sticker_xpos', $order->getStore());
		$y = Mage::getStoreConfig('sales_pdf/' . $this->_pdfType . '/return_sticker_ypos', $order->getStore());

        $this->_setFontBold($page);
        $page->drawText(Mage::helper('kega_pdf')->__('Return sticker:'), $x, $y + 10, 'UTF-8');

        $this->_setFontRegular($page, 6);
        $page->drawText(Mage::helper('kega_pdf')->__('Return sticker info'), $x, $y + 3, 'UTF-8');

        // Draw address sticker
        $sticker = Mage::helper('kega_tntdirect_barcode_kix')
					->addressSticker(Mage::getStoreConfig('sales_pdf/' . $this->_pdfType . '/return_sticker_name', $order->getStore()),
									 Mage::getStoreConfig('sales_pdf/' . $this->_pdfType . '/return_sticker_address', $order->getStore()),
									 Mage::getStoreConfig('sales_pdf/' . $this->_pdfType . '/return_sticker_postcode', $order->getStore()),
									 Mage::getStoreConfig('sales_pdf/' . $this->_pdfType . '/return_sticker_city', $order->getStore()),
									 null,
									 6,
									 true,
									 Mage::getStoreConfig('sales_pdf/' . $this->_pdfType . '/return_sticker_border', $order->getStore())
									 );

		$rotate = Mage::getStoreConfigFlag('sales_pdf/' . $this->_pdfType . '/return_sticker_rotate', $order->getStore());
        if ($rotate) {
            $sticker = Mage::helper('kega_pdf/image')->rotateImage($sticker, 270);
        }

        $tmpfname = tempnam("/tmp", "sticker") . '.png';
        imagepng($sticker, $tmpfname);
        imagedestroy($sticker);
        if (is_file($tmpfname)) {
            $image = Zend_Pdf_Image::imageWithPath($tmpfname);
            unlink($tmpfname);
            if ($rotate) {
                $page->drawImage($image, $x, $y - 295, $x + 118, $y);
            } else {
                $page->drawImage($image, $x, $y - 118 , $x + 295, $y);
            }
        }
    }
}