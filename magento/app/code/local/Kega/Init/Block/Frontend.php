<?php

class Kega_Init_Block_Frontend extends Mage_Core_Block_Template
{
	public function _prepareLayout()
	{
		return parent::_prepareLayout();
	}

	/**
	 * Get Category by its Url key
	 *
	 * @param String $categoryUrl
	 * @param String $additionalAttributes
	 * @return Mage_Model_Catalog_Category
	 */
	public function getCategoryByUrl($categoryUrl, $additionalAttributes = '*')
	{
	    return Mage::getModel('catalog/category')->loadByAttribute('url_key', $categoryUrl);
	}

	/**
	 * Get footer items
	 *
	 * return an Array containing footer columns.
	 * Array items can be of two different types:
	 * - Category, Category and its subcategories will be displayed as a list
	 * - Cms block identifier, the content of the corresponding cms block will be shown
	 *
	 * @param void
	 * @return Array
	 * @todo It would be nice if this could be configured from xml.
	 * 		 Idea: Have a footercolums block wich has a method addColumn($identifier, $type, $sortOrder)
	 * 			   Since this method can be called trough xml, columns can be configured from xml
	 */
	public function getFooterItems()
	{
	    return array();
	}

	/**
	 * Get copyright message from config
	 *
	 * @param void
	 * @return String
	 */
	public function getCopyright()
	{
	    return sprintf(Mage::getStoreConfig('design/footer/copyright'), date('Y'));
	}
}