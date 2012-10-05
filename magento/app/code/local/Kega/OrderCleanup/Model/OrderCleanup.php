<?php

class Kega_OrderCleanup_Model_OrderCleanup extends Mage_Core_Model_Abstract
{
    /**
     * Kega_OrderCleanup_Model_OrderCleanup::cleanup
	 * Cancel all pending orders placed 30 to 120 mins ago
     *
	 * @param void
	 * @return void
     */
    public function cleanup()
    {
        $to = date('Y-m-d H:i', time() - 5400); // 90mins ago

		$orders = Mage::getModel('sales/order')->getCollection()
            ->addFieldToFilter('status', 'pending')
            ->addFieldToFilter('created_at', array('to' => $to));

        if (count($orders)) {
			echo 'cancelled (' . count($orders) . '): ';
            foreach($orders as $order) {
            	echo $order->getIncrementId() . ',';

                $realOrder = Mage::getModel('sales/order')->load($order->getId());

				$order->cancel();
				$order->addStatusToHistory(
					$order->getStatus(),
					Mage::helper('core')->__('Order was canceled by order cleanup script')
				);

                $order->save();
            }
        }
        else {
        	echo 'no orders to cancel';
        }
    }
}