<?php
class Kega_TntDirect_Helper_Barcode_Data extends Mage_Core_Helper_Abstract
{
	/**
	 * Placeholder for font paths.
	 *
	 * @var array
	 */
	protected static $_fonts;

	/**
	 * Retreive path to font file.
	 *
	 * @param string $name (name of font we want to load).
	 */
    public static function getFont($name)
    {
        if (!isset(self::$_fonts[$name])) {
            if (isset($_SERVER['WINDIR']) && file_exists($_SERVER['WINDIR'])) {
                self::$_fonts[$name] = $_SERVER['WINDIR'] . "\Fonts\{$name}.ttf";
            } else {
                self::$_fonts[$name] = realpath(dirname(__FILE__) . "/../fonts/{$name}.ttf");
            }
        }

        return self::$_fonts[$name];
    }
}
