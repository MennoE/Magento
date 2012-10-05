<?php

class Kega_ExternalImage_Model_Catalog_Product extends Mage_Catalog_Model_Product
{
	/**
	 * Kega_ExternalImage_Model_Mysql4_Catalog_Product::getMediaGalleryImages()
	 * Retrieve external images for gallery instead of local images
	 *
	 * @access public
	 * @return array
	 */
	public function getMediaGalleryImages()
	{

        $externalImages = $this->_getExternalImageGallery();

		if (!$this->hasData('media_gallery_images') && !empty($externalImages)) {
			$images = new Varien_Data_Collection();

			foreach ($externalImages as $image)	{
				$images->addItem(new Varien_Object($image));
			}
			$this->setData('media_gallery_images', $images);
		}
		else {
			parent::getMediaGalleryImages();
		}

		return $this->getData('media_gallery_images');
	}

	/**
	 * Kega_ExternalImage_Model_Mysql4_Catalog_Product::_getExternalImageGallery()
	 * Retrieve external images for gallery
	 *
	 * @access protected
	 * @return array
	 */
	protected function _getExternalImageGallery()
	{
		$images = $this->getResource()->getExternalImageCollection($this);
		$gallery = array();

		foreach ($images as $image)	{
			$gallery[] = array(
				'id' => $image['id'],
				'is_external' => true,
				'url' => Mage::app()->getStore()->getConfig('catalog/externalimage/image_path') . 'thumbnail/'. $image['name'],
                'zoom' => Mage::app()->getStore()->getConfig('catalog/externalimage/image_path') . 'image/'. $image['name'],
			);
		}

		return $gallery;
	}
}