<?php

class Kega_CustomerExport_Model_Export extends Mage_Core_Model_Abstract
{
	const EXPORT_DIRECTORY = 'customerexport';
	const EXPORT_TEMP_FILE = 'customerexport.temp';

	/**
	 * Export customers to file
	 * Exporting starts at id defined in EXPORT_TEMP_FILE,
	 * and counts up to EXPORT_AT_ONCE
	 * When exporting is done, the last id is saved in the temp file,
	 * which is the new starting point for next export.
	 * 
	 * @param void
	 * @return void
	 */
	public function export()
	{
		$startDate = date('Y-m-d H:i:s');

		$startId = $this->getStartId();
		$endId = $startId + Mage::getStoreConfig('kega_customerexport/export/export_at_once');

		$exportDirectory = self::EXPORT_DIRECTORY;

		$count = 0;
		foreach ($this->getAllCustomers($startId, $endId) as $customer)
		{
			$customerId = $this->exportCustomer($customer);
			$count++;
		}
		
		$newStartId = $this->setStartId($endId);
		
		echo 'start: '. $startDate . PHP_EOL;
		echo 'end: '. date('Y-m-d H:i:s') . PHP_EOL;
		echo 'exported customers: ' . $count . PHP_EOL;
		echo 'next starting point: ' . $newStartId . PHP_EOL;
	}

	/**
	 * Export single customer to file <id>.customer
	 * 
	 * @param Mage_Customer_Model_Customer $customer
	 * @return string Customer id
	 */
	private function exportCustomer($customer)
	{
		$fullCustomerData = array();
		$billingAddress = $shippingAddress = $shippingData = $billingData = array();
		$customerDataRaw = $customer->getData();

		if (isset($customerDataRaw['default_billing'])) {
			$billingAddress = Mage::getModel('customer/address')->load($customerDataRaw['default_billing'])->getData();
			
			if ($customerDataRaw['default_billing'] != $customerDataRaw['default_shipping']) {
				$shippingAddress = Mage::getModel('customer/address')->load($customerDataRaw['default_shipping'])->getData();
			}
		}
		$customerData = $this->customerMapping($customerDataRaw);

		if (!empty($billingAddress)) {
			$billingData = $this->addressMapping($billingAddress);
		}
		if (!empty($shippingAddress)) {
			$shippingData = $this->addressMapping($shippingAddress);
		}

		$fullCustomerData['customer'] = $customerData;
		$fullCustomerData['billing_address'] = $billingData;
		$fullCustomerData['shipping_address'] = $shippingData;

		$directory = Mage::getBaseDir('var') . DS . self::EXPORT_DIRECTORY;
		if (!file_exists($directory)) {
			mkdir($directory, 0777, true);
		}
		$filename = $directory . '/' . $customerDataRaw['entity_id'] . '.customer';
		$file = fopen($filename, 'a+');

		fwrite($file, serialize($fullCustomerData));

		return $customerDataRaw['entity_id'];
	}

	/**
	 * Get customer collection between to entity_ids
	 * 
	 * @param string $startId
	 * @param string $endId
	 * @return Mage_Customer_Model_Entity_Customer_Collection
	 */
	private function getAllCustomers($startId, $endId)
	{
		$collection = Mage::getModel('customer/customer')
			->getCollection()
			->addAttributeToFilter('entity_id', array('gteq' => $startId))
			->addAttributeToFilter('entity_id', array('lt' => $endId))
			->addAttributeToSelect('default_billing')
			->addAttributeToSelect('default_shipping')
			->addAttributeToSelect('prefix')
			->addAttributeToSelect('firstname')
			->addAttributeToSelect('lastname')
			->addAttributeToSelect('password_hash');

		return $collection;
	}

	/**
	 * Create array of address fields that needs to be exported
	 * 
	 * @param array $data
	 * @return array $addressData
	 */
	private function addressMapping($data)
	{
		$fields = array('created_at', 'is_active', 'prefix', 'firstname', 'lastname', 'city', 'country_id', 'postcode', 'telephone', 'street');
		$addressData = array();
		foreach ($fields as $field) {
			$addressData[$field] = isset($data[$field]) ? $data[$field] : '';
		}

		return $addressData;
	}

	/**
	 * Create array of customer fields that needs to be exported
	 * 
	 * @param array $data
	 * @return array $customerData
	 */
	private function customerMapping($data)
	{
		$fields = array('website_id', 'email', 'password_hash', 'group_id', 'store_id', 'created_at', 'prefix', 'firstname', 'lastname');
		$customerData = array();
		foreach ($fields as $field) {
			$customerData[$field] = isset($data[$field]) ? $data[$field] : '';
		}

		return $customerData;
	}

	/**
	 * Get starting id of export in EXPORT_TEMP_FILE
	 * If file doesnt exists, create it and insert '1'
	 * 
	 * @param void
	 * @return int $startId
	 */
	private function getStartId()
	{
		$tempFilename = Mage::getBaseDir('var') . DS . self::EXPORT_TEMP_FILE;

		if (file_exists($tempFilename)) {
			$tempFile = fopen($tempFilename, 'r+');
			$startId = fread($tempFile, filesize($tempFilename));
		}
		else {
			$startId = 1;
			$tempFile = fopen($tempFilename, 'w');
			fwrite($tempFile, $startId);
		}

		return $startId;
	}

	/**
	 * Set (new) starting id for export in EXPORT_TEMP_FILE
	 * 
	 * @param int $id
	 * @return int $id
	 */
	private function setStartId($id)
	{
		$tempFilename = Mage::getBaseDir('var') . DS . self::EXPORT_TEMP_FILE;
		$newFile = fopen($tempFilename, 'w');
		fwrite($newFile, $id);

		return $id;
	}
}