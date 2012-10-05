<?php
abstract class Kega_Pdf_Model_Order_Pdf_Abstract extends Mage_Sales_Model_Order_Pdf_Abstract
{
	/**
	 * Placeholder for what type of PDF we are creating.
	 * var string
	 */
	protected $_pdfType;

	public function __construct()
	{
		$this->_pdfType = strtolower(substr(strrchr(get_class($this), '_'), 1));
	}

	/**
	 * Insert header image into the PDF.
	 * @see Mage_Sales_Model_Order_Pdf_Abstract::insertLogo($page, $store)
	 */
	protected function insertLogo(&$page, $store = null)
    {
		if (!Mage::getStoreConfigFlag('sales_pdf/' . $this->_pdfType . '/header_logo', $store)) {
			return;
		}

    	$image = Mage::getStoreConfig('sales/identity/logo', $store);
        if ($image) {
	        $image = Mage::getStoreConfig('system/filesystem/media', $store) . '/sales/store/logo/' . $image;
	        if (is_file($image)) {
				list($width, $height) = getimagesize($image);
				$image = Zend_Pdf_Image::imageWithPath($image);
				$x = 25; // Margin left
				$this->y -= 5; // Margin top
				$page->drawImage($image, $x, $this->y - $height, $x + $width, $this->y);

				// Adjust y, with the height of the image.
				$this->y -= $height;

				return;
	        }
        }
    }

	protected function insertAddress(&$page, $store = null)
    {
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
        $this->_setFontRegular($page, 5);

        $this->y = 25;
        foreach (explode("\n", Mage::getStoreConfig('sales/identity/address', $store)) as $value){
            if ($value!=='') {
                $page->drawText(trim(strip_tags($value)), 130, $this->y, 'UTF-8');
                $this->y -=7;
            }
        }
    }

    protected function _getPaymentText($payment)
    {
        $paymentInfo = Mage::helper('payment')->getInfoBlock($payment)
            ->setIsSecureMode(true)
            ->toPdf();

        $paymentInfo = trim(str_replace('{{pdf_row_separator}}', ' ', $paymentInfo));
        if ($payment->getCcType()) {
        	$paymentInfo .= ', ' . $payment->getCcType();
        }

        return $paymentInfo;
    }

    /**
     * Insert a header with default order info.
     *
     * @param Zend_Pdf_Page $page
     * @param Mage_Sales_Model_Order $order
     */
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
        $page->drawText(Mage::helper('kega_pdf')->__('Payment type'), 295, $this->y, 'UTF-8');
        if (!empty($additionalData['loyalty']['number'])) {
            $page->drawText(Mage::helper('kega_pdf')->__('Vipcard number'), 430, $this->y, 'UTF-8');
        }
        $this->y-=10;

        $this->_setFontBold($page);
        $page->drawText($order->getRealOrderId(), 25, $this->y, 'UTF-8');
        $this->_setFontRegular($page);
        $page->drawText(Mage::helper('core')->formatDate($order->getCreatedAtStoreDate(), 'medium', false), 160, $this->y, 'UTF-8');

        $page->drawText($this->_getPaymentText($payment), 295, $this->y, 'UTF-8');
        if (!empty($additionalData['loyalty']['number'])) {
            $page->drawText($additionalData['loyalty']['number'], 430, $this->y, 'UTF-8');
        }
		$this->y-=20;

		// Second row
		$minY = $this->y;

		$this->_setFontBold($page);

        $page->drawText(Mage::helper('kega_pdf')->__('Billing address'), 25, $this->y, 'UTF-8');
        $page->drawText(Mage::helper('kega_pdf')->__('Shipping address'), 160, $this->y, 'UTF-8');
        $page->drawText(Mage::helper('kega_pdf')->__('Shipping method'), 295, $this->y, 'UTF-8');
        $page->drawText(Mage::helper('kega_pdf')->__('Shipper address'), 430, $this->y, 'UTF-8');
        $this->y-=10;

        $this->_setFontRegular($page);

        /* Billing address */
        $address = $order->getBillingAddress();
        // Remove some not needed items.
        $address->setPrefix(null)
        		->setTelephone(null);
        $billingAddress = $this->_formatAddress($address->format('pdf'));
        $y = $this->y;
    	foreach ($billingAddress as $value){
            if ($value !== '') {
            	if ($y < $minY) {
            		$minY = $y;
            	}
                $page->drawText(strip_tags(trim($value)), 25, $y, 'UTF-8');
                $y-=10;
            }
        }

        /* Shipping address */
		$additionalData = ($payment->getAdditionalData() ? unserialize($payment->getAdditionalData()) : array());
		if (isset($additionalData['store_pickup'])) {
			$shippingAddress = array($additionalData['store_pickup']['name'],
									 $additionalData['store_pickup']['address'],
									 @$additionalData['store_pickup']['postcode'] . ' ' .  $additionalData['store_pickup']['city']
									);

		} else {
			$address = $order->getShippingAddress();
        	// Remove some not needed items.
        	$address->setPrefix(null)
        			->setTelephone(null);
			$shippingAddress = $this->_formatAddress($address->format('pdf'));
		}
    	$y = $this->y;
    	foreach ($shippingAddress as $value){
            if ($value !== '') {
            	if ($y < $minY) {
            		$minY = $y;
            	}
                $page->drawText(strip_tags(trim($value)), 160, $y, 'UTF-8');
                $y-=10;
            }
        }

        /* Shipping method */
        if (isset($additionalData['store_pickup'])) {
			$page->drawText(Mage::helper('kega_pdf')->__('Store Pickup') . ': ' . $additionalData['store_pickup']['name'], 295, $this->y, 'UTF-8');
        } else {
        	$page->drawText($order->getShippingDescription(), 295, $this->y, 'UTF-8');
        }

        /* Shipper address */
        $y = $this->y;
    	foreach (explode("\n", Mage::getStoreConfig('sales/identity/address', $order->getStore())) as $value){
    		if ($value !== '') {
    		    if ($y < $minY) {
            		$minY = $y;
            	}
                $page->drawText(strip_tags(trim($value)), 430, $y, 'UTF-8');
                $y-=10;
            }
        }

        $this->y = $minY - 20;
    }

	/**
     * Draw lines extention that also draws vertical and horizontal lines.
     *
     * column array format ext:
     * line-feed	int; x position (required if you want to draw a line)
     *
     * @see: Mage_Sales_Model_Order_Pdf_Abstract::drawLineBlocks
     *
     * @param Zend_Pdf_Page $page
     * @param array $draw
     * @param array $pageSettings
     * @throws Mage_Core_Exception
     * @return Zend_Pdf_Page
     */
    public function drawLineBlocks(Zend_Pdf_Page $page, array $draw, array $pageSettings = array())
    {
    	$page->setLineWidth(0.5);
		$page->setLineColor(new Zend_Pdf_Color_GrayScale(0.75));

    	foreach ($draw as &$itemsProp) {
			$height = (isset($itemsProp['height']) ? $itemsProp['height'] : 10);
	        $lineTopPadding = (isset($itemsProp['line-top-padding']) ? $itemsProp['line-top-padding'] : $height-9);
	        $fontSize = (isset($itemsProp['font_size']) ? $itemsProp['font_size'] : 7);
	        foreach ($itemsProp['lines'] as &$line) {
				$lastLeft = null;
				foreach ($line as &$column) {
					// Set font size per column, if not set yet.
					if (!isset($column['font_size'])) {
		        		$column['font_size'] = $fontSize;
		        	}
					if (!isset($column['line-feed'])) {
						continue;
					}
					$height = (!empty($column['height']) ? $column['height'] : $height);
					$lineTopPadding = (isset($itemsProp['line-top-padding']) ? $itemsProp['line-top-padding'] : $lineTopPadding);

					$page->drawLine($column['line-feed'], $this->y-$lineTopPadding+$height, $column['line-feed'], $this->y-$lineTopPadding);
					if ($lastLeft) {
						$page->drawLine($lastLeft, $this->y-$lineTopPadding, $column['line-feed'], $this->y-$lineTopPadding);
					}

					$lastLeft = $column['line-feed'];
				}
	        }
    	}

    	return parent::drawLineBlocks($page, $draw, $pageSettings);
    }

    /**
     * Create a grid, so you can see better if items are aligned.
     * Also usefull to count the 'steps' you want to move an item.
     *
     * @param Zend_Pdf_Page $page
     * @param int $gridSize
     */
	protected function _addGrid(&$page, $gridSize = 10) {
    	if ($gridSize < 2) {
    		$gridSize = 2;
    	}
    	$page->setLineWidth(0.5);
		$page->setLineColor(new Zend_Pdf_Color_GrayScale(0.5));
		for ($counter = $page->getHeight(); $counter > 0; $counter -= $gridSize) {
			$page->drawLine(0, $counter, $page->getWidth(), $counter);
		}

		for ($counter = 0; $counter <= $page->getWidth(); $counter += $gridSize) {
			$page->drawLine($counter, 0, $counter, $page->getHeight());
		}
    }


	protected function _setFontRegular($object, $size = 8)
    {
        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
        $object->setFont($font, $size);
        return $font;
    }

    protected function _setFontBold($object, $size = 8)
    {
        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
        $object->setFont($font, $size);
        return $font;
    }

    protected function _setFontItalic($object, $size = 8)
    {
        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD_ITALIC);
        $object->setFont($font, $size);
        return $font;
    }

	/**
     * Draw a (CODE128) barcode on the pdf
     *
     * @param Zend_Pdf_page $page
     * @param int $x
     * @param int $y
     * @param String $barcodeText
     */
    protected function _drawBarcode($page, $x, $y, $barcodeText)
    {
		Zend_Barcode::setBarcodeFont(dirname(__FILE__) . '/../../../fonts/arial.ttf');

		$barcodeOptions = array(
			'text' => $barcodeText,
			'factor' => 20, // Make barcode 20times bigger in memory.
			'drawText' => false,
			'barHeight' => 20,
		);

		$y = $page->getHeight() - $y;
		if ($y < 0) {
			die('offset should be >0 and is: ' . $y);
		}

		$rendererOptions = array(
			'leftOffset' => $x,
			'topOffset' => $y,
			'moduleSize' => .05, // Put barcode 20times smaller in the PDF so we have high res barcode.
		);

		$barcode = Zend_Barcode::factory(
			'Code128',
			'pdf',
			$barcodeOptions,
			$rendererOptions
		);

		$barcode->setResource($this->_getPdf())->draw();
    }

	/**
     * Create new page and assign to PDF object
     *
     * @param array $settings
     * @return Zend_Pdf_Page
     */
    public function newPage(array $settings = array())
    {
		$pageSize = !empty($settings['page_size']) ? $settings['page_size'] : Zend_Pdf_Page::SIZE_A4;
        $page = $this->_getPdf()->newPage($pageSize);
        $this->_getPdf()->pages[] = $page;

		// Start at top of the page.
        $this->y = $page->getHeight();

        if (!empty($settings['table_header'])) {
			/* Add margin (from config) */
			$this->y -= intval(Mage::getStoreConfig('sales_pdf/' . $this->_pdfType . '/top_margin', $settings['store']));

			/* Draw table header */
            $this->_drawTableHeader($page);
        }

        return $page;
    }
}
