<?php

class Kega_Faq_Model_Mysql4_Category extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('faq/category', 'category_id');
    }
    
    protected function _afterLoad(Mage_Core_Model_Abstract $object)
    {
		$object->setData('store_id', $this->getStoresIdArray($object->getId()));
        return parent::_afterLoad($object);
    }

    public function loadByPermalinkAndStore($object, $permalink, $storeId)
	{
        $collection = Mage::getModel('faq/category')->getCollection()
                        ->addFieldToFilter('permalink', $permalink)
                        ->addStoreFilter(Mage::app()->getStore()->getStoreId());

        if (!$collection->count()) {
             return false;
        }

        return $collection->getFirstItem();
	}
    
    public function getStoresIdArray($categoryId)
    {
		$select = $this->_getReadAdapter()->select()
					   ->from($this->getTable('faq/category_store'))
	        		   ->where('category_id = ?', $categoryId);
		
	    $storesArray = array();
	    if ($data = $this->_getReadAdapter()->fetchAll($select)) {
	        foreach ($data as $row) {
	            $storesArray[] = $row['store_id'];
	        }
	    }
	    return $storesArray;
    }
    
	public function saveGlobalData($object)
    {
    	if ($object->getStoreId() === Mage_Core_Model_App::ADMIN_STORE_ID) return;
    	
    	$categoryTable = $this->getTable('faq/category');
    	
    	if (!$object->getId()) return;
    	$columns = $object->getColumns();
    	$recordData = array();
    	
    	foreach ($columns as $property) {
    		// need this to make sure the overview image and category image are saved correctly
			if ($property == 'category_image' || $property == 'overview_image') {
				if ($object->getData('images') != null) {
					foreach ($object->getData('images') as $imageName => $imageValue) {
						$recordData[$imageName] = $imageValue['value'];
						continue;
					}
				}
    			continue;
    		}
    		
    		$recordData[$property] = $object->getData($property);
    	}
    	if (count($recordData) == 0 ) {
    		return false;
    	}
    	
		$where = $this->_getWriteAdapter()->quoteInto('category_id = ?', $object->getId());
        $this->_getWriteAdapter()->update($categoryTable, $recordData, $where);
    }

	public function saveStoreViewData($object)
    {
        $categoryId = $object->getId();

		$where = $this->_getWriteAdapter()->quoteInto('category_id = ?', $categoryId);
		$this->_getWriteAdapter()->delete($this->getTable('faq/category_store'), $where);

        foreach ((array)$object->getData('stores') as $store) {
			$storeArray = array();
			$storeArray['category_id'] = $categoryId;
			$storeArray['store_id'] = $store;
			$this->_getWriteAdapter()->insert($this->getTable('faq/category_store'), $storeArray);
	   	}
    }
}