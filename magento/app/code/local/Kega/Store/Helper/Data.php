<?php
class Kega_Store_Helper_Data extends Mage_Core_Helper_Abstract
{

	const XML_PATH_GOOGLE_KEY = 'store/google_map/google_map_key';

	const XML_PATH_STORE_SEARCH_RADIUS = 'store/google_map/store_search_radius';

	const XML_PATH_STORE_COUNT = 'store/google_map/store_count';

	const XML_PATH_LANGUAGE = 'store/google_map/language';

	const XML_PATH_FIRST_SORT_ORDER = 'store/storelocator/sort_order_first';

	const XML_PATH_SECOND_SORT_ORDER = 'store/storelocator/sort_order_second';

	const XML_PATH_FIRST_SORT_ORDER_DIRECTION = 'store/storelocator/sort_order_first_direction';

	const XML_PATH_SECOND_SORT_ORDER_DIRECTION = 'store/storelocator/sort_order_second_direction';

	public function getGoogleLanguage()
	{
		return Mage::app()->getStore()->getConfig(self::XML_PATH_LANGUAGE);
	}


	public function getGoogleSearchRadius()
	{
		return Mage::app()->getStore()->getConfig(self::XML_PATH_STORE_SEARCH_RADIUS);
	}


	public function getGoogleStoreCount()
	{
		return Mage::app()->getStore()->getConfig(self::XML_PATH_STORE_COUNT);
	}


	public function getGoogleKey()
	{
		return Mage::app()->getStore()->getConfig(self::XML_PATH_GOOGLE_KEY);
	}

	/**
     * Retrieve sort order from config
     *
     * @param string $type
     * @return mixed (string|boolean)
     */
	public function getSortOrder($type = 'first')
	{
		if ($type == 'first') {
			$path = self::XML_PATH_FIRST_SORT_ORDER;
		}
		else {
			$path = self::XML_PATH_SECOND_SORT_ORDER;
		}

		$value = Mage::getStoreConfig($path);

		if (empty($value)) {
			return false;
		}

		return $value;
	}

	public function getSortOrderDirection($type = 'first')
	{
		if ($type == 'first') {
			$path = self::XML_PATH_FIRST_SORT_ORDER_DIRECTION;
		}
		else {
			$path = self::XML_PATH_SECOND_SORT_ORDER_DIRECTION;
		}

		$value = Mage::getStoreConfig($path);

		if (empty($value)) {
			return false;
		}

		return $value;
	}

	/**
     * Kega_Store_Helper_Data::storeSort()
     * Sort stores by its name
     *
     * @param Kega_Store_Model_store $a
     * @param Kega_Store_Model_store $b
     * @return int
     */
    public static function storeSort($a, $b)
    {
        return strcmp($a->getName(), $b->getName());
    }

	/**
	 * Get magento store by code. Returns null if store not found
	 *
	 * @param string $code
	 * @return Mage_Core_Model_Store|null
	 */
	public function getMagentoStoreByCode($code)
	{
		$storeCollection = Mage::getModel('core/store')->getCollection()
					->addFieldToFilter('code', $code);
		if ($storeCollection->getSize() >= 1) {
			return $storeCollection->getFirstItem();
		}
		return null;
	}

	/**
	 * Get magento store by code. Returns null if website not found
	 *
	 * @param string $code
	 * @return array|null
	 */
	public function getMagentoStoreIdsByWebsiteCode($code)
	{
		$codes = explode(",", $code);

		$storeIds = array();
        $webstores = Mage::app()->getStores(true, true);

		foreach($codes as $code) {
			foreach ($webstores as $store) {
				 if ($store->getWebsite()->getCode() == $code) {
					$id = $store->getWebsite()->getId();
					$storeIds = array_merge($storeIds, Mage::app()->getWebsite($id)->getStoreIds());
				 }
			 }
		}

		if (count($storeIds)) {
			return array_unique($storeIds);
		}

        return null;
	}
    /**
     * Tries to append country code to search criteria by pregmatching a zipcode
     * pattern unique for BE, NL or FR.
     *
     * Returns $criteria (untouched) when unable to match anything.
     *
     * We need this method to append country code when the user enters only
     * a zipcode.
     *
     * @param string $criteria
     * @return string $criteria
     */
    public function getRelatedCountryCode($criteria)
    {
        if (preg_match('#^[0-9]{4}$#', $criteria)) {
            return $criteria . ', BE';
        }
        else if (preg_match('#^[0-9]{4}\s?[a-z]{2}$#i', $criteria)) {
            return $criteria . ', NL';
        }
        else if (preg_match('#^F|FR?-?[0-9]{5}$#i', $criteria)) {
            return $criteria . ', FR';
        }
        else {
            return $criteria;
        }
    }

}