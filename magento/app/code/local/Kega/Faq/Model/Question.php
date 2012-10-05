<?php

class Kega_Faq_Model_Question extends Mage_Core_Model_Abstract
{
	protected $storeId;
    
    protected $isNew = true;
    
    public function _construct()
    {
        parent::_construct();
        $this->_init('faq/question');
        
        $this->storeId = Mage_Core_Model_App::ADMIN_STORE_ID;
    }
    
	public function getStoreId()
    {
    	return $this->storeId;
    }
    
	public function setStoreId($storeId)
    {
    	$this->storeId = $storeId;
    	
    	return $this;
    }
    
	public static function getColumns()
    {
    	return array(
    		'question',
    		'answer',
    		'display_order',
    		'category_id',
    	);
    }
}