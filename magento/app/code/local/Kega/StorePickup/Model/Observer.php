<?php
/**
 * @category   Kega
 * @package    Kega_StorePickup
 *
 * @tutorial app/code/local/Kega/StorePickup/doc/usage.info
 *
 */
class Kega_StorePickup_Model_Observer
{
	/**
     * Notification email template configuration
     */
    const XML_PATH_NOTIFICATION_EMAIL_TEMPLATE   = 'store_notification_email/email_template';
    
    /**
     * Notification email identity
     */
    const XML_PATH_NOTIFICATION_EMAIL_IDENTITY   = 'store_notification_email/email_identity';
    
    /**
     * Notification email address
     */
    const XML_PATH_NOTIFICATION_EMAIL_ADDRESS   = 'carriers/storepickup/store_pickup_notification_email_address_test';
	
	
    /**
     * Observes sales_order_load_after
     *
     * Replaces existing order shipping data with store pickup data (if exists)
     *
     * @param  Varien_Event_Observer  $observer
     * @return void
     */
    public function addStorePickupDataToShipping($observer)
    {
        $orderModel = $observer->getEvent()->getOrder();

        if (Mage::helper('storepickup')->orderShippingIsStorePickup($orderModel)) {
            Mage::helper('storepickup')->addStorePickupDataToShipping($orderModel, $clone = false);
        }
    }

     /**
     * Observes sales_order_save_before
     *
     * Replaces store pickup shipping address with the original shipping address
     * otherwise it's saved in database and the original data is lost
     *
     * @param  Varien_Event_Observer  $observer
     * @return void
     */
    public function removeStorePickupDataToShipping($observer)
    {
        $orderModel = $observer->getEvent()->getOrder();

        if (Mage::helper('storepickup')->orderShippingIsStorePickup($orderModel)) {
            Mage::helper('storepickup')->removeStorePickupDataShipping($orderModel);
        }
    }
    
    
	/**
     * Observes checkout_submit_all_after
     *
     * Sends email notification to the retail store if shipping method is pickup; 
     * only for completed orders and only if notification was not send
     *
     * @param  Varien_Event_Observer  $observer
     * @return void
     */
    public function notifyStore($observer)
    {
        $order = $observer->getEvent()->getOrder();
        
        if (!$order) return;
        
        $orderModel = Mage::getModel('sales/order')->load($order->getId());
        
		if ($orderModel->getState() != Mage_Sales_Model_Order::STATE_COMPLETE) return;
		
		if (!Mage::helper('storepickup')->orderShippingIsStorePickup($orderModel)) return;
		
		if ($orderModel->getStorePickupNotificationSent() == '1') return;
        
        $storeOrderPickupData = Mage::helper('storepickup')->getStoreOrderPickupData($order);

		$pickupStoreId = $storeOrderPickupData['storenummer'];
		$retailStore = Mage::getModel('store/store')->setStoreFilter()->load($pickupStoreId);
			
		$emailData = array(
			'deliverydate' => $retailStore->getTodayRoute(), 
			'city' => $storeOrderPickupData['city'],
			'ordernumber' => $order->getIncrementId(),
			'customer_name' => $orderModel->getCustomerName(),
			'order' => $orderModel,
		);   
        
        $translate = Mage::getSingleton('core/translate');
        /* @var $translate Mage_Core_Model_Translate */
        $translate->setTranslateInline(false);

        $emailTemplate = Mage::getModel('core/email_template');
        /* @var $emailTemplate Mage_Core_Model_Email_Template */
        $emailTemplate->setDesignConfig(array('area'=>'frontend', 'store'=>$order->getStoreId()))
              ->sendTransactional(
                   Mage::getStoreConfig(self::XML_PATH_NOTIFICATION_EMAIL_TEMPLATE),
                   Mage::getStoreConfig(self::XML_PATH_NOTIFICATION_EMAIL_IDENTITY),
                   Mage::helper('storepickup')->getStorePickupNotificationEmailAddress($storeOrderPickupData['entity_id']),
                   null,
                   $emailData
               );
        $translate->setTranslateInline(true);
        
        $orderModel->setStorePickupNotificationSent('1');
        $orderModel->save();
    }
    
    


}