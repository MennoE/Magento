<?php
/**
 * @category   Kega
 * @package    Kega_Touch
 */
class Kega_Touch_Model_Sqlite_Stock extends Kega_Touch_Model_Sqlite_Abstract
{
	/**
	 * Table name
	 * @var string
	 */
	protected $_table = 'stock';

	/**
	 * Column definitions
	 * Reflects the mapping of Export profile 'Touch - Product Export'.
	 * @var array
	 */
	protected $_columns = array('id' => 'INTEGER PRIMARY KEY',
								'qty' => 'NUMERIC',
								'updated_at' => 'NUMERIC',
								'processed' => 'NUMERIC',
								);

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

		foreach ($vars['rows'] as &$row) { // Pass by reference!
			$this->_setProcessedBoolean($keyMap, $row);
		}

		// Insert rows to product table.
		$this->insertRows($vars['rows']);

		// No output to URapidFlow file ;-)
		$vars['rows'] = array();
	}

	/**
	 * Set processed boolean to false.
	 * This is internally used by the touch app.
	 *
	 * @param array $keyMap
	 * @param array $vars (passed by refference)
	 */
	private function _setProcessedBoolean(array $keyMap, array &$row)
	{
		$row[$keyMap['processed']] = false;
	}
}