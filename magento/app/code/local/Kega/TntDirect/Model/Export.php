<?php
class Kega_TntDirect_Model_Export extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('kega_tntdirect/export');
    }

    public function create($extraInfo = null) {
        if ($this->getId()) {
            throw new Mage_Core_Model_Mysql4_Exception("Can't create new export row on already active export row.");
        }

    	$date = Mage::app()->getLocale()->date(time());
		$this->setCreatedAt($date->toString('YYYY-MM-dd HH:mm:ss'));
		$this->setUpdatedAt($date->toString('YYYY-MM-dd HH:mm:ss'));

        if ($extraInfo) {
            $this->setExtraInfo($extraInfo);
        }

        // Save record, so id get selected.
		$this->save();

        // Retreive id and use it to construct the filename.
        $this->setFilename('VM' . $this->getFileId() . '.LST');
        $this->save();

        return $this;
    }

    public function getFileId() {
        if (!$this->getId()) {
            return null;
        }

        return str_pad($this->getId(), 6, 0, STR_PAD_LEFT);
    }

    public function uploaded($extraInfo = null) {
        if (!$this->getId()) {
            throw new Mage_Core_Model_Mysql4_Exception("Can't set non active export row to uploaded.");
        }

    	$date = Mage::app()->getLocale()->date(time());
        $this->setUpdatedAt($date->toString('YYYY-MM-dd HH:mm:ss'));
		$this->setUploadedAt($date->toString('YYYY-MM-dd HH:mm:ss'));

        if ($extraInfo) {
            $this->setExtraInfo($extraInfo);
        }

		$this->save();

        return $this;
    }

    public function error($extraInfo) {
        if (!$this->getId()) {
            throw new Mage_Core_Model_Mysql4_Exception("Can't set non active export row to error.");
        }

    	$date = Mage::app()->getLocale()->date(time());
		$this->setUpdatedAt($date->toString('YYYY-MM-dd HH:mm:ss'));

        $this->setExtraInfo($extraInfo);

		$this->save();

        return $this;
    }
}