<?php
require_once('Mage/Checkout/controllers//OnepageController.php');
class Kega_StorePickup_Checkout_OnepageController extends Mage_Checkout_OnepageController
{
	public function saveShippingMethodAction()
    {
    	$this->_expireAjax();
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost('shipping_method', '');
            $result = $this->getOnepage()->saveShippingMethod($data);

            if ($data == 'storepickup_store') {
            	if ($store_id = $this->getRequest()->getPost('pickup_store_id', false))
            	{
            		$store = Mage::getModel('store/store')->load($store_id);
	            	$shipping_address = $this->getOnepage()->getQuote()->getShippingAddress();

	            	$store_street = $store->getAddress();
	            	$store_city = $store->getCity();
	            	$store_postcode = $store->getZipcode();
	            	$store_country_id = $store->getDistrict();
	            	$store_telephone = $store->getPhone();

	            	$store_shipping_address = array(
	            		'firstname' => $shipping_address->getFirstname(),
	            		'lastname' => $shipping_address->getLastname(),
	            		'company' => $shipping_address->getCompany(),
	            		'street' => empty($store_street) ? $shipping_address->getStreet() : array($store_street),
	            		'city' => (empty($store_city) ? $shipping_address->getCity() : $store_city),
	            		'region_id' => '',
	            		'postcode' => (empty($store_postcode) ? $shipping_address->getPostcode() : $store_postcode),
	            		'country_id' => (empty($store_country_id) ? $shipping_address->getCountryId() : $store_country_id),
	            		'telephone' => (empty($store_telephone) ? $shipping_address->getTelephone() : $store_telephone),
	            		'pickup_store_id' => $store->getId(),
	            		'pickup_store_name' => $store->getName(),
	            		'save_in_address_book' => '',
	            	);
	            	parent::getOnepage()->saveShipping($store_shipping_address, false);
            	}
            	else {
            		$result['error'] = 'please select the store';
            	}
            }

            /*
            $result will have erro data if shipping method is empty
            */
            if(!$result) {
                Mage::dispatchEvent('checkout_controller_onepage_save_shipping_method', array('request'=>$this->getRequest(), 'quote'=>$this->getOnepage()->getQuote()));
                $this->getResponse()->setBody(Zend_Json::encode($result));

                $result['goto_section'] = 'payment';
                $result['update_section'] = array(
                    'name' => 'payment-method',
                    'html' => $this->_getPaymentMethodsHtml()
                );

//                $result['payment_methods_html'] = $this->_getPaymentMethodsHtml();
            }
            parent::getResponse()->setBody(Zend_Json::encode($result));
        }
    }

}