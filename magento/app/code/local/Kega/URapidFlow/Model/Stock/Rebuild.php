<?php
class Kega_URapidFlow_Model_Stock_Rebuild extends Mage_Core_Model_Abstract
{
    /**
	 * Process in 'processed' orders, subtract orders items from new stock
	 * The regular stock import does not take processed order into account, it can't
	 * This script is trigger at the end of the stock import to subtract all ordered items
	 * that are on 'processing' and thus havn't been shipped from the stock.
	 *
	 * @return  void
	 */
	public function orderProductStockUpdate()
	{
		echo '-- Starting product stock correction script' . PHP_EOL;

	    $collection = Mage::getResourceModel('sales/order_collection')
	        ->addAttributeToSelect('*')
			->addFieldToFilter('status', 'processing');

	    if (!$collection->getSize())  return;
        foreach ($collection as $order) {
			echo sprintf('#%s - stock correction', $order->getIncrementId()) . PHP_EOL;

            $orderItems = $order->getAllItems();
            foreach ($orderItems as $orderItem) {

                //we only set qty for simple products
                if ($orderItem->getProductType() != 'simple')  continue;


                $qtyOrdered = $orderItem->getData('qty_ordered');
                $qtyShipped = $orderItem->getData('qty_shipped');
                $qtyCanceled = $orderItem->getData('qty_canceled');
                $qtyRefunded = $orderItem->getData('qty_refunded');

                //qty previously updated using this method
                $qtyUpdated = $orderItem->getData('qty_updated');

				echo sprintf(
					'#%s - qtyOrdered: %s, qtyShipped: %s, qtyCanceled: %s, qtyRefunded: %s, qtyUpdated: %s',
					$order->getIncrementId(), $qtyOrdered, $qtyShipped, $qtyCanceled, $qtyRefunded, $qtyUpdated) . PHP_EOL;

                $stockQtyToSubstract = $qtyOrdered
                                     - $qtyShipped
                                     - $qtyCanceled
                                     - $qtyRefunded
                                     - $qtyUpdated;

				echo sprintf(
					'#%s - stockQtyToSubstract: %s',
					$order->getIncrementId(), $stockQtyToSubstract
				) . PHP_EOL;

                //nothing to update
                if ($stockQtyToSubstract <=0 ) {
					echo sprintf('#%s - nothing to subtract', $order->getIncrementId()) . PHP_EOL;

					continue;
				}

                $product = Mage::getModel('catalog/product')->load($orderItem->getProductId());

                // product not found
                if(!$product->getId()) {
					echo sprintf('#%s - product not found', $order->getIncrementId()) . PHP_EOL;
					continue;
				}

                $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);

                $oldStockQty = $stockItem->getQty();

                if ($oldStockQty <= 0) {
					echo sprintf('#%s - item already out of stock for product %s', $order->getIncrementId(), $product->getId()) . PHP_EOL;
					continue;
				}

				$newStockQty = $oldStockQty - $stockQtyToSubstract;
				echo sprintf(
					'#%s - updated stock to %s (was %s) for product %s',
					$order->getIncrementId(), $newStockQty, $oldStockQty, $product->getId()
				) . PHP_EOL;

            	$stockItem->setQty($newStockQty)
                          ->save();

                $orderItem->setData('qty_updated', $stockQtyToSubstract);
                $orderItem->save();
			}
        }
	}

	/**
	 * Set correct 'is in stock status' on the configurable products.
	 * We first export all configurable products, with qty and current status.
	 * Then we check if qty is > 0 and if the calculated status matches the current status.
	 * If status is different we set a new 'is in stock status' for the configurable product.
	 */
	public function setConfigurableProductIsInStockStatus()
	{
		echo '-- Starting configurable product is in stock correction script' . PHP_EOL;

		// Export all configurable products with calculated qty (sum of all simple products).
		$profile = Mage::helper('urapidflow')
					->run('Product Stock Export (totals)');

		if (!$profile->getId()) {
			Mage::throwException('Could\'t load profile: Product barcode2sku Export');
		}

		$filePath = $profile->getFileBaseDir() . DS . $profile->getFilename();
		$handle = fopen($filePath, 'r');

		$targetPath = Mage::helper('kega_urapidflow')->getFileDir('import') . DS . '/stock.txt';
		$writeHandle = fopen($targetPath, 'w+');

		$header = false;
		while (($data = fgetcsv($handle, 400000000, ",")) !== false) {
			if (!$header) {
				fwrite($writeHandle, 'sku,stock.is_in_stock' . PHP_EOL);
				$header = true;
				continue;
			}

			// Convert total product qty to stock.is_in_stock
			$data[1] = (intval($data[1]) > 0 ? 1 : 0);

			// If new stock.is_in_stock is the same as original, skip importing.
			if ($data[1] == $data[2]) {
				continue;
			}

			// Remove old stock.is_in_stock value.
			unset($data[2]);

			echo sprintf('Set stock.is_in_stock to %s for sku: %s', $data[1], $data[0]) . PHP_EOL;

			$line = implode(',', $data) . PHP_EOL;
			fwrite($writeHandle, $line);
		}

		fclose($handle);
		fclose($writeHandle);
		@unlink($filePath);

		// Set new 'is in stock status' on the configurable products.
		Mage::helper('urapidflow')->run('Product Stock Import');
		@unlink($targetPath);
	}
}