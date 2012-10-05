<?php

class Kega_ExternalImage_Helper_Catalog_Image extends Mage_Catalog_Helper_Image
{
	/**
	 * Kega_ExternalImage_Helper_Catalog_Image::$_externalFileName
	 * Name of the current external image that is being handled
	 *
	 * @access protected
	 */
	protected $_externalFileName = false;

	/**
	 * Kega_ExternalImage_Helper_Catalog_Image::init()
	 * Retrieves the location of the
	 *
	 * @access public
	 * @param $product Mage_Catalog_Model_Product
	 * @param $attributeName string
	 * @param $imageFile string
	 * @return Kega_ExternalImage_Helper_Catalog_Image
	 */
	public function init(Mage_Catalog_Model_Product $product, $attributeName, $imageFile=null)
	{
		if(Mage::app()->getStore()->isCurrentlySecure()) {
			$path = Mage::app()->getStore()->getConfig('catalog/externalimage/secure_image_path');
		} else {
			$path = Mage::app()->getStore()->getConfig('catalog/externalimage/image_path');
		}

		if ($imageFile && strpos($imageFile, trim($path,'/')) !== false) {
			$this->_externalFileName = strtolower(substr($imageFile, strrpos($imageFile, '/')+1));
		}
		parent::init($product, $attributeName, $imageFile);
		return $this;
	}

	/**
	 * Kega_ExternalImage_Helper_Catalog_Image::init()
	 * Retrieves the external location of the image, uses default magento as fallback
	 *
	 * @access public
	 * @return Kega_ExternalImage_Helper_Catalog_Image
	 */
	public function __toString()
	{
		// Skip this part when no image_path was found
		if (!trim(Mage::app()->getStore()->getConfig('catalog/externalimage/image_path'))) {
			return parent::__toString();
		}

		$type = $this->_getModel()->getDestinationSubdir();
		$product = $this->getProduct();

		if ($this->_externalFileName) {
			$url = $this->getExternalUrl(
				$product,
				$type,
				$this->_externalFileName
			);
		}
		else {
			if ($url = $this->getExternalUrl($product, $type)) {
				$this->getProduct()->setImage($url);
			}
			else {
				$url = parent::__toString();
			}
		}

		return $url;
	}

	/**
	 * Kega_ExternalImage_Helper_Catalog_Image::getExternalUrl()
	 * Retrieves the location of the
	 *
	 * @access public
	 * @param $product Mage_Catalog_Model_Product
	 * @param $imgType string
	 * @param $imageName string
	 * @return string
	 */
	public function getExternalUrl($product, $imgType, $imageName = null)
	{
		//$baseUrl = Mage::app()->getStore()->getConfig('catalog/externalimage/image_path');
		if(Mage::app()->getStore()->isCurrentlySecure()) {
			$baseUrl = Mage::app()->getStore()->getConfig('catalog/externalimage/secure_image_path');
		} else {
			$baseUrl = Mage::app()->getStore()->getConfig('catalog/externalimage/image_path');
		}

		if (is_null($imageName)) {
			$external = $product->getResource()->getExternalImageName($product);
			$imageName = $external['name'];
		}
		return is_null($imageName) ? false : ($baseUrl . $imgType . '/' . $imageName);
	}

	/**
	 * Kega_ExternalImage_Helper_Catalog_Image::getImageByType()
	 * Retrieves the location of the external by type
	 *
	 * @access public
	 * @param $product Mage_Catalog_Model_Product
	 * @param $imgType string
	 * @param $defaultImgType string
	 * @return string
	 */
	public function getImageByType($product, $imgType, $defaultImgType = 'small_image')
	{
		$result = $this->init($product, $defaultImgType)->__toString();

		$external = $product->getResource()->getExternalImageName($product);

		if (isset($external['name']))
		{
			$baseUrl = Mage::app()->getStore()->getConfig('catalog/externalimage/image_path');
			$result = $baseUrl . $imgType . '/' . $external['name'];
		}

		return $result;
	}
}