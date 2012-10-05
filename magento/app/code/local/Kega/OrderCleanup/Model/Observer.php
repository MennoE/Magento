<?php

class Kega_OrderCleanup_Model_Observer
{
    /**
     * Cancel all pending orders placed 30 to 120 mins ago
     *
     * @param   Mage_Cron_Model_Schedule $observer
     * @return  Kega_OrderCleanup_Model_Observer
     */
    public function cleanup($observer)
    {
        Mage::getModel('kega_ordercleanup/OrderCleanup')->cleanup();
        return $this;
    }
}