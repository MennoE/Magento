<?php
class Kega_Store_Model_Session extends Mage_Core_Model_Session_Abstract
{
    public function __construct()
    {
        $this->init('stores');
    }

    public function getDisplayMode()
    {
        return $this->_getData('display_mode');
    }

}