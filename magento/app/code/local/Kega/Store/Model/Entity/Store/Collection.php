<?php
class Kega_Store_Model_Entity_Store_Collection extends Mage_Eav_Model_Entity_Collection_Abstract
{
	protected $_storeId = null;
	
	
	protected function _construct()
	{
		$this->_init('store/store');
	}

    public function setStore($store)
    {
        $this->setStoreId(Mage::app()->getStore($store)->getId());
        return $this;
    }

    public function setStoreId($storeId)
    {
        if ($storeId instanceof Mage_Core_Model_Store) {
            $storeId = $storeId->getId();
        }
        $this->_storeId = $storeId;
        return $this;
    }

    public function getStoreId()
    {
        if (is_null($this->_storeId)) {
            $this->setStoreId(Mage::app()->getStore()->getId());
        }
        return $this->_storeId;
    }

    public function getDefaultStoreId()
    {
        return Kega_Store_Model_Store::DEFAULT_STORE_ID;
    }
	
//!!!	public function addExtraopeningToSelect()
//	{
//		
//		$fields = array();
//		$this->addExpressionAttributeToSelect('name', '', array('name'=>'name'));
//		return $this;
//	}

	
	
	   /**
     * Retrieve attributes load select
     *
     * @param   string $table
     * @return  Mage_Eav_Model_Entity_Collection_Abstract
     */
    protected function _getLoadAttributesSelect($table)
    {
        if ((int) $this->getStoreId()) {
            $entityIdField = $this->getEntity()->getEntityIdField();
            $joinCondition = 'store.attribute_id=default.attribute_id
                AND store.entity_id=default.entity_id
                AND store.store_id='.(int) $this->getStoreId();

            $select = $this->getConnection()->select()
                ->from(array('default'=>$table), array($entityIdField, 'attribute_id', 'default_value'=>'value'))
                ->joinLeft(
                    array('store'=>$table),
                    $joinCondition,
                    array(
                        'store_value' => 'value',
                        'value' => new Zend_Db_Expr('IFNULL(store.value, default.value)')
                    )
                )
                ->where('default.entity_type_id=?', $this->getEntity()->getTypeId())
                ->where("default.$entityIdField in (?)", array_keys($this->_itemsById))
                ->where('default.attribute_id in (?)', $this->_selectAttributes)
                ->where('default.store_id = 0');
        }
        else {
            $select = parent::_getLoadAttributesSelect($table)
                ->where('store_id=?', $this->getDefaultStoreId());
        }
        return $select;
    }
	
}