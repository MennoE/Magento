<?php

class Kega_Extraopening_Model_Mysql4_Extraopening extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        // Note that the extraopening_id refers to the key field in your database table.
        $this->_init('extraopening/extraopening', 'extraopening_id');
    }


    public function loadStoreIds(Kega_Extraopening_Model_Extraopening $object)
    {
        $extraopeningId   = $object->getId();
        $storeIds = array();
        if ($extraopeningId) {
            $select = $this->_getReadAdapter()->select()
                ->from($this->getTable('extraopening/extraopening_store'), 'store_id')
                ->where('extraopening_id = ?', $extraopeningId);
            $storeIds = $this->_getReadAdapter()->fetchCol($select);
        }
        $object->setStoreIds($storeIds);
    }

    public function _afterSave(Mage_Core_Model_Abstract $object)
    {
        /** stores */
        $deleteWhere = $this->_getWriteAdapter()->quoteInto('extraopening_id = ?', $object->getId());
        $this->_getWriteAdapter()->delete($this->getTable('extraopening/extraopening_store'), $deleteWhere);

        foreach ($object->getStoreIds() as $storeId) {
            $extraopeningStoreData = array(
            'extraopening_id'   => $object->getId(),
            'store_id'  => $storeId
            );
            $this->_getWriteAdapter()->insert($this->getTable('extraopening/extraopening_store'), $extraopeningStoreData);
        }
    }
}