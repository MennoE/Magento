<?php

class Kega_Faq_Model_Mysql4_Question_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
	protected $storeId;
	
    public function _construct()
    {
        parent::_construct();
        $this->_init('faq/question');
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

	public function addFieldToFilter($field, $condition=null)
	{
		parent::addFieldToFilter($field, $condition);
		return $this;
	}

    /**
	 * Add Filter by store
	 */
	public function addStoreFilter($storeId)
	{
	    $this->getSelect()->join(
	        array('faq_table' => $this->getTable('faq/category_store')),
	        'main_table.category_id = faq_table.category_id',
	        array()
	    )
	    ->where('faq_table.store_id in (?)', $storeId)
	    ->orWhere('faq_table.store_id in (?)', 0);

	    return $this;
	}
}