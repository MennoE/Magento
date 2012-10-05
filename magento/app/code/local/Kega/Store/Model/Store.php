<?php
class Kega_Store_Model_Store extends Mage_Core_Model_Abstract
{

	protected $_attributes;

	public function _construct()
	{
		parent::_construct();
		$this->_init('store/store');
	}


	/**
	 * Get url for store
	 *
	 * @param void
	 * @return String
	 */
	public function getUrl()
	{
		return Mage::getUrl('winkels') . $this->getCustomUrl() . '/';
	}

	public function getIdentifier(){

		return sprintf("%03d", $this->getId());

	}

    public function getAllOpeningDays()
    {
        $read = Mage::getSingleton('core/resource')->getConnection('core_read');

        $select = $read->select()
                        ->from('extraopening')
						->where('status = ?', 'OPEN')
                        ->order('datetime_from');
        $openings = $read->fetchAll($select);

        foreach ($openings as &$opening) {
            $opening['stores'] = $this->getStoresForOpening($opening['extraopening_id']);
        }
        return $openings;
    }

    public function getStoresForOpening($openingId)
    {
        $read = Mage::getSingleton('core/resource')->getConnection('core_read');

        $select = $read->select();
        $select->from('extraopening_store', 'store_id')
               ->where('extraopening_id = ?', $openingId);
        $storeIds = $read->fetchCol($select);
        $stores = array();

        foreach($storeIds as $id) {
			$store = Mage::getModel('store/store')->load($id);
			if ($store->getIsActive() && (in_array(Mage::app()->getStore()->getStoreId(), $store->getStoreviewIds())
				|| in_array(0, $store->getStoreviewIds()))) {
				$stores[] = $store;
			}
        }
        usort($stores, array('Kega_Store_Helper_Data', 'storeSort'));
        return $stores;
    }


	public function getByCodeByGoogle($latitude, $longitude)
	{

		$radius = Mage::helper('store')->getGoogleSearchRadius();//Mage::app()->getStore()->getConfig(self::XML_PATH_STORE_SEARCH_RADIUS);

		$stores_number = Mage::helper('store')->getGoogleStoreCount();//Mage::app()->getStore()->getConfig(self::XML_PATH_STORE_COUNT);

		return $this->_getResource()->getStoresByGoogleCode($this,$latitude, $longitude, $radius, $stores_number);

	}


	public function getStoreCollection($storeId, $active = true){

		$collection = parent::getCollection();
		$collection
			->joinAttribute('description', 'store/description', 'entity_id', null, 'inner', $storeId)
			->joinAttribute('name', 'store/name', 'entity_id', null, 'inner', $storeId)
			->joinAttribute('address', 'store/address', 'entity_id', null, 'inner', $storeId)
			->joinAttribute('city', 'store/city', 'entity_id', null, 'inner', $storeId)
			->joinAttribute('is_active', 'store/is_active', 'entity_id', null, 'inner', $storeId)
			->addAttributeToSelect('name_internal')
			->addAttributeToSelect('hours')
			->addAttributeToSelect('zipcode')
			->addAttributeToSelect('lat')
			->addAttributeToSelect('lng')
			->addAttributeToSelect('phone')
			->addAttributeToSelect('email')
			->addAttributeToSelect('custom_url')
			->addAttributeToSelect('manager_name')
			->addAttributeToSelect('store_image')
			->addAttributeToSelect('manager_image');

		if ($active)
			$collection->addAttributeToFilter('is_active', 1);

		return $collection;
	}


	public function loadOpeningData()
    {
        $this->_getResource()->loadOpeningData($this);
    }

    public function getStoreviewIds()
	{
		$ids = $this->_getData('storeview_ids');

		if (is_null($ids))
		{
			$this->loadStoreviewIds();

			$ids = $this->getData('storeview_ids');
		}

		return $ids;
	}


	public function loadStoreviewIds()
	{
		$this->_getResource()->loadStoreviewIds($this);
	}


    public function getOpeningData(){//!!!

    	if (is_null($this->getId()))
    		return;

    	$write = Mage::getSingleton('core/resource')->getConnection('core_write');

		$readresult=$write->query("SELECT so.mondayopen1, so.mondayclose1, so.mondayopen2, so.mondayclose2,
						so.tuesdayopen1, so.tuesdayclose1, so.tuesdayopen2, so.tuesdayclose2,
                		so.wednesdayopen1, so.wednesdayclose1, so.wednesdayopen2, so.wednesdayclose2,
                		so.thursdayopen1, so.thursdayclose1, so.thursdayopen2, so.thursdayclose2,
                		so.fridayopen1, so.fridayclose1, so.fridayopen2, so.fridayclose2,
                		so.saturdayopen1, so.saturdayclose1, so.saturdayopen2, so.saturdayclose2,
                		so.sundayopen1, so.sundayclose1, so.sundayopen2, so.sundayclose2
			FROM store_opening as so
			WHERE so.store_id = ".$this->getId());

		foreach ($readresult as $data){
			foreach ($data as $f_name => $f_value)
			$this->__set($f_name, $f_value);
		}
		return $this;
    }


	public function getActiveVacancy(){//!!!

		$write = Mage::getSingleton('core/resource')->getConnection('core_write');

		// now $write is an instance of Zend_Db_Adapter_Abstract
		$readresult=$write->query("SELECT vacancytype.*, vacancy.vacancy_id AS vacancy_id
			FROM vacancy AS vacancy
			INNER JOIN vacancytype AS vacancytype
				ON vacancy.vacancytype_id = vacancytype.vacancytype_id
			WHERE vacancy.status = 1 AND vacancy.store_id = ".$this->getId());


		return $readresult;
	}

	public function getExtraopenings()
	{
		$parsedData = array(
			'OPEN' => array(),
			'CLOSED' => array(),
		);

		$rawData = $this->_getResource()->getExtraopenings($this);
		$locale = Mage::getStoreConfig('general/locale/code');

		setlocale(LC_TIME, $locale . '.utf8', $locale);

		foreach($rawData as $opening) {
			$parsedData[$opening['status']][$opening['extraopening_id']]['title'] = $opening['title'];
			$parsedData[$opening['status']][$opening['extraopening_id']]['datetime_from'] = $opening['datetime_from'];
			$parsedData[$opening['status']][$opening['extraopening_id']]['datetime_from_parsed'] =
				strftime("%A %d %B", strtotime($opening['datetime_from']));
			$parsedData[$opening['status']][$opening['extraopening_id']]['datetime_to'] = $opening['datetime_to'];
			$parsedData[$opening['status']][$opening['extraopening_id']]['datetime_to_parsed'] =
				strftime("%A %d %B", strtotime($opening['datetime_to']));
		}
		return $parsedData;
	}


	public function getExtraopeningIds()
	{
		return $this->_getResource()->getExtraopeningIds($this);
	}


	public function getAttributes()
    {
//        if (null === $this->_attributes) {
//            $this->_attributes = $this->_getResource()
//            ->loadAllAttributes($this)
//            ->getSortedAttributes();
//        }
        return $this->getEditableAttributes();//$this->_attributes;
    }


//    public function getAttributes($groupId = null, $skipSuper=false)
//    {
//        $productAttributes = $this->getTypeInstance()->getEditableAttributes();
//        if ($groupId) {
//            $attributes = array();
//            foreach ($productAttributes as $attribute) {
//                if ($attribute->isInGroup($this->getAttributeSetId(), $groupId)) {
//                    $attributes[] = $attribute;
//                }
//            }
//        }
//        else {
//            $attributes = $productAttributes;
//        }
//
//        return $attributes;
//    }

    protected $_setAttributes;//!!!
    protected $_editableAttributes;//!!!

    public function getSetAttributes()
    {
        if (is_null($this->_setAttributes)) {
            $attributes = $this->getResource()
                ->loadAllAttributes($this)
                ->getSortedAttributes();
            $this->_setAttributes = array();
            foreach ($attributes as $attribute) {
                    $attribute->setDataObject($this);
                    $this->_setAttributes[$attribute->getAttributeCode()] = $attribute;
            }
            //uasort($this->_setAttributes, array($this, 'attributesCompare'));
        }
        return $this->_setAttributes;
    }

    public function getEditableAttributes()
    {
        if (is_null($this->_editableAttributes)) {
            $this->_editableAttributes = array();
            foreach ($this->getSetAttributes() as $attributeCode => $attribute) {
                    $this->_editableAttributes[$attributeCode] = $attribute;
            }
        }
        return $this->_editableAttributes;
    }



    public function getAttributeText($attributeCode)
    {
        return $this->getResource()
            ->getAttribute($attributeCode)
                ->getSource()
                    ->getOptionText($this->getData($attributeCode));
    }


        /**
     * Identifuer of default store
     * used for loading default data for entity
     */
    const DEFAULT_STORE_ID = 0;

    /**
     * Attribute default values
     *
     * This array contain default values for attributes which was redefine
     * value for store
     *
     * @var array
     */
    protected $_defaultValues = array();

        /**
     * Adding attribute code and value to default value registry
     *
     * Default value existing is flag for using store value in data
     *
     * @param   string $attributeCode
     * @return  Mage_Catalog_Model_Abstract
     */
    public function setAttributeDefaultValue($attributeCode, $value)
    {
        $this->_defaultValues[$attributeCode] = $value;
        return $this;
    }

    /**
     * Retrieve default value for attribute code
     *
     * @param   string $attributeCode
     * @return  mixed
     */
    public function getAttributeDefaultValue($attributeCode)
    {
        return isset($this->_defaultValues[$attributeCode]) ? $this->_defaultValues[$attributeCode] : null;
    }


    /**
     * Get current day store route number
     * @param array $routes -> key - mondayroute/tuesdayroute/..., value => route number
     * @return string route number
     */
    public function getTodayRoute($routes = array())
    {
        $hourLimit = 18;//6pm

        $tz_string = 'Europe/Amsterdam';
        $tz_object = new DateTimeZone($tz_string);

        $datetime = new Datetime('now',$tz_object);
        $currentHour = (int) $datetime->format('H');
        $currentDay = $datetime->format('l');

        if (empty($routes)) {
            $routes = $this->getStoreRoutesData();
        }

        switch ($currentDay) {
            case 'Monday':
                if ($currentHour < $hourLimit) {
                    return $routes['mondayroute'];
                } else {
                    return $routes['tuesdayroute'];
                }
            break;
            case 'Tuesday':
                if ($currentHour < $hourLimit) {
                    return $routes['tuesdayroute'];
                } else {
                    return $routes['wednesdayroute'];
                }
            break;
            case 'Wednesday':
                if ($currentHour < $hourLimit) {
                    return $routes['wednesdayroute'];
                } else {
                    return $routes['thursdayroute'];
                }
            break;
            case 'Thursday':
                if ($currentHour < $hourLimit) {
                    return $routes['thursdayroute'];
                } else {
                    return $routes['fridayroute'];
                }
            break;
            case 'Friday':
                if ($currentHour < $hourLimit) {
                    return $routes['fridayroute'];
                } else {
                    return $routes['saturdayroute'];
                }
            break;
            case 'Saturday':
                if ($currentHour < $hourLimit) {
                    return $routes['saturdayroute'];
                } else {
                    return $routes['sundayroute'];
                }
            break;
            case 'Sunday':
                if ($currentHour < $hourLimit) {
                    return $routes['sundayroute'];
                } else {
                    return $routes['mondayroute'];
                }
            break;
            default:
                Mage::throwException(sprintf("invalid day name %s", $currentDay));
        }
    }

	/**
	 * Load store data by store number
	 *
	 * @param string $storeNumber
	 * @return Kega_Store_Model_Store
	 */
	public function loadStoreByStoreNumber($storeNumber)
	{
		$stores_collection = Mage::getModel('store/store')->getCollection()
					->addAttributeToFilter('storenummer', $storeNumber)
					->load();
		if ($stores_collection->getSize() >= 1) {
			$storeModel =  $stores_collection->getFirstItem();

			$this->load($storeModel->getId());
		}
		return $this;
	}
}