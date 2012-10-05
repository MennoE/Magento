<?
class Kega_Store_DetailsController extends Mage_Core_Controller_Front_Action
{
	/**
	 * Kega_Store_DetailsController->indexAction()
	 * Loads the default geodata for our map into the register
	 *
     * @param void
	 * @return void
	 */
	public function indexAction()
	{
		$storeFound = $this->fetchStoreDetails();

		// Openings overview
		if ($this->getRequest()->getParam('view') == 'index') {
			$head = $this->getLayout()->createBlock('Kega_Meta_Block_Html_Head');

			$keywords = '';
			if ( Mage::getStoreConfig('store/seo_openings_store_page/meta_keywords') ) {
				$keywords = Mage::getStoreConfig('store/seo_openings_store_page/meta_keywords');
			}

			$metaTitle = '';
			if ( Mage::getStoreConfig('store/seo_openings_store_page/meta_title') ) {
				$metaTitle = Mage::getStoreConfig('store/seo_openings_store_page/meta_title');
			}

			$metaDescription = '';
			if ( Mage::getStoreConfig('store/seo_openings_store_page/meta_description') ) {
				$metaDescription = Mage::getStoreConfig('store/seo_openings_store_page/meta_description');
			}

			$head->setKeywords($keywords);
			$head->overwriteTitle($metaTitle);
            $head->setDescription($metaDescription);

			$storeFound = TRUE;
		}

        if (!$storeFound) {
            $this->_forward('404');
            return;
        }

		// normal way not working due to loadLayoutUpdates
        $update = $this->getLayout()->getUpdate();

        $update->addHandle('default');
        $update->addHandle('store_details_index');

		$this->addActionLayoutHandles();
		$this->loadLayoutUpdates();

		$this->generateLayoutXml()->generateLayoutBlocks();
		
		$data = Mage::registry('geodata');
		$breadcrumbs = $this->getLayout()->getBlock('breadcrumbs');
		$breadcrumbs->addCrumb($data['city'], array('label' => $data['city'], 'title' => $data['city']));
		
        $this->renderLayout();

	}

	/**
	 * Kega_Store_DetailsController->useDefaultDataInRegister()
	 * Loads the default geodata for our map into the register
	 *
     * @param void
	 * @return void
	 */
	public function fetchStoreDetails()
	{
		$stores = $this->getStoreGeoData(
			$this->getRequest()->getParam('view')
		);

        $storeFound = false;
		foreach($stores as $store) {
			$head = $this->getLayout()->createBlock('Kega_Meta_Block_Html_Head');

			$head->setKeywords($store->getName());

            $metaTitle = '';
            if ($store->getMetaTitle()) {
				$metaTitle = $store->getMetaTitle();
			} elseif(Mage::getStoreConfig('store/seo_default_store_page/meta_title')) {
				$metaTitle = Mage::getStoreConfig('store/seo_default_store_page/meta_title');

				//add meta title part
                if (Mage::getStoreConfig('store/seo_default_store_page/meta_title_part')) {
					$metaTitle .= ' ' . Mage::getStoreConfig('store/seo_default_store_page/meta_title_part');
                    $metaTitle = str_replace('{store_city}', $store->getCity(), $metaTitle);
                }
            }

            $metaDescription = '';
            if ($store->getMetaDescription()) {
                $metaDescription = $store->getMetaDescription();
            } elseif (Mage::getStoreConfig('store/seo_default_store_page/meta_description')) {
                $metaDescription = Mage::getStoreConfig('store/seo_default_store_page/meta_description');
				$metaDescription = str_replace('{store_city}', $store->getCity(), $metaDescription);
            }

            $head->overwriteTitle($metaTitle);
            $head->setDescription($metaDescription);

			Mage::register('geodata', array(
				'long' => $store->getLng(),
				'lat' => $store->getLat(),
				'postcode' => $store->getZipcode(),
				'city' => $store->getCity(),
				'zoom' => 11,
				'status' => ''
			));
            $storeFound = true;
		}

        return $storeFound;
	}

	/**
	 * Kega_Store_DetailsController->getStoreGeoData()
	 * Retrieves the geodata for the given store
	 *
     * @param $key string
	 * @return Kega_Store_Model_Store
	 */
	public function getStoreGeoData($key)
	{
		return Mage::getModel('store/store')->getCollection()
			->addAttributeToSelect('lat')
			->addAttributeToSelect('lng')
			->addAttributeToSelect('city')
			->addAttributeToSelect('address')
			->addAttributeToSelect('zipcode')
			->addAttributeToFilter('custom_url', $key);
	}
}