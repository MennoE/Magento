<?php
/**
 * @category   Kega
 * @package    Kega_Touch
 */
class Kega_Touch_Model_Sqlite_Blocks extends Kega_Touch_Model_Sqlite_Content
{
	/**
	 * Table name
	 * @var string
	 */
	protected $_table = 'blocks';

	/**
	 * Adjust output to needed format and export it to SQLite DB.
	 *
	 * @param array $vars (passed by refference)
	 *
	 * @see: http://www.unirgy.com/wiki/URapidFlow/customization
	 */
	public function importURapidFlowOutput(array &$vars)
	{
		// Retreive keymap of the rows.
		$keyMap = array_flip(array_keys($vars['fields']));

		// Retreive all rows that contain a text for the "touch-applicatie/blocks/".
		$rows = $this->_filterCategories($keyMap, $vars['rows'], 'touch-applicatie/blocks/');


		// Insert rows to product table.
		$this->insertRows($rows);

		// No output to URapidFlow file ;-)
		$vars['rows'] = array();
	}
}
