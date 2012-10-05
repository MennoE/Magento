<?php

class Kega_Vacancy_Model_Vacancy extends Mage_Core_Model_Abstract
{
	private $_storeInstance;
	private $_storeCollection;
	private $_vacancytypeInstance;

	protected $_resource;
    protected $_read;
    protected $_name = 'vacancy';

	protected $storeId;
    protected $isNew = true;

    public function _construct()
    {
        parent::_construct();
        $this->_init('vacancy/vacancy');

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
     * @return Kega_Faq_Model_Category
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
     * @return Kega_Faq_Model_Category
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
     * Return details of Vacancy
     *
     * @param Int $vacancyId
     * @return Array
     */
    public function getDetails($vacancyId)
    {
        if($vacancyId == 0) {
            return array('vacancy_id' => 0,
                         'title' => 'Open solicitatie',
                         'vacancy_form_type' => 3,
                         'store' => 0,
                         'name_full' => 'Open solicitiatie');
        }

	    $select = $this->_read->select();
        $select->from(array('v' => $this->_name),
                      array('vacancy_id', 'comments' => 'title', 'shop_id', 'number', 'hours', 'status'))
               ->joinLeft(array('vt' => 'vacancytype'),
                      'v.vacancytype_id = vt.vacancytype_id',
                      array('type_id' => 'vacancytype_id', 'title', 'text', 'meta_keywords', 'meta_description', 'vacancy_form_type'))
               ->joinLeft(array('vr' => 'vacancyregion'),
                      'v.vacancyregion_id = vr.vacancyregion_id',
                      array('region_id' => 'vacancyregion_id', 'region' => 'title'))
               ->where('vacancy_id = ?', $vacancyId);
        $vacancy = $this->_read->fetchRow($select);

		$vacancy['store'] = Mage::getModel('store/store')->load($vacancy['shop_id']);

        $vacancy['name_full'] = $vacancy['comments'] . ' ' .
                                $vacancy['title'] .
                                ' (' . $vacancy['store']->address . ', ' . $vacancy['store']->city . ') ';
        return $vacancy;
    }

	public static function getStoreViewColumns()
    {
    	return array(
    		'title',
			'number',
    		'vacancytype_id',
    		'shop_id',
    		'vacancyregion_id',
    		'status',
    	);
    }

	public static function getGlobalColumns()
    {
    	return array(
    		'store_id',
    	);
    }
}