<?php

class Kega_Extraopening_Model_Mysql4_Extraopening_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('extraopening/extraopening');
    }


     public function addFieldToFilter($field, $condition=null)
    {
        if ($field == 'stores') {
            return $this->addStoresFilter($condition);
        }
        else {
            return parent::addFieldToFilter($field, $condition);
        }
    }

    /**
     * Add Stores Filter
     *
     * @param int $storeId
     * @return Kega_News_Model_Mysql4_News_Collection
     */
    public function addStoresFilter($storeId)
    {
        $this->_select->join(
            array('store' => $this->getTable('extraopening/extraopening_store')),
            'main_table.extraopening_id=store.extraopening_id AND store.store_id=' . (int)$storeId,
            array()
        );
        return $this;
    }

    /**
     * Add stores data
     *
     * @return Kega_News_Model_Mysql4_News_Collection
     */
    public function addStoreData()
    {
        $extraopeningIds = $this->getColumnValues('extraopening_id');
        $storesToNews = array();

        if (count($extraopeningIds) > 0) {
            $select = $this->getConnection()->select()
                ->from($this->getTable('extraopening/extraopening_store'))
                ->where('extraopening_id IN(?)', $extraopeningIds);
            $result = $this->getConnection()->fetchAll($select);

            foreach ($result as $row) {
                if (!isset($storesToNews[$row['extraopening_id']])) {
                    $storesToNews[$row['extraopening_id']] = array();
                }
                $storesToNews[$row['extraopening_id']][] = $row['store_id'];
            }
        }

        foreach ($this as $item) {
            if(isset($storesToNews[$item->getId()])) {
                $item->setStores($storesToNews[$item->getId()]);
            } else {
                $item->setStores(array());
            }
        }

        return $this;
    }

    public function addSelectStores()
    {
        $extraopeningId = $this->getId();
        $select = $this->getConnection()->select()
            ->from($this->getTable('extraopening/extraopening_store'))
            ->where('extraopening_id = ?', $extraopeningId);
        $result = $this->getConnection()->fetchAll($select);
        $stores = array();
        foreach ($result as $row) {
            $stores[] = $row['store_id'];
        }
        $this->setSelectStores($stores);

        return $this;
    }
}