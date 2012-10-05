<?php

class Kega_Checkout_Model_Cart extends Mage_Checkout_Model_Cart
{
    /**
     * Kega_Checkout_Model_Cart::updateItems
	 * Extended to be able to switch simple products (like shoe size) in the cart
	 * For this we need to remove the previously selected simple and re-add the newly selected simple
     *
     * @param   array $data
     * @return  Mage_Checkout_Model_Cart
     */
    public function updateItems($data)
    {
        Mage::dispatchEvent('checkout_cart_update_items_before', array('cart'=>$this, 'info'=>$data));

        foreach ($data as $itemId => $itemInfo) {
			$item = $this->getQuote()->getItemById($itemId);
			if (!$item) {
				continue;
			}

			$this->removeItem($itemId);

            if (!empty($itemInfo['remove']) || (isset($itemInfo['qty']) && $itemInfo['qty']=='0')) {
                continue;
            }

			$product = Mage::getModel('catalog/product')->load($item->getProductId());
			$params = array('product' => $product->getId());

			foreach($itemInfo['option'] as $id => $value) {
				$params['super_attribute'] = array($id => $value);
			}
			$params['qty'] = 0;

            $qty = isset($itemInfo['qty']) ? (float) $itemInfo['qty'] : false;
            if ($qty > 0) {
                $params['qty'] = $qty;
            }

			$this->addProduct($product, $params);
        }

		$this->save();
		Mage::getSingleton('checkout/session')->setCartWasUpdated(true);

        Mage::dispatchEvent('checkout_cart_update_items_after', array('cart'=>$this, 'info'=>$data));
        return $this;
    }
}