<?php
/**
 * @category   Kega
 * @package    Kega_StorePickup
 *
 * @tutorial app/code/local/Kega/StorePickup/doc/usage.info
 *
 */
class Kega_StorePickup_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Checks if an order shipping method is store pickup or not
     *
     * @param Mage_Sales_Model_Order $order
     * @return bool
     */
    public function orderShippingIsStorePickup($order)
    {
        // get additional data to check if it's a store pickup order
        if ($paymentAdditional = $order->getPayment()) {
	        $additionalData = $paymentAdditional->getAdditionalData()
	            ? unserialize($paymentAdditional->getAdditionalData())
	            : array()
	        ;

	        if (!empty($additionalData['store_pickup'])) {
	        	return true;
	        }
        }

        return false;
    }

    /**
     * Get store order pickup data
     *
     * @param Mage_Sales_Model_Order $order
     * @return mixed array or false
     */
    public function getStoreOrderPickupData($order)
    {
        // get additional data to check if it's a store pickup order
        $paymentAdditional = $order->getPayment();
        if ($paymentAdditional = $order->getPayment()) {
	        $additionalData = $paymentAdditional->getAdditionalData()
	            ? unserialize($paymentAdditional->getAdditionalData())
	            : array()
	        ;

			if (isset($additionalData['store_pickup'])) {
				return $additionalData['store_pickup'];
			}
        }

        return false;
    }

    /**
     * Gets the store pickup data as it is saved in the store table
     * @param Mage_Sales_Model_Order $order
     * @return mixed array or false
     */
    public function getStorePickupDataDb($order)
    {
        // get additional data to check if it's a store pickup order
        $paymentAdditional = $order->getPayment();
        if ($paymentAdditional = $order->getPayment()) {
	        $additionalData = $paymentAdditional->getAdditionalData()
	            ? unserialize($paymentAdditional->getAdditionalData())
	            : array()
	        ;

	        if(!empty($additionalData['store_pickup']))  {
	            $storeData = Mage::getModel('store/store')->setStoreFilter()->load($additionalData['store_pickup']['id']);
	            return empty($storeData)? $additionalData['store_pickup']: $storeData->getData();
	        }
        }

        return false;
    }


    /**
     * Get shipping address for store pickup; if clone is set to true
     * then it doesn't change the original shipping address
     * With $clone = false it changes the order shipping address
     *
     * @param Mage_Sales_Model_Order $order
     * @param bool $clone
     * @return Mage_Sales_Model_Order_Address
     */
    public function addStorePickupDataToShipping($order, $clone = true)
    {
        $storePickupData = $this->getStoreOrderPickupData($order);

        if (!$storePickupData || !isset($storePickupData['name'])) return $order->getShippingAddress();

        // we add the store name as first name because otherwise
        // the address will start with a blank line
        $shippingData = array(
            'prefix' => '',
            'firstname' => $storePickupData['name'],
            'middlename' => '',
            'lastname' => '',
            'suffix' => '',
            'company' => '',
            'street' => implode("\n", array($storePickupData['address'])),
            'city' => $storePickupData['city'],
            'postcode' => $storePickupData['zipcode'],
            'telephone' => '',
            'street1' => $storePickupData['address'],
        );

        if ($clone) {
            $orderShipping = clone $order->getShippingAddress();
        } else {
            //preserve original shipping data
            $originalShippingAddress = clone $order->getShippingAddress();

            if (!$order->getOriginalShippingAddress()) {
                $order->setOriginalShippingAddress($originalShippingAddress);
            }

            $orderShipping = $order->getShippingAddress();
        }


        foreach ($shippingData as $fieldKey => $fieldValue) {
            $orderShipping->setData($fieldKey, $fieldValue);
        }

        return $orderShipping;
    }


    /**
     * Remove store pickup shipping address
     *
     * @param Mage_Sales_Model_Order $order
     * @return void
     */
    public function removeStorePickupDataShipping($order)
    {
        $shippingData = array(
            'prefix',
            'firstname',
            'middlename',
            'lastname',
            'suffix',
            'company',
            'street',
            'city',
            'postcode',
            'telephone',
            'street1',
            'street2',
            'street3',
        );

        if (!$order->getOriginalShippingAddress()) return;

        if ($order->getOriginalShippingAddress()) {
            $originalShippingAddress = clone $order->getOriginalShippingAddress();
            foreach($shippingData as $fieldName) {
                $originalValue = $originalShippingAddress->getData($fieldName);
                $order->getShippingAddress()->setData($fieldName, $originalValue);
            }
        }
    }

}