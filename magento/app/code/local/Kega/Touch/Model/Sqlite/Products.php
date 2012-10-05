<?php
/**
 * @category   Kega
 * @package    Kega_Touch
 */
class Kega_Touch_Model_Sqlite_Products extends Kega_Touch_Model_Sqlite_Abstract
{
	/**
	 * Table name
	 * @var string
	 */
	protected $_table = 'products';

	/**
	 * These categories need to be skipped during export.
	 * @var array
	 */
	protected $_skipCategories = array('Nieuw', 'Merken', 'Thema', 'Onderhoud');

	/**
	 * Column definitions
	 * Reflects the mapping of Export profile 'Touch - Product Export'.
	 * @var array
	 */
	protected $_columns = array('id' => 'INTEGER PRIMARY KEY',
								'type' => 'TEXT',
								'status' => 'NUMERIC',
								'sku' => 'TEXT',
								'name' => 'TEXT',
								'short_description' => 'TEXT',
								'description' => 'TEXT',
								'price' => 'NUMERIC',
								'special_price' => 'NUMERIC',
								'special_from_date' => 'TEXT',
								'special_to_date' => 'TEXT',
								'labels' => 'TEXT',
								'categories' => 'TEXT',
								'types' => 'TEXT',
								'sizes' => 'TEXT',
								'upsell' => 'TEXT',
								'category_filter_positions' => 'TEXT',
								'images' => 'TEXT',
								'attributes' => 'TEXT',
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
			// Bugfix: It was not possible to add this filter in the Export profile setting.
			// We get an integrity constrain error when selecting Status Active in combination with the other filters.
			// Skip rows that are not active.
			if ($row[$keyMap['status']] != 'Ingeschakeld') {
				$row = null; // Remove from dataset.
				continue;
			}

			// Set boolean value on status field for active products.
			$row[$keyMap['status']] = true;

			$product = Mage::getModel('catalog/product')->load($row[$keyMap['id']]);

			$this->_splitCategoryNames($keyMap, $row);
			$this->_addUpsellProducts($keyMap, $row, $product);
			$this->_addSimpleProducts($keyMap, $row, $product);
			$this->_addCategoryFilterPositions($keyMap, $row, $product);
			$this->_addMediaGalleryImages($keyMap, $row, $product);
			$this->_combineAttributes($keyMap, $row);
		}

		// Insert rows to product table.
		$this->insertRows($vars['rows']);

		// Remove all products that do not have a category or type set.
		$this->_db->exec("DELETE FROM {$this->_table} WHERE categories = '[]' AND types = '[]'");

		// No output to URapidFlow file ;-)
		$vars['rows'] = array();
	}

	/**
	 * Split category names into category and type.
	 * In the export every category is seperated by ';' and every level by '>'
	 * The categories are placed over the original data (as json encoded array).
	 * The types are placed into the placeholder [const.value] field (as json encoded array).
	 *
	 * @param array $keyMap
	 * @param array $row (passed by refference)
	 */
	private function _splitCategoryNames(array $keyMap, array &$row)
	{
		$categories = array();
		$types = array();

		foreach (explode(';', $row[$keyMap['categories']]) as $category) {
			if ($levelPos = strpos($category, '>')) {
				$cat = trim(substr($category, 0, $levelPos));
			} else {
				$cat = $category;
			}

			if (in_array($cat, $this->_skipCategories) || empty($cat)) {
				continue;
			}

			$categories[$cat] = array('value' => $cat);

			if ($levelPos = strrpos($category, '>')) {
				$type = trim(substr($category, $levelPos + 1));

				// Work-arround for kids section, we need these as category instead of type.
				if ($type == 'Jongensschoenen' || $type == 'Meisjesschoenen') {
					$categories[$type] = array('value' => $type);
					continue;
				}

				$types[$type] = array('value' => $type);
			}
		}

		$row[$keyMap['categories']] = json_encode(array_values($categories));
		$row[$keyMap['types']] = json_encode(array_values($types));
	}

	/**
	 * Add upsell product data into the placeholder [const.value] field (as json encoded array).
	 *
	 * @param array $keyMap
	 * @param array $vars (passed by refference)
	 * @param Mage_Catalog_Model_Product $product
	 */
	private function _addUpSellProducts(array $keyMap, array &$row, Mage_Catalog_Model_Product $product)
	{
		$upsellProducts = $product->getUpSellProducts();
		if (!$upsellProducts) {
			return;
		}

		$upsell = array();
		foreach ($upsellProducts as $upsellProduct) {
			$upsell[] = array('id' => $upsellProduct->getId(),
							  'sku' => $upsellProduct->getSku(),
							  'position' => $upsellProduct->getPosition(),
							  );
		}

		$row[$keyMap['upsell']] = json_encode($upsell);
	}

	/**
	 * Add all simple products that belong to this configurable product.
	 *
	 * @param array $keyMap
	 * @param array $vars (passed by refference)
	 * @param Mage_Catalog_Model_Product $product
	 */
	private function _addSimpleProducts(array $keyMap, array &$row, $product)
	{
		if ($row[$keyMap['type']] == 'simple') {
			return;
		}

		$simpleProducts = Mage::getModel('catalog/product_type_configurable')
							->getUsedProducts(null, $product);

		if (!$simpleProducts) {
			return;
		}

		$sizes = array();
		foreach ($simpleProducts as $simpleProduct) {
			// Retreive size attribute.
			$size = $simpleProduct->getResource()->getAttribute('size');

			$sizes[] = array('id' => $simpleProduct->getId(),
							 'value' => $size->getFrontend()->getValue($simpleProduct),
							 'sku' => $simpleProduct->getSku(),
							 'price' => $simpleProduct->getPrice(),
							 'special_price' => $simpleProduct->getSpecialPrice(),
							 'special_from_date' => $simpleProduct->getSpecialFromDate(),
							 'special_to_date' => $simpleProduct->getSpecialToDate(),
							 'qty' => $simpleProduct->getStockItem()->getQty(),
							 'supper_attribute_key' => $size->getId(),
							 'supper_attribute_value' => $simpleProduct->getSize(),
							 );
		}

		$row[$keyMap['sizes']] = json_encode($sizes);
	}

	/**
	 * Add all simple products that belong to this configurable product.
	 *
	 * @param array $keyMap
	 * @param array $vars (passed by refference)
	 * @param Mage_Catalog_Model_Product $product
	 */
	private function _addCategoryFilterPositions(array $keyMap, array &$row, $product)
	{
		$categories = Mage::getModel('catalog/category')->getCollection()
			->addAttributeToSelect('name')
			->joinField('position',
                'catalog/category_product',
                'position',
                'category_id=entity_id',
                '`product_id` = ' . $product->getId(),
                'left');

		// Filter out all categories without a position.
		$categories->getSelect()->where('`_table_position`.`position` IS NOT NULL');

		$categoryFilterPositions = array();
		foreach ($categories as $category) {
			$categoryFilterPositions[$category->getName()] = $category->getPosition();
		}
		unset($categories);

		$row[$keyMap['category_filter_positions']] = json_encode($categoryFilterPositions);
	}

	/**
	 * Add all product images to this configurable product.
	 *
	 * @param array $keyMap
	 * @param array $vars (passed by refference)
	 * @param Mage_Catalog_Model_Product $product
	 */
	private function _addMediaGalleryImages(array $keyMap, array &$row, $product)
	{
		$images = array();
		foreach ($product->getMediaGalleryImages() as $image) {
			$images[] = array('thumbnail' => $image->getUrl(),
							  'zoom' => $image->getZoom());
		}

		$row[$keyMap['images']] = json_encode($images);
	}

	/**
	 * Combine all attr_ fields to one JSON encoded array in field attributes.
	 *
	 * @param array $keyMap
	 * @param array $vars (passed by refference)
	 */
	private function _combineAttributes(array $keyMap, array &$row)
	{
		$attributes = array();
		foreach ($keyMap as $key => $position) {
			if (substr($key, 0, 5) != 'attr_') {
				continue;
			}

			if (!empty($row[$position])) {
				$key = substr($key, 5);
				$attributes[$key] = array();
				if ($key == 'zie_kleur') {
					// Explode multi value attribute.
					foreach (explode(';', $row[$position]) as $value) {
						$attributes[$key][] = array('value' => $value);
					}
				} else {
					// Single value attribute.
					$attributes[$key][] = array('value' => $row[$position]);
				}
			}

			// Remove attribute value from array (so it is not created as empty field in SQLite DB.
			unset($row[$position]);
		}

		$row[$keyMap['attributes']] = json_encode($attributes);
	}
}