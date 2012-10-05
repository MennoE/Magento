<?php
class Kega_Pdf_Helper_Image extends Mage_Core_Helper_Abstract
{
    /**
	 * Rotate gd image (90, 180 or 270 degrees).
	 *
	 * @param gd resource $image
	 * @param int $rotation
	 */
	public function rotateImage($image, $rotation)
	{
		$width = imagesx($image);
		$height = imagesy($image);
		switch ($rotation) {
			case 90:
			case 270:
				$newimg = imagecreatetruecolor($height , $width);
				break;
			case 180:
				$newimg = imagecreatetruecolor($width , $height);
				break;
			default:
				return $image;
		}

		for ($i = 0;$i < $width ; $i++) {
			for ($j = 0;$j < $height ; $j++) {
				switch ($rotation) {
					case 90:
						imagecopy($newimg, $image, ($height - 1) - $j, $i, $i, $j, 1, 1);
						break;
					case 180:
						imagecopy($newimg, $image, ($width - $i -1), ($height - 1) - $j, $i, $j, 1, 1);
						break;
					case 270:
						imagecopy($newimg, $image, $j, ($width - $i -1), $i, $j, 1, 1);
						break;
				}
			}
		}

		return $newimg;
	}
}
