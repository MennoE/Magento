<?php
/**
 * @category   Kega
 * @package    Kega_Touch
 */
class Kega_Touch_Model_Sqlite_Settings extends Kega_Touch_Model_Sqlite_Abstract
{
	/**
	 * Table name
	 * @var string
	 */
	protected $_table = 'settings';

	/**
	 * Column definitions
	 * @var array
	 */
	protected $_columns = array('id' => 'TEXT PRIMARY KEY',
								'value' => 'TEXT',
								);

	/**
	 * Import config array into SQLite DB.
	 *
	 * @param array $config
	 */
	public function importConfig(array $config)
	{
		// Add DB creation data.
		$config['db_created_at'] = date('Y-m-d H:i:s');

		// Add extra shipping data to the config.
		$this->_addShippingData($config, 'shipping_method_delivery');
		$this->_addShippingData($config, 'shipping_method_pickup');

		// Sort the settings A-Z.
		ksort($config);

		// Convert to input rows that match the column definitions.
		$rows = array();
		foreach ($config as $key => $value) {
			$rows[] = array($key, $value);
		}

		$this->insertRows($rows);
	}

	/**
	 * Add extra shipping data to the config (price and free_shipping_subtotal)
	 *
	 * @param array $config (passed by refference)
	 * @param string $key (key of shipping method in config array)
	 */
	private function _addShippingData(&$config, $key)
	{
		$price = null;
		$freeShippingSubtotal = null;

		if ($carrierModel = $this->_getCarrierModel($config[$key], $config['api_store_id'])) {
			$price = $carrierModel->getConfigData('price');
			if ($carrierModel->getConfigData('free_shipping_enable')) {
				$freeShippingSubtotal = $carrierModel->getConfigData('free_shipping_subtotal');
			}
		}

		$config[$key . '_price'] = $price;
		$config[$key . '_free_shipping_subtotal'] = $freeShippingSubtotal;
	}

	/**
	 * Retreive carrier model with the use of carrier with shipping method key.
	 *
	 * @param string $carrierMethod
	 * @param int $storeId
	 */
	private function _getCarrierModel($carrierMethod, $storeId)
	{
		$carriers = Mage::getSingleton('shipping/config')
						->getActiveCarriers($storeId);

		foreach ($carriers as $carrierCode => $carrierModel) {
			foreach ($carrierModel->getAllowedMethods() as $methodCode => $methodTitle) {
				if ($carrierMethod == $carrierCode . '_' . $methodCode) {
					return $carrierModel;
				}
			}
		}

		return false;
	}
}