<?php
/**
 * @category   Kega
 * @package    Kega_Touch
 */
class Kega_Touch_Model_Sqlite_Abstract
{
	/**
	 * Table name
	 * @var string
	 */
	protected $_table = null;

	/**
	 * The current SQLite DB.
	 * @var Zend_Db_Adapter_Pdo_Sqlite $_db
	 */
	protected $_db = null;

	/**
	 * Constructor that initializes SQLite DB.
	 *
	 * @param array with 0 => Zend_Db_Adapter_Pdo_Sqlite
	 *                   1 => boolean 'create table'
	 */
	public function __construct(array $parameters)
	{
		if (empty($parameters[0])) {
			throw new Kega_Touch_Model_Sqlite_Exception('Parameter 0 should be an instance of Zend_Db_Adapter_Pdo_Sqlite.');
		}

		$this->_db = $parameters[0];

		if (isset($parameters[1]) && $parameters[1]) {
			$this->_createTable($this->_table, $this->_columns);
		}
	}

	/**
	 * Create table in SQLite database.
	 *
	 * @param string $table
	 * @param array $columns
	 */
	protected function _createTable($table, $columns)
	{
		$fields = array();
		foreach ($columns as $field => $type) {
			$fields[] = $field . ' ' . $type;
		}

		$this->_db->exec('CREATE TABLE ' . $table .
						 '(' . implode(', ', $fields) . ')');
	}

	/**
	 * Insert provided rows into SQLite DB with the use of one query.
	 * We combine all rows to one query with the use of UNION.
	 *
	 * @param array $rows
	 */
	public function insertRows($rows)
	{
		$rowCount = count($rows);
		if ($rowCount > 500) {
			throw new Kega_Touch_Model_Sqlite_Exception('Maximum allowed rows to insert at once is 500, you provided: ' . $rowCount . ' rows.');
		}

		if ($rowCount == 0) {
			return;
		}

		echo 'Importing ' . $rowCount . ' rows into table: ' . $this->_table . '.' .PHP_EOL;

		$sql = '';
		foreach ($rows as $row) {
			if (!$row) {
				continue;
			}

			$sql .= PHP_EOL . 'SELECT ';
			foreach ($row as $value) {
				$sql .= $this->_db->quote($value) . ',';
			}
			$sql = substr($sql, 0, -1) . PHP_EOL . 'UNION';
		}

		$sql = substr($sql, 0, -6);

		if (!empty($sql)) {
			$this->_db->exec('INSERT INTO ' . $this->_table . $sql);
		}

		return $this;
	}
}