<?

/**
 * @category   Kega
 * @package    Kega_Wizard
 */
class Kega_Wizard_CartController extends Mage_Core_Controller_Front_Action
{
    /**
	 * Kega_Wizard_CartController::_getCart
     * Retrieve shopping cart model object
     *
	 * @param void
     * @return Mage_Checkout_Model_Cart
     */
    protected function _getCart()
    {
        return Mage::getSingleton('checkout/cart');
    }

    /**
	 * Kega_Wizard_CartController::_getSession
     * Get checkout session model instance
     *
	 * @param void
     * @return Mage_Checkout_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('checkout/session');
    }

	/**
	 * Kega_Wizard_CartController::addAction
	 * Handles all cart XHR from the frontend
     *
	 * @param void
	 * @return void
	 */
	public function addAction()
	{
        $cart = $this->_getCart();
		$req = $this->getRequest()->getParams();

		if (!($product = $this->_validateProduct($req['product']))) {
			error_log('CartController::addAction ~ no product found');
			echo 'FAILED';
			return;
		}

		$params = array('product' => $req['product']);
		if (isset($req['super_attribute_value'])) {
			$params['super_attribute'] = array($req['super_attribute_id'] => $req['super_attribute_value']);
		}

		try {
			$cart->addProduct($product, $params);
			$cart->save();

			$this->_getSession()->setCartWasUpdated(true);
			$this->_getUpdatedCartHtml();
		}
		catch (Mage_Core_Exception $e) {
			echo 'FAILED';
		}
        catch (Exception $e) {
			echo 'FAILED';
        }
	}

	/**
     * Update shoping cart data action Extend
     * For some reason errors are displayed twice when cart is updated and product is not in stock.
     * Since there was no time to find the source of this problem this dirty fix was used.
     *
     * Outcommented adding of error fixes the problem.
     *
     * @todo Find better fix/cause for this?
     */
    public function updatePostAction()
    {
        try {
            $cartData = $this->getRequest()->getParam('cart');
            if (is_array($cartData)) {
                $filter = new Zend_Filter_LocalizedToNormalized(
                    array('locale' => Mage::app()->getLocale()->getLocaleCode())
                );
                foreach ($cartData as $index => $data) {
                    if (isset($data['qty'])) {
                        $cartData[$index]['qty'] = $filter->filter($data['qty']);
                    }
                }
                $cart = $this->_getCart();
                if (! $cart->getCustomerSession()->getCustomer()->getId() && $cart->getQuote()->getCustomerId()) {
                    $cart->getQuote()->setCustomerId(null);
                }

                $cartData = $cart->suggestItemsQty($cartData);
                $cart->updateItems($cartData)
                    ->save();
            }
            $this->_getSession()->setCartWasUpdated(true);
        }
        catch (Mage_Core_Exception $e) {
        	/**
        	 * Dirt fix goes below
        	 * Commented line causes duplicate error messages
        	 */
            //$this->_getSession()->addError($e->getMessage());
        }
        catch (Exception $e) {
            $this->_getSession()->addException($e, $this->__('Cannot update shopping cart.'));
        }
        $this->_goBack();
    }

	/**
	 * Kega_Wizard_CartController::_getUpdatedCartHtml
	 * Rebuild the cart block and send it back to the browser
     *
	 * @param $productId integer
	 * @return string
	 */
	private function _getUpdatedCartHtml()
	{
		$block = $this->getLayout()->createBlock('checkout/cart_sidebar')
			->setName('cart_sidebar')
			->setTemplate('checkout/cart/sidebar.phtml')
			->addItemRender(
				'configurable',
				'checkout/cart_item_renderer_configurable',
				'checkout/cart/sidebar/default.phtml'
			)
			->addItemRender(
				'simple',
				'checkout/cart_item_renderer',
				'checkout/cart/sidebar/default.phtml'
			);
		$this->getResponse()->setBody($block->toHtml());
	}

	/**
	 * Kega_Wizard_CartController::_validateProduct
	 * Tries loading the requested product
     *
	 * @param $productId integer
	 * @return $product Mage_Catalog_Model_Product
	 */
	private function _validateProduct($productId)
	{
		$productId = (int) $productId;
		$product = Mage::getModel('catalog/product')
			->setStoreId(Mage::app()->getStore()->getId())
            ->load($productId);

		return $product->getId() ? $product : false;
	}
}