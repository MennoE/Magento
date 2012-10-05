<?php
class Kega_URapidFlow_Model_Observer
{
	/**
	 * Observers: controller_action_predispatch_adminhtml_catalog_product_save
	 *
	 * Removes last_updated and created_by_urapidflow fields
	 * so they are not updated (these fields should only be updated by the urapidflow import).
	 */
	public function removeUrapidflowFields($observer)
	{
		$controllerAction = $observer->getEvent()->getControllerAction();

		$request = $controllerAction->getRequest();

		$params = $request->getPost();
		unset($params['product']['last_updated']);
		unset($params['product']['created_by_urapidflow']);
		$request->setPost($params);
	}

	/**
	 * Observers: urapidflow_product_import_after_diff
	 *
	 * When importing products find all newly created products and save them to a logfile.
	 * We only do this for profiles that have the import action option set to only 'create' new records.
	*/
	public function logNewProducts($observer)
	{
		$vars = $observer->getEvent()->getVars();

		$profileOptions = $vars['profile']->getOptions();
		if ($profileOptions['import']['actions'] != 'create') {
			return;
		}

		if (!empty($vars['insert_entity'])) {
			$logFilename = Mage::helper('kega_urapidflow')->getFileDir('log') . DS . $vars['profile']->getFilename() . '.new';
			$handle = fopen($logFilename, 'a');

			foreach ($vars['insert_entity'] as $sku => $basicData) {
				$product = array_merge($basicData, $vars['change_attr'][$sku]);
				$line = sprintf('%s, %s (%s)',
					$product['sku'],
					$product['name'],
					trim($product['type_id'])
				);

				fwrite($handle, $line . PHP_EOL);
			}

			fclose($handle);
		}
	}

	/**
	 * Cleanup old files and directories set in configuration
	 *
	 * @param void
	 * @return void
	 */
	public function runDirectoryCleanup()
	{
		$cleanup = Mage::getModel('kega_urapidflow/cleanup');
		return $cleanup->cleanupDirectories();
	}
}