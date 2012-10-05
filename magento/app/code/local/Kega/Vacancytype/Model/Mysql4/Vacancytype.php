<?php

class Kega_Vacancytype_Model_Mysql4_Vacancytype extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        // Note that the vacancytype_id refers to the key field in your database table.
        $this->_init('vacancytype/vacancytype', 'vacancytype_id');
    }
    
	/**
     * overrided to load the store view data
     */
    protected function _afterLoad(Mage_Core_Model_Abstract $object)
    {	
    	$this->_loadStoreView($object);

        return parent::_afterLoad($object);
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
    	$vacancytypeStoreViewTable = $this->getTable('vacancytype/vacancytype_store_view');

        // get previous values
        $select = $this->_getReadAdapter()->select()
                        ->from($vacancytypeStoreViewTable, array('*'))
                        ->where('vacancytype_id = ?', $object->getVacancytypeId())
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
    	
    	$vacancytypeStoreViewTable = $this->getTable('vacancytype/vacancytype_store_view');
    	
    	$storeViewColumns = $object->getStoreViewColumns();
    	
    	
    	if (!empty($previousData)) {

    		foreach ($storeViewColumns as $property) {
    			if (is_array($object->getData('use_default')) && in_array($property, $object->getData('use_default'))) {
    				$recordData[$property] = null;
    			} else {
    				$recordData[$property] = $object->getData($property);
    			}
    		}            
            $this->_getWriteAdapter()->update($vacancytypeStoreViewTable, $recordData, 
            					array('vacancytype_id = ' . $object->getId(),
            						  'store_id = ' . $object->getStoreId())
            					);
        } else {
        	        	
        	$recordData['vacancytype_id'] = $object->getId();
        	$recordData['store_id'] = $object->getStoreId();
        	foreach ($storeViewColumns as $property) {
    			if (is_array($object->getData('use_default')) && in_array($property, $object->getData('use_default'))) {
    				$recordData[$property] = null;
    			} else {
    				$recordData[$property] = $object->getData($property);
    			}
    		} 
            $this->_getWriteAdapter()->insert($vacancytypeStoreViewTable, $recordData);           
        }
    }
    
	public function saveGlobalData($object)
    {
    	if ($object->getStoreId() == Mage_Core_Model_App::ADMIN_STORE_ID) return;
    	
    	$vacancytypeTable = $this->getTable('vacancytype/vacancytype');
    	
    	if (!$object->getId()) return;
    	
    	$globalColumns = $object->getGlobalColumns();
    	
    	$recordData = array();
    	
    	foreach ($globalColumns as $property) {
    		$recordData[$property] = $object->getData($property);
    	}
    	if (count($recordData) == 0 ) {
    		return false;
    	}
        $this->_getWriteAdapter()->update($vacancytypeTable, $recordData, 
            					array('vacancytype_id' => $object->getId())            					
            					);
    }
}