<?php

class Kega_Vacancytype_Model_Mysql4_Vacancytype_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
	protected $storeId;

    public function _construct()
    {
        parent::_construct();
        $this->_init('vacancytype/vacancytype');
    }
    
	public function setStoreId($storeId)
    {
    	$this->storeId = $storeId;
    	return $this;
    }
    
	public function getStoreId()
    {
    	return $this->storeId;
    }
    
    public function addStoreView()
    {
    	if ($this->getStoreId() == Mage_Catalog_Model_Product::DEFAULT_STORE_ID) return $this;
        
        $vacancytypeStoreViewTable = $this->getTable('vacancytype/vacancytype_store_view');
        
        
        $storeViewColumns = Kega_Vacancytype_Model_Vacancytype::getStoreViewColumns();
        
        
        $joinCondition = 'main_table.vacancytype_id = vacancytype_store_view.vacancytype_id               
                AND vacancytype_store_view.store_id='.(int) $this->getStoreId();
        
        $selectColumns = array();
        
        foreach ($storeViewColumns as $columnName) {
        	$selectColumns['store_'.$columnName] = $columnName;
        	$selectColumns[$columnName] = new Zend_Db_Expr('IFNULL(vacancytype_store_view.'.$columnName.', main_table.'.$columnName.')');
        }
        
        $this->getSelect()->joinLeft(
                    array('vacancytype_store_view' => $vacancytypeStoreViewTable),
                    $joinCondition,
                    $selectColumns
                );
                
        //echo $this->getSelect(); 
        
        return $this;
    }
    
    public function addFieldToFilter($field, $condition=null)
    {
    	if ($this->getStoreId() == Mage_Catalog_Model_Product::DEFAULT_STORE_ID) {
    		parent::addFieldToFilter($field, $condition);
    	} else {
    		$storeViewColumns = Kega_Vacancytype_Model_Vacancytype::getStoreViewColumns();
    		if (in_array($field, $storeViewColumns)) {
    			$field = 'IFNULL(vacancytype_store_view.'.$field.', main_table.'.$field.')';
    			$conditionSql = $this->_getConditionSql($field, $condition);
    			$this->getSelect()->where($conditionSql);
    		} else {
    			parent::addFieldToFilter($field, $condition);
    		}
    	}
    	return $this;
    }
}