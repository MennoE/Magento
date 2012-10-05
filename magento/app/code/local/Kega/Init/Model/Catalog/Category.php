<?php

/**
 * Extended Catalog_Category for handling external urls
 *
 * @category   Kega
 * @package    Kega_Init
 */
class Kega_Init_Model_Catalog_Category extends Mage_Catalog_Model_Category
{
	/**
	 * Get category url.
	 * Returns redirect_url if it's set. Otherwise return default url of category
	 *
	 * @param void
	 * @return String Url to category
	 * @todo find better way for getting redirect_url when it's not in the collection
	 */
	public function getUrl()
	{
		// load full category again because sometimes redirect_url is not part of the data in the model
		$cat = Mage::getModel('catalog/category')->load($this->getId());
		if($cat->getRedirectUrl()) {
			return $cat->getRedirectUrl();
		}
		return parent::getUrl();
	}
}
