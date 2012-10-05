<?php

class Kega_Vacancy_Model_Mysql4_Vacancy extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the vacancy_id refers to the key field in your database table.
        $this->_init('vacancy/vacancy', 'vacancy_id');
    }

	/**
     * overrided to load the store view data
     */
    protected function _afterLoad(Mage_Core_Model_Abstract $object)
    {
    	$this->_loadStoreView($object);

	    $select = $this->_getReadAdapter()->select()
	        ->from($this->getTable('vacancy/vacancy_store'))
	        ->where('vacancy_id = ?', $object->getId());

	    if ($data = $this->_getReadAdapter()->fetchAll($select)) {
	        $storesArray = array();
	        foreach ($data as $row) {
	            $storesArray[] = $row['store_id'];
	        }
	        $object->setData('store_id', $storesArray);
	    }

        return parent::_afterLoad($object);
    }
    
    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
    	$condition = $this->_getWriteAdapter()->quoteInto('vacancy_id = ?', $object->getId());
	    $this->_getWriteAdapter()->delete($this->getTable('vacancy/vacancy_store'), $condition);

	    foreach ((array)$object->getData('stores') as $store) {
	        $storeArray = array();
	        $storeArray['vacancy_id'] = $object->getId();
	        $storeArray['store_id'] = $store;
	        $this->_getWriteAdapter()->insert($this->getTable('vacancy/vacancy_store'), $storeArray);
	    }

	    return parent::_afterSave($object);
    }

    protected function _loadStoreView($object)
    {
    	$storeViewData = $this->getStoreViewData($object);

    	$useDefault = array();

    	if ($storeViewData) {
    		// set store view values
    		foreach ($storeViewData as $k=>$v) {
    			if (is_null($v)) {
    				$useDefault[] = $k;
    			} else {
    				$object->setData($k, $v);
    			}
    		}
    	} else {
    		$useDefault = array_keys($object->getData());
    	}

    	if (!empty($useDefault)) {
    		$object->setData('use_default', $useDefault);
    	}
    }

    protected function getStoreViewData($object)
    {
    	$vacancyStoreViewTable = $this->getTable('vacancy/vacancy_store_view');

        // get previous values
        $select = $this->_getReadAdapter()->select()
                        ->from($vacancyStoreViewTable, array('*'))
                        ->where('vacancy_id = ?', $object->getVacancyId())
                        ->where('store_id = ?', $object->getStoreId())
                        ;
        $result = $select->query();

        $row = $result->fetch(Zend_Db::FETCH_ASSOC);

        return $row;  
    }

	public function saveStoreViewData($object)
    {
    	if ($object->getStoreId() == Mage_Core_Model_App::ADMIN_STORE_ID) return;

    	$previousData = $this->getStoreViewData($object);

    	$vacancyStoreViewTable = $this->getTable('vacancy/vacancy_store_view');

    	$storeViewColumns = $object->getStoreViewColumns();

    	if (!empty($previousData)) {

    		foreach ($storeViewColumns as $property) {
    			if (is_array($object->getData('use_default')) && in_array($property, $object->getData('use_default'))) {
    				$recordData[$property] = null;
    			} else {
    				$recordData[$property] = $object->getData($property);
    			}
    		}
            $this->_getWriteAdapter()->update($vacancyStoreViewTable, $recordData, 
            					array('vacancy_id = ' . $object->getId(),
            						  'store_id = ' . $object->getStoreId())
            					);
        } else {
        	$recordData['vacancy_id'] = $object->getId();
        	$recordData['store_id'] = $object->getStoreId();
        	foreach ($storeViewColumns as $property) {
    			if (is_array($object->getData('use_default')) && in_array($property, $object->getData('use_default'))) {
    				$recordData[$property] = null;
    			} else {
    				$recordData[$property] = $object->getData($property);
    			}
    		}
            $this->_getWriteAdapter()->insert($vacancyStoreViewTable, $recordData);
        }
    }

	public function saveGlobalData($object)
    {
    	if ($object->getStoreId() == Mage_Core_Model_App::ADMIN_STORE_ID) return;

    	$vacancyTable = $this->getTable('vacancy/vacancy');

    	if (!$object->getId()) return;

    	$globalColumns = $object->getGlobalColumns();

    	$recordData = array();

    	foreach ($globalColumns as $property) {
    		$recordData[$property] = $object->getData($property);
    	}

    	unset($recordData['store_id']);

    	if (count($recordData) == 0 ) {
    		return false;
    	}
        $this->_getWriteAdapter()->update($vacancyTable, $recordData,
            					array('vacancy_id' => $object->getId())
            					);
    }
}