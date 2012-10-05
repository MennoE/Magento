<?php

class Kega_Faq_Model_Mysql4_Question extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('faq/question', 'question_id');
    }
    
	public function saveGlobalData($object)
    {
    	if ($object->getStoreId() == Mage_Core_Model_App::ADMIN_STORE_ID) return;
    	
    	$questionTable = $this->getTable('faq/question');
    	
    	if (!$object->getId()) return;
    	
    	$columns = $object->getColumns();
    	
    	$recordData = array();
    	
    	foreach ($columns as $property) {
    		$recordData[$property] = $object->getData($property);
    	}
    	if (count($recordData) == 0 ) {
    		return false;
    	}
        $this->_getWriteAdapter()->update($questionTable, $recordData, 
            					array('question_id' => $object->getId())            					
            					);
    }
}