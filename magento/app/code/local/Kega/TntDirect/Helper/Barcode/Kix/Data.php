<?php
class Kega_TntDirect_Helper_Barcode_Kix_Data extends Kega_TntDirect_Helper_Barcode_Data
{
   /**
     * Convert the provided street and postcode data to KIX barcode image.
     *
     * @see http://www.xat.nl/riscos/sw/riscos/kix/index.htm
     * @see http://www.tntpost.nl/zakelijk/klantenservice/downloads/kIX_code/download.aspx
     *
     * @param String  $street    Wattstraat 3 | Antwoordnummer 2000 | Postbus 1100
     * @param String  $postcode  2181TP | 2181 TP
     * @param int     $scale     1 = 72dpi | 2 = 144 dpi | 3 = 216 dpi | 4 = 288 dpi | etc.
     * @param boolean $text      display text under the barcode?
     *
     * @return Object $im        GD image resource
     */
    public function create($street, $postcode, $scale = 1, $text = true)
    {
        if ($scale <1) {
            $scale = 1;
        }

        $barnumber = self::_KixEncode($street, $postcode);

        // Calculate total width & height needed for the barcode.
        $total_x = $scale * (strlen($barnumber) * 14) + ($scale * 0);
        if ($text) {
            $total_y = $scale * 24;
            $ypos = floor($total_y - ($scale * 16));
        } else {
            $total_y = $scale * 18;
            $ypos = floor($total_y - ($scale * 8));
        }

        // Create image.
        if ($total_x == 0) {
        	$total_x = 1;
        }
        if ($total_y == 0) {
        	$total_y = 1;
        }
        $im = imagecreatetruecolor($total_x, $total_y);

        // Define some colors.
        $white = imagecolorallocate($im, 255, 255, 255);
        $black = imagecolorallocate($im, 0, 0, 0);

        // Fill background of image with white.
        imagefilledrectangle($im, 0, 0, $total_x, $total_y, $white);

        $xpos = $scale * 1;
		imagettftext($im, ($scale * 6), 0, $xpos, $ypos, $black, self::getFont('kix'), $barnumber);
        if ($text) {
            $ypos = floor($total_y) - 1 * $scale;
            imagettftext($im, ($scale * 4), 0, $xpos, $ypos, $black, self::getFont('arial'), $barnumber);
        }

        return $im;
    }

    /**
     * Create an address sticker (image) with KIX barcode..
     *
     *
     * @param String  $street    Wattstraat 3 | Antwoordnummer 2000 | Postbus 1100
     * @param String  $postcode  2181TP | 2181 TP
     * @param String  $city      Sassenheim
     * @param String  $country   Nederland (if used, no KIX barcode generated!)
     * @param int     $scale     1 = 72 dpi | 2 = 144 dpi | 3 = 216 dpi | 4 = 288 dpi | etc.
     * @param boolean $text      display text under the barcode?
     * @param boolean $border    Add border?
     *
     * @return Object $im        GD image resource
     */
    public function addressSticker($name, $street, $postcode, $city, $country = null, $scale = 1, $text = true, $border = true)
    {
        // Create image of 10x4 (cm).
        $im = imagecreate(295 * $scale, 118 * $scale);

        // Define colors.
        $white = imagecolorallocate($im, 255, 255, 255);
        $black = imagecolorallocate($im, 0, 0, 0);

        // Fill background of image with white.
        imagefilledrectangle($im, 0, 0, imagesx($im), imagesy($im), $white);

        if ($border) {
            imagerectangle($im, 0, 0, imagesx($im)-1, imagesy($im)-1, imagecolorallocate($im, 100, 100, 100));
        }

        $xpos = (14 * $scale);
        imagettftext($im, (10 * $scale), 0, $xpos, (22 * $scale), $black, self::getFont('arial'), $name);

        imagettftext($im, (10 * $scale), 0, $xpos, (42 * $scale), $black, self::getFont('arial'), $street);

        imagettftext($im, (12 * $scale), 0, $xpos, (70 * $scale), $black, self::getFont('arialbd'), str_replace(' ', '', $postcode));
        imagettftext($im, (12 * $scale), 0, $xpos + (12 * $scale * 6), (70 * $scale), $black, self::getFont('arialbd'), strtoupper($city));

        if (!empty($country)) {
            imagettftext($im, (12 * $scale), 0, $xpos, (95 * $scale), $black, self::getFont('arial'), $country);
        } else {
            // Add Kix barcode
            $barcodeImage = $this->create($street, $postcode, $scale, $text);
            imagecopy($im, $barcodeImage, $xpos, (84 * $scale), 0 , 0 , imagesx($barcodeImage), imagesy($barcodeImage));
            imagedestroy($barcodeImage);
        }

        return $im;
    }

    /**
     * Encode the provided street and postcode data to KIX barcode format.
     * @see http://www.xat.nl/riscos/sw/riscos/kix/index.htm
     * @see http://www.tntpost.nl/zakelijk/klantenservice/downloads/kIX_code/download.aspx
     *
     * @param String  $street    Wattstraat 3 | Antwoordnummer 2000 | Postbus 1100
     * @param String  $postcode  2181TP | 2181 TP
     *
     * @return String $kixcode   2181TP3
     */
    private static function _KixEncode($street, $postcode)
    {
        $postcode = str_replace(' ', '', $postcode);

        $number        = '';
		$numberSuffix  = '';

		// Split street into street, number, addition.
		$matches = array();
		preg_match('/([^0-9]+)([0-9]+)(.*)/', $street, $matches);
		if (!empty($matches[1])) {
			$street = trim($matches[1]);
		}
		if (!empty($matches[2])) {
			$number = trim($matches[2]);
		}
		if (!empty($matches[3])) {
			$numberSuffix = trim($matches[3]);
		}

		// If number contains 12-20 use only the first part.
		if (strpos($number, '-') !== false) {
			$parts = explode('-', $number);
			list($number) = array_slice($parts, 0, 1);
			// Add the rest to housenumber addition field.
			$numberSuffix = trim(implode('-', array_slice($parts, 1)) . ' ' . $numberSuffix);
		}
		$numberSuffix = str_replace(array(' ', '-'), 'X', $numberSuffix);

        return strtoupper($postcode . $number . (strlen($numberSuffix) >0 ? 'X' . $numberSuffix : ''));
    }
}
