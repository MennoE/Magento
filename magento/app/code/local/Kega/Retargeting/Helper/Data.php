<?php

class Kega_Retargeting_Helper_Data extends Mage_Core_Helper_Abstract
{
	/**
	 * Get category model based on URL parameters
	 *
	 * @param void
	 * @return Kega_Init_Model_Catalog_Category|bool
	 */
	public function loadCurrentCategory()
	{
		$params = Mage::app()->getRequest()->getParams();
		$controller = Mage::app()->getRequest()->getControllerName();
		$categoryId = $currentCategory = '';

		if ($controller == 'product') {
			$categoryId = isset($params['category']) ? intval($params['category']) : '';
		}
		elseif ($controller == 'category') {
			$categoryId = intval($params['id']);
		}

		// Load category if category ID is set
		if ($categoryId) {
			$currentCategory = Mage::getModel('catalog/category')->load($categoryId);
		}
		return $currentCategory ? $currentCategory : false;
	}

	/**
	 * Get product model based on URL parameters
	 *
	 * @param void
	 * @return Kega_Init_Model_Catalog_Product|bool
	 */
	public function loadCurrentProduct()
	{
		$params = Mage::app()->getRequest()->getParams();
		$controller = Mage::app()->getRequest()->getControllerName();
		$productId = $currentProduct = '';

		if ($controller == 'product') {
			$productId = intval($params['id']);
		}

		// Load product if product ID is set
		if ($productId) {
			$currentProduct = Mage::getModel('catalog/product')->load($productId);
		}
		return $currentProduct ? $currentProduct : false;
	}

}