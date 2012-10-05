<?php

class Kega_Store_Block_Store extends Mage_Core_Block_Template
{
	public $markers = array();

    public function __construct()
    {
        parent::__construct();
	}

	/**
	 * Kega_Store_Block_Store->pushMarker()
	 * Adds a marker to the marker collection variable
	 *
     * @param void
	 * @return string
	 */
	public function pushMarker($data)
	{
		array_push($this->markers, $data);
	}

	/**
	 * Kega_Store_Block_Store->getMarkers()
	 * Fetches all collected map markers
	 *
     * @param void
	 * @return string
	 */
	public function getMarkers()
	{
		return implode(",", $this->markers);
	}

	/**
	 * Kega_Store_Block_Store->buildMarkerData()
	 * Creates the JS marker data for the given establishment
	 *
     * @param $establishment Kega_Store_Model_store
	 * @return string
	 */
	public function buildMarkerData($store)
	{
		return sprintf(
			'{"id":"%s","name": "%s", "lon": %s, "lat": %s, "markerHTML":"<h4>%s</h4><p>%s %s%s<br />%s %s<br />Tel: %s</p><p><a href=\"%s\">%s</a>", "type":"marker"}',
			$store->getId(), $store->getName(), $store->getLat(), $store->getLng(), $store->getName(), $store->getAddress(), $store->getNumber(), $store->getNumberExt(), $store->getZipcode(), $store->getCity(), $store->getPhone(), $this->getUrl('winkel').$store->getCustomUrl(), $this->__('Show store information')
		);
	}

	/**
	 * Kega_Store_Block_Store->getStoreDetails()
	 * Retrieve the details for the selected shop
	 *
     * @param void
	 * @return Kega_Store_Model_Store
	 */
	public function getStoreDetails()
	{
		$stores = $this->getStoreCollection(
			$this->getRequest()->getParam('view')
		);
		foreach($stores as $store) {
			$store->getOpeningData();
		}
		return $store;
	}

	/**
	 * Kega_Store_Block_Store->getStoreHtml()
	 * Passes $store to $template and renders it
	 *
     * @param $store Kega_Store_Model_Store
     * @param $template string
	 * @param $var string
	 * @return string
	 */
	public function getStoreHtml($store, $template = 'store/details.phtml', $var)
	{
		echo $this->getLayout()->createBlock('store/store')
			->setTemplate($template)
			->setStore($store)
			->setVariable($var)
			->toHtml();
	}

	/**
	 * Retrieve all establishments and their timetable
	 * Only uses by store when status code was 200
	 *
     * @param void
	 * @return Kega_Store_Model_Store
	 */
	public function getStores()
	{
		$geodata = Mage::registry('geodata');

		if ($this->getRequest()->getParam("criteria")) {
			$stores = $this->getStoreRetrievalType($geodata);
		}
		else {
			$stores = $this->getStoreCollection(
				Mage::registry('store-detail')
					? Mage::registry('store-detail')
					: false
			);
		}

		return $stores;
	}

	/**
	 * Kega_Store_Block_Store->getShopRetrievalType()
	 * Returns the shops by using geodata, or just retrieving all
	 *
     * @param $geodata array
	 * @return array
	 */
	public function getStoreRetrievalType($geodata)
	{
		if ($geodata['status'] == '200') {
			return $this->getStoresByGoogleCode(
				$geodata['lat'],
				$geodata['long'],
				Mage::helper('store')->getGoogleSearchRadius()
			);
		}
		else {
			return $this->getStoreCollection();
		}
	}

	/**
	 * Fetches all stores from our database
	 *
     * @param void
	 * @return Kega_Store_Model_Store
	 */
	public function getStoreCollection($key = '')
	{
		$firstSortOrder = Mage::helper('store')->getSortOrder('first');
		$secondSortOrder = Mage::helper('store')->getSortOrder('second');

		$firstSortOrderDirection = Mage::helper('store')->getSortOrderDirection('first');
		$secondSortOrderDirection = Mage::helper('store')->getSortOrderDirection('second');

		$stores = Mage::getModel('store/store')->getCollection()
			->addAttributeToSelect('name')
			->addAttributeToSelect('address')
			->addAttributeToSelect('number')
			->addAttributeToSelect('number_ext')
			->addAttributeToSelect('zipcode')
			->addAttributeToSelect('city')
			->addAttributeToSelect('district')
			->addAttributeToSelect('description')
			->addAttributeToSelect('lat')
			->addAttributeToSelect('lng')
			->addAttributeToSelect('phone')
			->addAttributeToSelect('fax')
			->addAttributeToSelect('email')
			->addAttributeToSelect('custom_url')
			->addAttributeToSelect('evening_opening')
			->addAttributeToSelect('sunday_opening')
			->addAttributeToSelect('employees')
			->addAttributeToSelect('opening_date_store')
			->addAttributeToSelect('store_front_image')
			->addAttributeToSelect('store_front_image_thumb')
			->addAttributeToSelect('manager_name')
			->addAttributeToSelect('store_image')
			->addAttributeToSelect('manager_image')
			->addFieldToFilter('is_active', '1');

		if ($firstSortOrder && empty($key)) {
			$stores->addAttributeToSort($firstSortOrder, $firstSortOrderDirection);
		}
		if ($secondSortOrder && empty($key)) {
			$stores->addAttributeToSort($secondSortOrder, $secondSortOrderDirection);
		}

		if ($key) {
			$stores->addAttributeToFilter('custom_url', $key);
		}

		foreach($stores as $store) {
			$store->getOpeningData();
			$this->pushMarker($this->buildMarkerData($store));
		}
		return $stores;
	}

	/**
	 * Retrieve stores that are near our search criteria
	 *
	 * @param $latitude  float
	 * @param $longitude float
	 * @param $radius    integer
	 * @param bool $filterStore
	 *
	 * @return Kega_Store_Model_Store
	 */
    public function getStoresByGoogleCode($latitude, $longitude, $radius, $filterStore = false)
	{
    	$get_shops = Mage::getModel('store/store')->getCollection()
			->getNearestActiveStores(array($latitude, $longitude), $radius);

		if($filterStore) {
			$get_shops->addAttributeToFilter('entity_id', array('nin' => array($filterStore)));
		}

		foreach($get_shops as $store) {
			$store->getOpeningData();
			$this->pushMarker($this->buildMarkerData($store));
		}

		return $get_shops;
	}

	/**
	 * Fetches and returns all the data for the google map markers
	 *
     * @param void
	 * @return Kega_Store_Model_Store
	 */
	function retrieveStoreMarkers()
	{
		$stores = $this->getStoreCollection();
		foreach($stores as $store) {
			$this->pushMarker($this->buildMarkerData($store));
		}
		return $this->getMarkers();
	}
}