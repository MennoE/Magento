<?php

class Kega_Vacancytype_Model_Vacancytype extends Mage_Core_Model_Abstract
{
    protected $_resource;
    protected $_read;
    protected $_name = 'vacancytype';

    protected $storeId;
    protected $isNew = true;

    public function _construct()
    {
        parent::_construct();
        $this->_init('vacancytype/vacancytype');

        $this->storeId = Mage_Core_Model_App::ADMIN_STORE_ID;

        $this->_resource = Mage::getSingleton('core/resource');
        $this->_read = $this->_resource->getConnection('core_read');
    }

	public function getStoreId()
    {
    	return $this->storeId;
    }

	/**
     * Save store_view fields if different store view and 
     * Prevent saving store_view fields in the main table if different store view - existent object
     *
     * @return Kega_Vacancytype_Model_Vacancytype
     */
    protected function _beforeSave()
    {
    	parent::_beforeSave();

    	if ($this->getId()) {
    		$this->isNew = false;
    	}

        if ($this->storeId != Mage_Core_Model_App::ADMIN_STORE_ID && $this->getId()) {
        	$this->_dataSaveAllowed = false;
        	$this->_getResource()->saveStoreViewData($this);
        	$this->_getResource()->saveGlobalData($this);
        }
        return $this;
    }

    /**
     * Save store_view fields if different store view - new object
     *
     * @return Kega_Vacancytype_Model_Vacancytype
     */
	protected function _afterSave()
	{
    	parent::_beforeSave();

        if ($this->storeId != Mage_Core_Model_App::ADMIN_STORE_ID && $this->isNew) {
        	$this->_getResource()->saveStoreViewData($this);
        	$this->_getResource()->saveGlobalData($this);
        }
        return $this;
    }

	public function setStoreId($storeId)
    {
    	$this->storeId = $storeId;

    	return $this;
    }

    /**
     * Get vacancyTypes with vacancies
     *
     * @param int $regionFilterId Region to return vacancies for
     * @return array
     */
    public function getVacancytypesWithVacancies($regionFilterId = null)
    {
    	$collection = $this->getCollection()->setStoreId($this->getStoreId())
                                            ->addStoreView()
                                            ->addFieldToFilter('status', 1);

    	foreach ($collection as $item) {
    		$vacancies = $this->getVacanciesByVacancytypeId($item->getId(), $regionFilterId);
    		$item->vacancies = $vacancies;

    		$collection->item = $item;
    	}

    	return $collection;
    }

    /**
     * Get vacancies by given vacancy type
     * 
     * @param int $id The id of the vacancy type
     * @return array
     */
	public function getVacanciesByVacancytypeId($id, $regionFilterId = null)
	{
		$vacancies = Mage::getModel('vacancy/vacancy')->getCollection()
													->addFieldToFilter('status', 1)
													->addFieldToFilter('vacancytype_id', $id);
													
		if ($regionFilterId) {
			$vacancies->addFieldToFilter('vacancyregion_id', $regionFilterId);			
		}
													
		foreach ($vacancies as $vacancy) {
			$store = Mage::getModel('store/store')->load($vacancy->getShopId());
			$vacancy->store = $store;

			$vacancies->vacancy = $vacancy;
		}

		return $vacancies;
	}

    /**
     * Get Vacancies for given vacancy type
     *
     * @param int $typeId Id of the vacancytype to return vacancies for
     * @param int $regionFilterId Id of the region to return vacancies for
     * @return Array
     */
    public function getVacanciesForType($typeId, $regionFilterId = null)
    {
        $select = $this->_read->select();
        $select->from('vacancy', '*')
               ->where('vacancytype_id = ?', $typeId)
               ->where('status = 1');

        if($regionFilterId) {
            $select->where('vacancyregion_id = ?', $regionFilterId);
        }

        $vacancies = $this->_read->fetchAll($select);
        foreach($vacancies as &$vacancy) {
            $vacancy['store'] = Mage::getModel('store/store')->load($vacancy['store_id']);
        }
        usort($vacancies, array('Kega_Vacancytype_Model_Vacancytype', 'vacancySort'));

        return $vacancies;
    }

    public static function vacancySort($a, $b)
    {
        return strcmp($a['store']['city'], $b['store']['city']);
    }

	public static function getStoreViewColumns()
    {
    	return array(
    		'title',
    		'status',
    		'text',
    		'vacancy_form_type',
    		'meta_keywords',
    		'meta_description'
    	);
    }
    
	public static function getGlobalColumns()
    {
    	return array(
    	);
    }
}
