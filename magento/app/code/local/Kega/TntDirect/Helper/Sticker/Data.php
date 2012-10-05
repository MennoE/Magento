<?php
class Kega_TntDirect_Helper_Sticker_Data extends Mage_Core_Helper_Abstract
{
    protected $_methods = array('03086' => "Rembours",
                                '03087' => "Verhoogd Aansprakelijk",
                                '03089' => "Handtekening ontvangst",
                                '03090' => "Na bestelpoging(en)\nRetour afzender",
                                '03091' => "Verhoogd Aansprakelijk\nRembours",
                                '03093' => "Na bestelpoging(en)\nRetour afzender\nRembours",
                                '03094' => "Na bestelpoging(en)\nRetour afzender\nVerhoogd Aansprakelijk",
                                '03096' => "Na bestelpoging(en)\nRetour afzender\nHandtekening voor ontvangst",
                                '03097' => "Na bestelpoging(en)\nRetour afzender\nVerhoogd Aansprakelijk\nRembours",
                                '03385' => "Alleen Huisadres",
                                '03390' => "Alleen Huisadres\nNa bestelpoging(en)\nRetour afzender",
    							'04940' => "EU",
    							'04944' => "EU-C",
                                );

    /**
     * Create GD image with the PostNL sticker layout.
     *
     * @param Mage_Sales_Model_Order $order
     * @param String	$barcode	    The complete 3S barcode, or a numeric identifier.
     *                                  If barcodeSuffix = envelope (or KIX), we generate a non-package (letter) sticker.
     *                                  If barcodeSuffix = INTERNAL, we generate a internal package sticker.
     *
     * @param int		$scale          2 = 144 dpi | 3 = 216 dpi | 4 = 288 dpi | etc.
     * @param boolean	$border			Show debug border arround the sticker image.
     * @param int		$leftMargin		You can use this to make extra space for extra 'instore' barcode.
     * @param boolean	$showTelephone	Show telephone nubmer in the addres block (used for store pickup).
     *
     * @return Object $im               GD image resource
     */
    public function create($order, $barcode = null, $scale = 2, $border = false, $leftMargin = 0, $showTelephone = false)
    {
        if (!($order instanceof Mage_Sales_Model_Order) || !$order->getId()) {
            throw new Kega_TntDirect_Exception('No valid order provided, not possible to generate barcode sticker.');
        }

        // Make sure we have a minimum of 144 dpi.
        if ($scale <2) {
            $scale = 2;
        }

        // Make sure left margin is within the allowed range of 0 - 125.
        if (($leftMargin / $scale) > 100) {
			$leftMargin = 100;
        } else if ($leftMargin < 0) {
			$leftMargin = 0;
        }

	    $address = $order->getShippingAddress();

	    // Handle 'envelope' as 'KIX' shipments.
	    if ($barcode == 'envelope') {
			$barcode = 'KIX';
	    }

        if ($barcode == 'KIX') {
            $prisma = 'KIX';
        } else if ($barcode == 'INTERNAL') {
            $prisma = 'INTERNAL';
        } else if ($address->getCountryId() != 'NL') {
        	$prisma = '04944';
        } else if (substr($order->getShippingMethod(), 0, 10) == 'kega_tnt_direct') {
            $prisma = substr($order->getShippingMethod(), 11);
        } else {
            $prisma = '03085';
        }

        // Create image of 15 x 10 (cm).
        $im = imagecreate(442 * $scale, 295 * $scale);

        // Define colors.
        $white = imagecolorallocate($im, 255, 255, 255);
        $black = imagecolorallocate($im, 0, 0, 0);

        $arial = Mage::helper('kega_tntdirect_barcode')->getFont('arial');
        $arialbd = Mage::helper('kega_tntdirect_barcode')->getFont('arialbd');

        // Fill background of image with white.
        imagefilledrectangle($im, 0, 0, imagesx($im), imagesy($im), $white);

        // Ordernummer.
        imagettftext($im, (6 * $scale), 0, ($leftMargin + 10 * $scale), (17 * $scale), $black, $arial, $order->getIncrementId());

        // Referentietekst.
        $text =  Mage::getStoreConfig('carriers/kega_tnt_direct/reference', $order->getStoreId());

        imagettftext($im, (8 * $scale), 0, ($leftMargin + 80 * $scale), (17 * $scale), $black, $arial, $text);

        // Afzender.
        $text = Mage::helper('kega_tntdirect')->__('Sender') . ':' . PHP_EOL
        	  . Mage::getStoreConfig('carriers/kega_tnt_direct/a130', $order->getStoreId()) . PHP_EOL
              . Mage::getStoreConfig('carriers/kega_tnt_direct/a139', $order->getStoreId()) . ' '
              . Mage::getStoreConfig('carriers/kega_tnt_direct/a140', $order->getStoreId())
              . Mage::getStoreConfig('carriers/kega_tnt_direct/a141', $order->getStoreId()) . PHP_EOL
              . Mage::getStoreConfig('carriers/kega_tnt_direct/a150', $order->getStoreId())  . ' '
              . Mage::getStoreConfig('carriers/kega_tnt_direct/a151', $order->getStoreId()) . PHP_EOL
              . ($prisma == '04940' || $prisma == '04944' ? 'THE NETHERLANDS' : '');
        imagettftext($im, (8 * $scale), 0, ($leftMargin + 10 * $scale), (40 * $scale), $black, $arial, $text);


        // Eur shipment.
        if ($prisma == '04940') {
            imagettftext($im, (36 * $scale), 0, ($leftMargin + 7 * $scale), (185 * $scale), $black, $arialbd, 'EU');
        } else if ($prisma == '04944') {
            imagettftext($im, (36 * $scale), 0, ($leftMargin + 7 * $scale), (185 * $scale), $black, $arialbd, 'EU-C');
        } else if ($prisma != '03085' && $prisma != 'KIX' && $prisma != 'INTERNAL') {
            // Aanvullende Diensten.
            imagettftext($im, (36 * $scale), 0, ($leftMargin + 10 * $scale), (130 * $scale), $black, $arialbd, 'AD');
            $text = (isset($this->_methods[$prisma]) ? $this->_methods[$prisma] : $prisma);
            // Todo: Rembours price not implemented, yet...
            /*
            if (stripos($prisma, 'rembours')) {
                $text = 'EUR [PRICE,00]';
            }
            */
            imagettftext($im, (9 * $scale), 0, ($leftMargin + 10 * $scale), (144 * $scale), $black, $arial, $text);
        }

        // Frankeerkader.
        imagerectangle($im, (359 * $scale), (10 * $scale), (430 * $scale), (30 * $scale), $black);
        imagettftext($im, (10 * $scale), 0, (366 * $scale), (25 * $scale), $black, $arialbd, 'FRANCO');

        // NAW kader.
        if ($prisma == 'INTERNAL') {
            imagettftext($im, (10 * $scale), 0, (200 * $scale), (70 * $scale), $black, $arial, 'INTERN TRANSPORT');
        } else {
	        imagettftext($im, (10 * $scale), 0, (190 * $scale), (70 * $scale), $black, $arial, 'PostNL');
	        if ($prisma == '04940' || $prisma == '04944') {
	            imagettftext($im, (10 * $scale), 0, (370 * $scale), (70 * $scale), $black, $arial, 'AVG EPS');
	        } else if ($prisma != 'KIX') {
	            imagettftext($im, (10 * $scale), 0, (400 * $scale), (70 * $scale), $black, $arial, 'AVG');
	        }
        }

        imagerectangle($im, (190 * $scale), (75 * $scale), (430 * $scale), (($barcode == 'KIX' ? 230 : 185) * $scale), $black);

        $text = $address->getName() . PHP_EOL .
				$address->getCompany() . PHP_EOL .
				implode(' ', $address->getStreet()) . PHP_EOL;

		if ($showTelephone) {
			$text .= 'T: ' . $address->getTelephone() . PHP_EOL;
		}

        imagettftext($im, (10 * $scale), 0, (195 * $scale), (95 * $scale), $black, $arial, trim($text));

        imagettftext($im, (12 * $scale), 0, (195 * $scale), (162 * $scale), $black, $arialbd, str_replace(' ', '', $address->getPostcode()));
        imagettftext($im, (10 * $scale), 0, (260 * $scale), (162 * $scale), $black, $arialbd, strtoupper($address->getCity()));

        if ($address->getCountry() != 'NL') {
        	// Load en_US locale, so we can retreive English translation for the country name.
			$localeEnUs = new Zend_Locale('en_US');
			$countryName = $localeEnUs->getTranslation($address->getCountryId(), 'country', $localeEnUs);
			imagettftext($im, (10 * $scale), 0, (195 * $scale), (178 * $scale), $black, $arialbd, $countryName);
        }

        if ($barcode == 'KIX') {
            // Add Kix barcode
            $barcodeImage = Mage::helper('kega_tntdirect_barcode_kix')->create($address->getStreet(-1), $address->getPostcode(), $scale);
            imagecopy($im, $barcodeImage, (195 * $scale), (200 * $scale), 0 , 0 , imagesx($barcodeImage), imagesy($barcodeImage));
            imagedestroy($barcodeImage);
        } else if ($barcode != 'INTERNAL') {
            // Add Collo and 39 barcode.
            imagettftext($im, (8 * $scale), 0, ($leftMargin + 333 * $scale), (197 * $scale), $black, $arial, '1 Collo');

            if (substr($barcode, 0, 2) != '3S') {
				// If given barcode is not an 3S code, add 3S and PostNL customer id as prefix.
	            $barcode = '3S' . Mage::getStoreConfig('carriers/kega_tnt_direct/a030', $order->getStoreId()) . $barcode;
            }
            $barcodeImage = Mage::helper('kega_tntdirect_barcode_code39')->create($barcode, 52, ($scale + 1));

            // Add barcode centered.
            $leftPos = imagesx($im) /2 - (imagesx($barcodeImage) /2);
            imagecopy($im, $barcodeImage, $leftMargin + $leftPos, (200 * $scale), 0 , 0 , imagesx($barcodeImage), imagesy($barcodeImage));

            imagedestroy($barcodeImage);
        }

		if ($border) {
            imagerectangle($im, 0, 0, imagesx($im)-1, imagesy($im)-1, imagecolorallocate($im, 100, 100, 100));
        }

        return $im;
    }
}
