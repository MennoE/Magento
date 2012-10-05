<?php
class Kega_TntDirect_Helper_Barcode_Code39_Data extends Kega_TntDirect_Helper_Barcode_Data
{
    /**
     * Convert the provided barnumber to a code 39 barcode image.
     *
     * @param String  $barnumber Number
     * @param int     $height    Height of the image
     * @param int     $scale     1 = 72 dpi | 2 = 144 dpi | 3 = 216 dpi | 4 = 288 dpi | etc.
     * @param boolean $text      display text under the barcode?
     *
     * @return Object $im        GD image resource
     */
    public function create($barnumber, $height = 45, $scale = 1, $text = true)
    {
        $bars = self::_c39Encode($barnumber);

        if ($scale <1) {
            $scale = 1;
        }

        // Calculate total width & height needed for the barcode.
        $total_x = $scale * strlen($bars) + 2 * $scale * 10;
        $total_y = (double)$scale * $height + 10 * $scale;

        // Create image.
        $im = imagecreate($total_x, $total_y);

        // Define some colors.
        $white = imagecolorallocate($im, 255, 255, 255);
        $black = imagecolorallocate($im, 0, 0, 0);

        // Fill background of image with white.
        imagefilledrectangle($im, 0, 0, $total_x, $total_y, $white);

        if ($text) {
            $height = floor($total_y - ($scale * 8));
        } else {
            $height = floor($total_y);
        }
        $xpos = $scale * 10;
        foreach (str_split($bars) as $val) {
            if ($val == 1) {
                imagefilledrectangle($im, $xpos, 0, $xpos + $scale - 1, $height, $black);
            }
            $xpos += $scale;
        }

        if ($text) {
            $barnumber = "* " . $barnumber . " *";
            $box = imagettfbbox (($scale * 6), 0, self::getFont('arial'), $barnumber);
            $leftPos = floor($total_x-(int)$box[0] - (int)$box[2] + ($scale * 6)) / 2;
            imagettftext($im, ($scale * 6), 0, $leftPos, floor($total_y), $black, self::getFont('arial'), $barnumber);
        }

        return $im;
    }

    /**
     * Encode the provided barnumber to a code 39 barcode string.
     * A Code 39 barcode has the following structure:
     *
     * A start character - the asterisk (*) character.
     * Any number of characters encoded from the table below.
     * A stop character, which is a second asterisk character.
     *
     * @param String  $barnumber Number
     *
     * @return String $mfcStr    1100101011100
     */
    private static function _c39Encode($barnumber)
    {
        $encTable = array('0' => 'NNNWWNWNN',
                          '1' => 'WNNWNNNNW',
                          '2' => 'NNWWNNNNW',
                          '3' => 'WNWWNNNNN',
                          '4' => 'NNNWWNNNW',
                          '5' => 'WNNWWNNNN',
                          '6' => 'NNWWWNNNN',
                          '7' => 'NNNWNNWNW',
                          '8' => 'WNNWNNWNN',
                          '9' => 'NNWWNNWNN',
                          'A' => 'WNNNNWNNW',
                          'B' => 'NNWNNWNNW',
                          'C' => 'WNWNNWNNN',
                          'D' => 'NNNNWWNNW',
                          'E' => 'WNNNWWNNN',
                          'F' => 'NNWNWWNNN',
                          'G' => 'NNNNNWWNW',
                          'H' => 'WNNNNWWNN',
                          'I' => 'NNWNNWWNN',
                          'J' => 'NNNNWWWNN',
                          'K' => 'WNNNNNNWW',
                          'L' => 'NNWNNNNWW',
                          'M' => 'WNWNNNNWN',
                          'N' => 'NNNNWNNWW',
                          'O' => 'WNNNWNNWN',
                          'P' => 'NNWNWNNWN',
                          'Q' => 'NNNNNNWWW',
                          'R' => 'WNNNNNWWN',
                          'S' => 'NNWNNNWWN',
                          'T' => 'NNNNWNWWN',
                          'U' => 'WWNNNNNNW',
                          'V' => 'NWWNNNNNW',
                          'W' => 'WWWNNNNNN',
                          'X' => 'NWNNWNNNW',
                          'Y' => 'WWNNWNNNN',
                          'Z' => 'NWWNWNNNN',
                          '-' => 'NWNNNNWNW',
                          '.' => 'WWNNNNWNN',
                          ' ' => 'NWWNNNWNN',
                          '$' => 'NWNWNWNNN',
                          '/' => 'NWNWNNNWN',
                          '+' => 'NWNNNWNWN',
                          '%' => 'NNNWNWNWN',
                          '*' => 'NWNNWNWNN'
                        );

        $barnumber = "*{$barnumber}*";

        $mfcStr = '';
        for ($i=0;$i<strlen($barnumber);$i++) {
            $tmp = $encTable[$barnumber[$i]];

            $bar = true;
            for($j=0;$j<strlen($tmp);$j++) {
                if ($tmp[$j]=='N' && $bar)
                    $mfcStr.='1';
                else if ($tmp[$j]=='N' && !$bar)
                    $mfcStr.='0';
                else if ($tmp[$j]=='W' && $bar)
                    $mfcStr.='11';
                else if ($tmp[$j]=='W' && !$bar)
                    $mfcStr.='00';

                $bar = !$bar;
            }

            $mfcStr.='0';
        }

        return $mfcStr;
    }
}
