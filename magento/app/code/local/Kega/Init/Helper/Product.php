<?php
/**
 * Frontend helper
 *
 * Various methods to make repeating product related tasks easier
 * @todo Remove Shoeby specific code.
 */
class Kega_Init_Helper_Product extends Mage_Core_Helper_Abstract
{

    const BRANDLOGO_PATH = 'catalog/externalimage/brand_image_path';
    const BRANDLOGO_EXTENSION = '.png';
    const BRANDLOGO_PREFIX = 'catalog/externalimage/brand_image_prefix';
    const COLOR_SKU_PATTERN = 'extrasettings/color_options/pattern';
    const COLOR_SKU_CURRENT = 'extrasettings/color_options/current';

    /**
     * Get labels for product
     *
     * @param Mage_Catalog_Model_Product $product
     * @return Array
     */
    public function getLabels($product)
    {
        $attribute = $product->getResource()->getAttribute('labels');
        $labelIds = explode(',', $product->getLabels());
        $labels = array();

        foreach($labelIds as $labelId) {

        	if(!is_numeric($labelId)) {
        		continue;
        	}
            $label = Mage::getResourceModel('eav/entity_attribute_option_collection')
                        ->setPositionOrder('asc')
                        ->setAttributeFilter($attribute->getId())
                        ->setIdFilter($labelId)
                        ->setStoreFilter()
                        ->load()
                        ->getFirstItem();
            $labels[] = $label;
        }

        return $labels;
    }

    /**
     * Get similar products in other color based on SKU
     *
     * @param String $sku
     * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection
     */
    public function getColorRelatedProducts($sku)
    {
        $pattern = Mage::getStoreConfig(self::COLOR_SKU_PATTERN);
        preg_match($pattern, $sku, $matches);

        if(empty($matches)) {
            return array();
        }

        $resource = new Mage_Core_Model_Resource();
        $read = $resource->getConnection('core_read');

        $select = $read->select()
            ->from(array('e'=>$resource->getTableName('catalog/product')), 'entity_id')
            ->where("e.sku LIKE '" . $matches[1] . "%'")
            ->where("e.type_id = 'configurable'");

        // check if we want to show the current color in the color options
        $showCurrentColor = Mage::getStoreConfig(self::COLOR_SKU_CURRENT);
        if(!$showCurrentColor) {
            $select->where("e.sku NOT LIKE '" .$sku ."'");
        }

        $entity_ids = $read->fetchAll($select);

        /*$list = array();
        foreach($entity_ids as $id) {
            $list[] = $id['entity_id'];
        }*/

        $list = array();
        //do not add out of stock products
        foreach($entity_ids as $id) {

            $configurableProduct = Mage::getModel('catalog/product')->load($id['entity_id']);

            // if configurable is out of stock don't show
            if (!$configurableProduct->isSaleable()) continue;

            // get only configurable products that have at least one associated product in stock
            $hasStock = false;

            $childProducts = Mage::getModel('catalog/product_type_configurable')
                            ->getUsedProducts(null, $configurableProduct);

            if (is_array($childProducts) && count($childProducts)>0) {
                foreach ($childProducts as $childProduct) {
                    if ($childProduct->getStatus() != Mage_Catalog_Model_Product_Status::STATUS_ENABLED) continue;
                    if ($childProduct->isSaleable() && $childProduct->getStockItem()->getQty()>0) {
                        $hasStock = true;
                        break 1;
                    }
                }
            }

            if ($hasStock) {
                $list[] = $id['entity_id'];
            }
        }

        $_productCollection = array();

        if ($showCurrentColor && count($list) > 1 
              || !$showCurrentColor && count($list) >= 1) {
            $_productCollection = Mage::getModel('catalog/product')->getCollection()
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('entity_id',array('in'=>$list));

            Mage::getSingleton('catalog/product_status')->addSaleableFilterToCollection($_productCollection);
            Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($_productCollection);
            $_productCollection->addStoreFilter()->load();
        }
        return $_productCollection;
    }

	/**
	 * Get Review summary data
	 *
	 * @param Mage_Catalog_Model_Product $product
	 * @return Array
	 */
	public function getReviewSummary($product)
	{
		return Mage::getModel('review/review_summary')
			->setStoreId(Mage::app()->getStore()->getId())
			->load($product->getId());
	}

	/**
	 * Get Review stars html
	 *
	 * @param Mage_Catalog_Model_Product $product
	 * @param array $attributes
	 * @return Kega_Init_Block_Product
	 */
	public function getReviewHtml($product, $attributes = array())
	{
		$rating = $this->getReviewSummary($product);
		$attributes = array_merge(
            array(
                'tag' => 'li',
            	'template' => 'init/product/rating.phtml',
            	'product' => $product,
            	'review_count' => $rating['reviews_count'],
            	'rating_summary' => $rating['rating_summary'],
            ),
            $attributes
        );
        $block = Mage::getSingleton('core/layout')->createBlock(
            'Kega_Init_Block_Product',
            '',
            $attributes
        );
        return $block;
	}

    /**
     * Retrieve the brand logos
     *
     * @param Mage_Catalog_Model_Product $product
     * @param array $attributes
     * @return Kega_Init_Block_Product
     */
    public function retrieveBrandLogo($product)
    {
        if(!$product->getAttributeText('brand')){
            return false;
        }

        $brand = preg_replace(
            "![^a-z0-9]+!i",
            "-",
            $product->getAttributeText('brand')
        );

        if(Mage::app()->getStore()->isCurrentlySecure()) {
            $path = Mage::getStoreConfig('catalog/externalimage/secure_image_path');
        } else {
            $path = Mage::getStoreConfig('catalog/externalimage/image_path');
        }

        $dir = Mage::getStoreConfig(self::BRANDLOGO_PATH);
        $prefix = Mage::getStoreConfig(self::BRANDLOGO_PREFIX);
        $image = $prefix.strtolower($brand).self::BRANDLOGO_EXTENSION;

        $url = $path . $dir . $image;

        if (Mage::helper('init')->curlFileExists($url)) {
            return $url;
        }

        return false;
    }

}
