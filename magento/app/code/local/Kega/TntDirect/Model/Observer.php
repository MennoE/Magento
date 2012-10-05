<?php
class Kega_TntDirect_Model_Observer
{
	/**
    * Export all 'voormelding' files to TNT server.
    *
    * @param   Varien_Event_Observer $observer
    * @return  Kega_Import_Model_Observer
    */
    public function export2PostNL($observer)
    {
        Mage::getModel('kega_tntdirect/files')->export2PostNL();

		return $this;
    }
}