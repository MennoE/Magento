<?php
/**
 * @category   Kega
 * @package    Kega_Touch
 */
class Kega_Touch_Model_Sqlite_Content extends Kega_Touch_Model_Sqlite_Abstract
{
	/**
	 * Table name
	 * @var string
	 */
	protected $_table = 'content';

	/**
	 * Column definitions
	 * Reflects the mapping of Export profile 'Touch - Category Export'.
	 * @var array
	 */
	protected $_columns = array('id' => 'INTEGER PRIMARY KEY',
								'url_path' => 'TEXT',
								'url_key' => 'TEXT',
								'name' => 'TEXT',
								'description' => 'TEXT',
								'image' => 'TEXT',
								'is_active' => 'NUMERIC',
								'position' => 'NUMERIC',
								'config' => 'TEXT',
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

		// Retreive all rows that contain a text for the "touch-applicatie/content/".
		$rows = $this->_filterCategories($keyMap, $vars['rows'], 'touch-applicatie/content/');

		// Insert rows to product table.
		$this->insertRows($rows);

		// We need to export another part of the categories to the blocks table.
		$blocks = Mage::getSingleton('kega_touch/sqlite_blocks', array($this->_db, true));
		$blocks->importURapidFlowOutput($vars);

		// No output to URapidFlow file ;-)
		$vars['rows'] = array();
	}

	/**
	 * Retreive all rows that are mapped to the category "touch-applicatie/.../...".
	 *
	 * @param array $keyMap
	 * @param array $rows
	 * @param string $key
	 *
	 * @return array (all rows that match the provided key)
	 */
	protected function _filterCategories(array $keyMap, array &$rows, $key)
	{
		$matchingRows = array();
		foreach ($rows as $row) {
			if ($row[$keyMap['url_path']] == $key ||
				substr($row[$keyMap['url_path']], 0, strlen($key)) != $key) {
				continue;
			}

			// Fix is active field, convert to boolean.
			$row[$keyMap['is_active']] = ($row[$keyMap['is_active']] == 'Ja' ? true : false);
			$this->_parseConfig($keyMap, $row);

			// If we have an image, retreive the url.
			if (!empty($row[$keyMap['image']])) {
				$row[$keyMap['image']] = Mage::getModel('catalog/category')
											->load($row[$keyMap['id']])
											->getImageUrl();
			}

			$matchingRows[] = $row;

		}

		return $matchingRows;
	}

	/**
	 * Parse config data (set in META KEYWORDS field) into JSON encoded array.
	 * In the admin, you can fill the Meta keywords field with config data in the following format:
	 * filter:category|heren, type|slippers
	 * color:#ffffff
	 *
	 * @param array $keyMap
	 * @param array $row (passed by refference)
	 */
	private function _parseConfig(array $keyMap, array &$row)
	{
		$rawConfig = str_replace("\r", '', $row[$keyMap['config']]);
		$rawConfig = trim($rawConfig);

		if (empty($rawConfig)) {
			return;
		}

		$config = array();
		// Retreive all config rows.
		foreach (explode("\n", $rawConfig) as $configRow) {
			$configRow = trim($configRow);
			// Retreive config key and value.
			@list($key, $value) = explode(":", $configRow);

			// If the config param is a filter, we need to parse a level deeper.
			if ($key == 'filter') {
				$filters = array();

				// Retreive all filter items.
				foreach (explode(",", $value) as $filterItem) {
					$filterItem = trim($filterItem);

					// Retreive filter key and value.
					@list($filterKey, $filterValue) = explode("|", $filterItem);
					$filters[$filterKey] = $filterValue;
				}

				$value = $filters;
			}

			$config[$key] = $value;
		}

		$row[$keyMap['config']] = json_encode($config);
	}
}
