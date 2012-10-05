<?php
class Kega_ExternalImage_Model_Mysql4_Catalog_Product extends Mage_Catalog_Model_Resource_Eav_Mysql4_Product
{
	/**
	 * Kega_ExternalImage_Model_Mysql4_Catalog_Product::getExternalImageCollection()
	 * Retrieve all external images related to the current product
	 *
	 * @access public
	 * @param $object Mage_Catalog_Model_Product
	 * @return Mage_Catalog_Model_Product
	 */
	public function getExternalImageCollection($object)
	{
		$select = $this->_getReadAdapter()->select()
			->from(array('ext'=>$this->getTable('externalimage/gallery')),
				array('id'=>'ext.externalimage_id', 'name'=>'ext.image_name')
			)
			->where("ext.product_id = ?", $object->getId());

		return $this->_getReadAdapter()->fetchAssoc($select);
	}

	/**
	 * Kega_ExternalImage_Model_Mysql4_Catalog_Product::getExternalImageName()
	 * Returns the external image for the given product by number
	 *
	 * @param $object Mage_Catalog_Model_Product
	 * @return Mage_Catalog_Model_Product
	 */
	public function getExternalImageName($product, $number = 1)
	{
		$select = $this->_getReadAdapter()->select()
			->from(array('ext'=>$this->getTable('externalimage/gallery')),
				array('id'=>'ext.externalimage_id', 'name'=>'ext.image_name')
			)
			->where("ext.product_id = ?", $product->getId())
			->where("ext.is_main = ?", $number);

		return $this->_getReadAdapter()->fetchRow($select);
	}
}