<?php

class Kega_CustomerExport_Model_Import extends Mage_Core_Model_Abstract
{
	const EXPORT_DIRECTORY = Kega_CustomerExport_Model_Export::EXPORT_DIRECTORY;

	/**
	 * Import customers from export directory
	 * 
	 * @param void
	 * @return void
	 */
	public function import()
	{
		$startdate = date('Y-m-d H:i:s');

		$importDirectory = Mage::getBaseDir('var') . DS . self::EXPORT_DIRECTORY;

		$files = scandir($importDirectory);
		unset($files[0]);
		unset($files[1]);

		if (count($files) <= 0) {
			die('no customer files to be imported');
		}

		$customerCount = 0;
		$errors = 0;
		foreach ($files as $file) {
			$fullCustomerData = $this->getFileContent($importDirectory . DS . $file);

			if ($this->importCustomer($fullCustomerData)) {
				unlink($importDirectory . DS . $file);
				$customerCount++;
			}
			else {
				$errors++;
			}
		}

		echo 'start: ' . $startdate . PHP_EOL;
		echo '- end: ' . date('Y-m-d H:i:s') . PHP_EOL;
		echo '- customers imported: '. $customerCount . PHP_EOL;
		echo '- missed files: ' . $errors . PHP_EOL;
	}

	/**
	 * Import customer
	 * 
	 * @param array $fullCustomerData Unserialized array from .customer file
	 * @return bool True on success
	 */
	private function importCustomer($fullCustomerData)
	{
		$customerData = $billingAddressData = $shippingAddressData = array();
		$customerData = $fullCustomerData['customer'];
		$customerData = $this->storeMapping($customerData);
		if (Mage::getStoreConfig('kega_customerexport/import/default_password')) {
			$customerData['password_hash'] = 'c26e86f088ec4329536e104f0b3a55b22967739af10f3578584159157d4dd848:po'; // Welkom#1
		}
		$billingAddressData = $fullCustomerData['billing_address'];
		$shippingAddressData = $fullCustomerData['shipping_address'];

	    // import customer
	    try {
		    $customer = Mage::getModel('customer/customer')->setId(null);
			$customer->setData($customerData);
			$customer->save();
			
			$newCustomerId = $customer->getId();
			
			// import billing address
			if (!empty($billingAddressData)) {
				$address = Mage::getModel('customer/address')->setId(null);
				$billingAddressData['parent_id'] = $newCustomerId;
				
				$address->setData($billingAddressData);
				$address->save();
				
				$billingAddressId = $address->getId();
				
				$customer->setDefaultBilling($billingAddressId);
				$customer->save();
			}
			
			// import shipping address
			if (empty($shippingAddressData)) {
				if (isset($billingAddressId)) {
					$customer->setDefaultShipping($billingAddressId);
					$customer->save();
				}
			}
			else {
				$address = Mage::getModel('customer/address')->setId(null);
				$shippingAddressData['parent_id'] = $newCustomerId;
				
				$address->setData($shippingAddressData);
				$address->save();
				
				$shippingAddressId = $address->getId();
				
				$customer->setDefaultShipping($shippingAddressId);
				$customer->save();
			}
			return true;
	    } catch (Exception $e) {
	    	if (Mage::getStoreConfig('kega_customerexport/import/debug_mode')) {
	    		echo '<pre>'; print_r($customerData) . PHP_EOL;
	    		echo 'Exception: ' . $e->getMessage() . PHP_EOL;
	    	}
	    	return false;
	    }
	}

	/**
	 * Get content from file and unserialize it
	 * 
	 * @param string $file Full path of file
	 * @return array $fullCustomerData Unserialized file content
	 */
	private function getFileContent($filename)
	{
		$file = fopen($filename, 'r');
		$fileContent = fread($file, filesize($filename));

		$fullCustomerData = unserialize($fileContent);

		return $fullCustomerData;
	}

	/**
	 * Store and website ids of exported shop can be different
	 * Set the store ids to the new shops store ids
	 * 
	 * @param array $customerData
	 * @return array $customerData
	 */
	private function storeMapping($customerData)
	{
		$mapping = Mage::getStoreConfig('kega_customerexport/import/store_mapping');
		if (!empty($mapping)) {
			$mapping = explode(',', $mapping);
			$storeIds = array();
			foreach ($mapping as $store) {
				$store = explode('=', $store);
				$storeIds[$store[0]] = $store[1];
			}

			$customerData['store_id'] = isset($storeIds[$customerData['store_id']]) 
				? $storeIds[$customerData['store_id']]
				: $customerData['store_id'];
		}

		$mapping = Mage::getStoreConfig('kega_customerexport/import/website_mapping');
		if (!empty($mapping)) {
			$mapping = explode(',', $mapping);
			$websiteIds = array();
			foreach ($mapping as $store) {
				$store = explode('=', $store);
				$websiteIds[$store[0]] = $store[1];
			}

			$customerData['website_id'] = isset($websiteIds[$customerData['website_id']])
				? $websiteIds[$customerData['website_id']]
				: $customerData['website_id'];
		}

		return $customerData;
	}

}