<?php

class Kega_Retargeting_Model_Example
{
	/**
	 * This is an example of a retargeting pixel
	 *
	 * @see doc/readme.txt
	 * @param void
	 * @return string Pixel code
	 */
	public function getPixel()
	{
		$code = '';
		$request = Mage::app()->getRequest();
		$controllerAction = $request->getRouteName() . '_' . $request->getControllerName() . '_' . $request->getActionName();

		$category = Mage::helper('kega_retargeting')->loadCurrentCategory();
		$product = Mage::helper('kega_retargeting')->loadCurrentProduct();

		switch ($controllerAction) {
			case 'catalog_category_view':
				$code = 'some pixel code on category listpage: ' . $category->getName();
				break;
			case 'catalog_product_view':
				$code = 'some pixel code on product page: ' . $product->getName();
				break;
			case 'checkout_cart_index':
			case 'Kega_Wizard_wizard_index':
			case 'Kega_Wizard_wizard_delivery':
			case 'Kega_Wizard_wizard_payment':
				$code = 'some pixel code in the checkout wizard';
				break;
			default:
				$code = 'some default pixel code';
				break;
		}

		return $code;
	}

}