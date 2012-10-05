<?php

class Kega_Vacancy_Model_Vacancyregion extends Mage_Core_Model_Abstract
{
    protected $_resource;
    protected $_read;
    protected $_name = 'vacancyregion';

    public function _construct()
    {
        parent::_construct();
        $this->_init('vacancy/vacancyregion');

        $this->_resource = Mage::getSingleton('core/resource');
        $this->_read = $this->_resource->getConnection('core_read');
    }

    public function getActive()
    {
        $select = $this->_read->select();
        $select->from($this->_name)
               ->where('status = ?', 1);
        return $this->_read->fetchAll($select);
    }
}