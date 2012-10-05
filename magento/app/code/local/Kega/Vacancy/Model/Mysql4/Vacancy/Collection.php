<?php

class Kega_Vacancy_Model_Mysql4_Vacancy_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
	protected $storeId;

    public function _construct()
    {
        parent::_construct();
        $this->_init('vacancy/vacancy');
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

        $vacancyStoreViewTable = $this->getTable('vacancy/vacancy_store_view');

        $storeViewColumns = Kega_Vacancy_Model_Vacancy::getStoreViewColumns();

        $joinCondition = 'main_table.vacancy_id = vacancy_store_view.vacancy_id               
                AND vacancy_store_view.shop_id='.(int) $this->getStoreId();

        $selectColumns = array();

        foreach ($storeViewColumns as $columnName) {
        	$selectColumns['store_'.$columnName] = $columnName;
        	$selectColumns[$columnName] = new Zend_Db_Expr('IFNULL(vacancy_store_view.'.$columnName.', main_table.'.$columnName.')');
        }

        $this->getSelect()->joinLeft(
                    array('vacancy_store_view' => $vacancyStoreViewTable),
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
    		$storeViewColumns = Kega_Vacancy_Model_Vacancy::getStoreViewColumns();
    		if (in_array($field, $storeViewColumns)) {
    			$field = 'IFNULL(vacancy_store_view.'.$field.', main_table.'.$field.')';
    			$conditionSql = $this->_getConditionSql($field, $condition);
    			$this->getSelect()->where($conditionSql);
    		} else {
    			parent::addFieldToFilter($field, $condition);
    		}
    	}
    	return $this;
    }

    /**
	 * Add Filter by store
	 */
	public function addStoreFilter($storeId, $withAdmin = true)
	{
	    $this->getSelect()->join(
	        array('store_table' => $this->getTable('vacancy/vacancy_store')),
	        'main_table.vacancy_id = store_table.vacancy_id',
	        array()
	    )
	    ->where('store_table.store_id in (?)', ($withAdmin ? array(0, $storeId) : $storeId))
	    ->group('main_table.vacancy_id');

	    return $this;
	}
}