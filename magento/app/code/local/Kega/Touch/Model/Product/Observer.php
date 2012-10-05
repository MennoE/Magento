<?php
/**
 * @category   Kega
 * @package    Kega_Touch
 */
class Kega_Touch_Model_Product_Observer
{
	/**
	 * Observers: cataloginventory_stock_item_save_commit_after
	 *
	 * Updates the updated_at value of the catalog/product when cataloginventory/stock_item is saved (updated).
	 * We need this for the stock export to the touch app, because the app needs to know what products have an updated stock.
	 *
	 * @param Varien_Event_Observer $observer
	 */
	public function setUpdatedAt($observer)
	{
		$resource = Mage::getSingleton('core/resource');
		$resource->getConnection("core_write")
			->update($resource->getTableName('catalog/product'),
					 array('updated_at' => Mage::getSingleton('core/date')->gmtDate()),
					 array('entity_id = ?' => $observer->getItem()->getProductId())
					 );
	}
}