<?php

class Kega_URapidFlow_Model_Stock_Emulate extends Mage_Core_Model_Abstract
{
 	public function __construct()
	{
		ini_set("memory_limit", '3000M');
	}

   /**
	* This will update all simple products to have stock of 50
	* All parent products will be activated
	*
	* NOTE: you probably dont want to run this on a live website!
	*
	* @param void
	* @return void
	*/
	public function emulateFullStock()
	{
		$simples = Mage::getResourceModel('catalog/product_collection')
			->addAttributeToFilter('type_id', array('eq' => 'simple'))
			->addAttributeToFilter('status', array('eq' => '1'));


		foreach($simples as $simple) {
			$simple = Mage::getModel('catalog/product')->load($simple->getId());

			$stockItem = Mage::getModel('cataloginventory/stock_item')
				->loadByProduct($simple);

			if (!$stockItem->getId()) {
				$stockItem->assignProduct($simple);
				$stockItem->setStockId(1);
				$stockItem->setUseConfigManageStock(1);
			}

			$stockItem->setQty(50)
					  ->setIsInStock(1)
					  ->save();

			echo sprintf("Stock update: %s", $simple->getSku()) . PHP_EOL;
		}

		$configurables = Mage::getResourceModel('catalog/product_collection')
			->addAttributeToFilter('type_id', array('eq' => 'configurable'));

		foreach($configurables as $configurable) {
			$stockItem = Mage::getModel('cataloginventory/stock_item')
				->loadByProduct($configurable);

			if (!$stockItem->getId()) {
				$stockItem->assignProduct($configurable);
				$stockItem->setStockId(1);
				$stockItem->setUseConfigManageStock(1);
			}

			$stockItem->setIsInStock(1);
			$stockItem->save();

			$configurable->setStatus(1);
			$configurable->save();

			echo sprintf("Activate parent: %s", $configurable->getSku()) . PHP_EOL;
		}
	}

	/**
	 * Reset all products, both simple and configurable, to 0 stock.
	 * Configurables will keep their 'In stock' flag, simple will be set 'out of stock'.
	 * For both the 'manage stock' flag will be kept in tact (active).
	 *
	 * The script starts with exporting all products, after which all stock related
	 * flags will be nullified. Taking the above exceptions into account.
	 *
	 * NOTE: you probably only want to run this script before a new shop goes live,
	 * and you want get rid of the emulated full stock. (see method $this->emulateFullStock())
	 */
	public function resetAllStockToEmpty()
	{
		// initialize the locale - this is very important - otherwise all the import rows are flagged as errors
		// because they contain values such as: 'Ja', 'Ingeschakeld' which are valid only in NL locale
        // @see http://www.unirgy.com/wiki/urapidflow/i18n
		Mage::app()->getLocale()->setLocale('nl_NL');
		Mage::app()->getTranslator()->init('global', true);
		Mage::app()->getTranslator()->init('adminhtml', true);

		$profile = Mage::getModel('urapidflow/profile')
			->load('Product stock - export all', 'title');

		$filePath = $profile->getFileBaseDir() . DS . $profile->getFilename();

		$varDir = Mage::app()->getConfig()->getTempVarDir() . DS;
		$targetPath = $varDir . 'urapidflow/export/stockall-product-importfile.txt';

		Mage::helper('urapidflow')->run('Product stock - export all');

		$handle = fopen($filePath, 'r');
		$writeHandle = fopen($targetPath, 'w+');

		$counter = 1;
		while (($data = fgetcsv($handle, 400000000, ",")) !== false) {
			if ($counter == 1) {
				$counter++;
			}
			else {
				if ($data[4] == 'configurable') {
					$data[1] = '1'; // has stock
				}
				else if ($data[4] == 'simple') {
					$data[1] = '0'; // not in stock
				}
				$data[2] = '0.0000';
				$data[3] = '1'; // manage stock
			}

			unset($data[4]);
			$line = implode(',', $data) . PHP_EOL;
			fwrite($writeHandle, $line);
		}

		fclose($handle);
		fclose($writeHandle);

		Mage::helper('urapidflow')->run('Product stock - import all');
	}
}