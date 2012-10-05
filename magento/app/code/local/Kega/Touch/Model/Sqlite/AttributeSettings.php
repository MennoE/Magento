<?php
/**
 * @category   Kega
 * @package    Kega_Touch
 */
class Kega_Touch_Model_Sqlite_AttributeSettings extends Kega_Touch_Model_Sqlite_Abstract
{
	/**
	 * Table name
	 * @var string
	 */
	protected $_table = 'attribute_settings';

	/**
	 * Placeholder for attribute translations.
	 * @var array
	 */
	protected $_translations = array();

	/**
	 * Column definitions
	 * Reflects the mapping of Export profile 'Touch - Product Export'.
	 * @var array
	 */
	protected $_columns = array('attribute_code' => 'TEXT PRIMARY KEY',
								'is_global' => 'NUMERIC',
								'is_visible' => 'NUMERIC',
								'is_searchable' => 'NUMERIC',
								'is_filterable' => 'NUMERIC',
								'is_comparable' => 'NUMERIC',
								'is_visible_on_front' => 'NUMERIC',
								'is_html_allowed_on_front' => 'NUMERIC',
								'is_used_for_price_rules' => 'NUMERIC',
								'is_filterable_in_search' => 'NUMERIC',
								'used_in_product_listing' => 'NUMERIC',
								'used_for_sort_by' => 'NUMERIC',
								'is_configurable' => 'NUMERIC',
								'apply_to' => 'TEXT',
								'is_visible_in_advanced_search' => 'NUMERIC',
								'position' => 'NUMERIC',
								'is_wysiwyg_enabled' => 'NUMERIC',
								'frontend_label' => 'TEXT',
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
		$rows = $this->_mergeTranslations($vars['rows']);

		// Insert rows to attribute settings table.
		$this->insertRows($rows);

		// No output to URapidFlow file ;-)
		$vars['rows'] = array();
	}

	/**
	 * The export holds 2 type of exports.
	 * EA (That holds the translation of the attribute)
	 * EAXP (That holds all settings about visible, active etc.)
	 * We merge the translations from the EA records into the EAXP records.
	 *
	 * @param array $rows
	 */
	private function _mergeTranslations(array $rows)
	{
		$mergedRows = array();
		foreach ($rows as &$row) { // Pass by reference!
			// Position 1 holds the attribute_code.
			$attributeCode = $row[1];

			if ($row[0] == 'EA') {
				// Position 4 holds the frontend_label, add to translation array.
				$this->_translations[$attributeCode] = $row[4];
			} else if ($row[0] == 'EAXP') {
				// Remove type field.
				array_shift($row);

				// Add translation to the export row.
				$row[] = (isset($this->_translations[$attributeCode]) ? $this->_translations[$attributeCode] : '');

				$mergedRows[] = $row;
			}
		}

		return $mergedRows;
	}
}