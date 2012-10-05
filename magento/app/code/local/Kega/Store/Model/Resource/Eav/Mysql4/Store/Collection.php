<?php
class Kega_Store_Model_Resource_Eav_Mysql4_Store_Collection extends Mage_Catalog_Model_Resource_Eav_Mysql4_Collection_Abstract
{
	protected function _construct()
	{
		$this->_init('store/store');
	}

	/**
	 * Retrieve all stores 'near' the given criteria.
	 *
	 * Select all attributes, apply the criteria and radius as filters.
	 * And if provided, set a limiter.
	 *
	 * @param string $criteria
	 * @param int $radius (in km's)
	 * @param int $limit
	 *
	 * @return Kega_Store_Model_Resource_Eav_Mysql4_Store_Collection
	 */
	public function getNearestActiveStores($criteria, $radius, $limit = null)
	{
		$this->clear();

		$this->addAttributeToSelect('*')
			 ->addNearestStoreFilter($criteria, $radius)
			 ->addFieldToFilter('is_active', '1')
			 ->addAttributeToSort('distance', 'asc');

		if ($limit) {
			$this->addLimit($limit);
		}

		return $this;
	}

	/**
	 * Retrieve the store that is 'nearest' the given criteria.
	 * Criteria can be an array, if so, we internally use addDistanceByLatitudeAndLongitude(field 0, field 1).
	 *
	 * @param string|array $criteria
	 *
	 * @param int $radius (in km's)
	 *
	 * @return Kega_Store_Model_Store
	 */
	public function getNearestActiveStore($criteria, $radius)
	{
		$item = $this->getNearestActiveStores($criteria, $radius)->getFirstItem();

		return ($item->getId() ? $item : null);
	}

	/**
	 * @param string $criteria
	 * @param int $radius
	 *
	 * @return Kega_Store_Model_Resource_Eav_Mysql4_Store_Collection
	 */
	public function addNearestStoreFilter($criteria, $radius)
	{
		return $this->addDistanceByCriteria($criteria)
					->addFieldToFilter('distance', array('lt' => $radius));
	}

	/**
	 * Add distance by criteria.
	 * Criteria can be an array, if so, we internally use addDistanceByLatitudeAndLongitude(field 0, field 1).
	 *
	 * @param string|array $criteria
	 *
	 * @return Kega_Store_Model_Resource_Eav_Mysql4_Store_Collection
	 */
	public function addDistanceByCriteria($criteria)
	{
		if (is_array($criteria)) {
			return $this->addDistanceByLatitudeAndLongitude($criteria[0], $criteria[1]);
		}

		$geoData = Mage::helper('store/import')->getGeoLocation($criteria);

		return $this->addDistanceByLatitudeAndLongitude($geoData[3], $geoData[2]);
	}

	/**15)',
	 * @param string $latitude
	 * @param string $longitude
	 *
	 *
	 * @return Kega_Store_Model_Resource_Eav_Mysql4_Store_Collection
	 */
	public function addDistanceByLatitudeAndLongitude($latitude, $longitude)
	{
		$expression = sprintf('(((acos(sin((%1$s * pi()/180)) * sin(({{lat}}*pi()/180))+cos((%1$s * pi()/180)) * cos(({{lat}}*pi()/180)) * cos(((%2$s - {{lng}})*pi()/180))))*180/pi())*60*1.1515)',
							  $latitude, $longitude);

		$this->addExpressionAttributeToSelect('distance', $expression, array('lat', 'lng'));

		return $this;
	}

	/**
	 * Limit the store collection to x results.
	 *
	 * @param int $limit
	 *
	 * @return Kega_Store_Model_Resource_Eav_Mysql4_Store_Collection
	 */
	public function addLimit($limit)
	{
		$this->_select->limit($limit);

		return $this;
	}

	/**
     * Add the distance from customer store to retail store collection
     *
     * @param   int $lat
     * @param   int $lat
	 *
	 * @deprecated use: addDistanceByLatitudeAndLongitude
	 *
     * @return  Kega_Store_Model_Resource_Eav_Mysql4_Store_Collection
     */
    public function addDistance($lat, $lng, $limit = false)
    {
		$this->addDistanceByLatitudeAndLongitude($lat, $lng);
		$this->addAttributeToSort('distance', 'asc');

        if ($limit) {
			$this->_select->limit($limit);
        }
        return $this;
    }

	/**
	 * Filter the collection for matching $storeviewIds
	 * @param $storeviewIds
	 *
	 * @return Kega_Store_Model_Resource_Eav_Mysql4_Store_Collection
	 */
	public function addStoreviewFilter($storeviewIds)
	{
		if (is_array($storeviewIds)) {
			$storeviewIds = implode(', ', $storeviewIds);
		}		$this->_select->join(			array('storeview' => $this->getTable('store/storeview')),			'e.entity_id = storeview.store_id AND (storeview.storeview_id IN (' . $storeviewIds .') OR storeview.storeview_id = 0)');		$this->_select->group('entity_id');	}}