<?php

class Kega_Store_IndexController extends Mage_Core_Controller_Front_Action
{

	private $session;

	const XML_GOOGLE_MAP_CENTER_LON = 'store/google_map/store_center_lon';
	const XML_GOOGLE_MAP_CENTER_LAT = 'store/google_map/store_center_lat';
	const XML_GOOGLE_MAP_CENTER_ZOOM = 'store/google_map/store_center_zoom';

	/**
	 * Kega_Store_IndexController->indexAction()
	 * Handles all search request and geo-parsing for index- and searchpage
	 *
     * @param $location string
	 * @return array
	 */
	public function indexAction()
	{
		$criteria = $this->getRequest()->getParam("criteria");
		if ($criteria) {
			$criteria = Mage::helper('store')->getRelatedCountryCode(htmlentities($criteria));
			$content = $this->getGeoLocation($criteria);

			$this->setDefaultGeoData(array(
				'long' => $content[2],
				'lat' => $content[3],
				'zoom' => 11,
				'status' => 200
			));
		}
		else {
			$this->setDefaultGeodata();
		}

        $this->loadLayout();

        if (Mage::getStoreConfig('store/seo_general_store_page/meta_title')) {
            $metaTitle = Mage::getStoreConfig('store/seo_general_store_page/meta_title');
            $this->getLayout()->getBlock('head')->setTitle($metaTitle);
        } else {
            $this->getLayout()->getBlock('head')->setTitle($this->__('Store'));
        }

        if (Mage::getStoreConfig('store/seo_general_store_page/meta_description')) {
            $metaDescription = Mage::getStoreConfig('store/seo_general_store_page/meta_description');
            $this->getLayout()->getBlock('head')->setDescription($metaDescription);
        }

        $this->renderLayout();
	}

    /**
     * Kega_Store_OpeningsController->indexAction()
     * Show extra openings
     *
     * @param void
     * @return void
     **/
    public function openingsAction()
    {
        /**
         * Set locale
         */
        setlocale(LC_TIME, 'nl_NL');

        $this->loadLayout();
        $this->getLayout()->getBlock('head')->setTitle('Extra openingstijden');
        $this->renderLayout();
    }

	/**
	 * Kega_Store_IndexController->getGeoLocation()
	 * Returns geodata for given location string
	 *
     * @param $location string
	 * @return array
	 */
	public function getGeoLocation($criteria)
	{
		$countryCriteria = Mage::app()->getStore()->getConfig('store/google_map/store_country_criteria');
		if($countryCriteria != null){
			$countryCriteria= ','.$countryCriteria;
		}
		$url = sprintf(
			"http://maps.google.com/maps/geo?q=%s&output=csv&oe=utf8&sensor=true&key=%s",
			urlencode($criteria.$countryCriteria),
			'ABQIAAAAP8cM0a5GerqBsj_3zIk-NBTb_3-TxUy1LBXWwG4HHxwJVbRkYRTfvOiGUncAvg3Dtbxd_cKUoOMO_w'
		);
		$content = @file_get_contents($url);
		return explode(",", $content);
	}

	/**
	 * Kega_Store_IndexController->getDefaultGeodata()
	 * Loads the default geodata for our map into the register
	 *
     * @param void
	 * @return void
	 */
	public function setDefaultGeodata($geodata = false)
	{
		if (!isset($geodata['long']) || !isset($geodata['lat']) || !isset($geodata['zoom'])) {
			$geodata = false;
		}

		if (!$geodata) {
			$geodata = array(
				'long' => Mage::getStoreConfig(self::XML_GOOGLE_MAP_CENTER_LAT),
				'lat' => Mage::getStoreConfig(self::XML_GOOGLE_MAP_CENTER_LON),
				'zoom' => Mage::getStoreConfig(self::XML_GOOGLE_MAP_CENTER_ZOOM),
				'status' => ''
			);
		}
		Mage::register('geodata', $geodata);
	}
}