<?php

class Kega_Extraopening_Model_Extraopening extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('extraopening/extraopening');
    }

    public function getExtraOpeningStatuses()
    {
        $statuses = array();

        $statuses[] = array(
            'value' => 'OPEN',
            'label' => 'open',
        );

        $statuses[] = array(
            'value' => 'CLOSED',
            'label' => 'closed',
        );

        return $statuses;
    }

    public function getExtraOpeningStatusesOptions()
    {

        $statuses = array(
            'OPEN' => 'open',
            'CLOSED' => 'closed',
        );

        return $statuses;
    }

    public function addStoreId($storeId)
    {
        $ids = $this->getStoreIds();
        if (!in_array($storeId, $ids)) {
            $ids[] = $storeId;
        }
        $this->setStoreIds($ids);
        return $this;
    }

    public function getStoreIds()
    {
        $ids = $this->_getData('store_ids');
        if (is_null($ids)) {
            $this->loadStoreIds();
            $ids = $this->getData('store_ids');
        }
        return $ids;
    }

    public function loadStoreIds()
    {
        $this->_getResource()->loadStoreIds($this);
    }

    public function getActiveNews()
    {
        return $this->_getResource()->getActiveNews($this);
    }
}