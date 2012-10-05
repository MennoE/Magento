<?php
/**
 * @category   Kega
 * @package    Kega_Touch
 */
class Kega_Touch_Model_Sqlite
{
	/**
	 * The current SQLite DB.
	 * @var Zend_Db_Adapter_Pdo_Sqlite $_db
	 */
	protected $_db = null;

	/**
	 * Constructor that initializes SQLite DB.
	 *
	 * @param array with 0 => Zend_Db_Adapter_Pdo_Sqlite
	 *                   1 => boolean 'delete database'
	 */
	public function __construct(array $parameters)
	{
		if (empty($parameters[0])) {
			throw new Kega_Touch_Model_Sqlite_Exception('Parameter 0 should be a filename for the DB.');
		}

		if (isset($parameters[1]) && $parameters[1]) {
			@unlink($parameters[0]);
		}

		$this->_db = Zend_Db::factory('pdo_sqlite', array('dbname' => $parameters[0]));
	}

	public function getTable($tableName, $create = false)
	{
		return Mage::getSingleton('kega_touch/sqlite_' . $tableName, array($this->_db, $create));
	}
}