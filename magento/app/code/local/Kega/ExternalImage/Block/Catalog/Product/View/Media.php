<?php
class Kega_ExternalImage_Block_Catalog_Product_View_Media extends Mage_Catalog_Block_Product_View_Media
{
	/**
	 * Kega_ExternalImage_Block_Catalog_Product_View_Media::getProduct()
	 * Overwrite product image w/ external image location when external image_path is available
	 *
	 * @access public
	 * @return Mage_Catalog_Model_Product
	 */
	public function getProduct()
    {
    	$product = parent::getProduct();

        // Skip this part when no image_path was found
		if (!trim(Mage::app()->getStore()->getConfig('catalog/externalimage/image_path'))) {
			return $product;
		}

    	$product->setImage($this->helper('catalog/image')->getExternalUrl($product, 'image'));
    	$product->setThumbnail($this->helper('catalog/image')->getExternalUrl($product, 'thumbnail'));

    	return $product;
    }

}