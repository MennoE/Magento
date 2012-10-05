<?php

/**
 * Catalog helper
 *
 * Various methods to make repeating catalog tasks easier
 * Methods for product specific tasks can be put in Product helper.
 */
class Kega_Init_Helper_Catalog extends Mage_Core_Helper_Abstract
{
	private $_adminColorLabels;

    /**
     * Retrieve the category content for the given category id
     *
     * @param Integer $product
     * @return String
     */
	public function getCategoryContent($categoryId)
	{
		$category = Mage::getModel('catalog/category')->load($categoryId);
		if ($category->getId()) {
			return $category->getDescription();
		}
	}

    /**
     * Build product list page html
     *
     * @param Mage_Catalog_Model_Product $product
     * @param array $attributes
     */
    public function getProductListHtml($product, $attributes)
    {
        $attributes = array_merge(
            array(
                'tag' => 'li',
            	'color_options' => true,
            	'show_name' => true,
            	'show_labels' => true,
            	'image_size' => 'medium_image',
            	'template' => 'init/catalog/product-list.phtml',
            	'product' => $product
            ),
            $attributes
        );

        $block = Mage::getSingleton('core/layout')->createBlock(
            'Kega_Init_Block_Catalog',
            '',
            $attributes
        );
        return $block;
    }

	/**
     * Get pricehtml on Mage_Catalog_Block_Product_List
     *
     * @see Mage_Catalog_Block_Product_List
     * @param Mage_Catalog_Model_Product $product
     * @param boolean $displayMinimalPrice
     * @param String $idSuffix
     */
    public function getPriceHtml($product, $displayMinimalPrice = false, $idSuffix='')
    {
        $block = Mage::getSingleton('core/layout')->createBlock(
            'Mage_Catalog_Block_Product_List',
            ''
        );
        return str_replace('US$', '', $block->getPriceHtml($product, $displayMinimalPrice, $idSuffix));
    }

	/**
     * Get similar products with other colors by product
     *
     * @param Mage_Catalog_Model_Product $product
     * @return Array
     */
    public function getColorOptions($product)
    {

    	//Needs to be changed based on customer requirements. Example:
    	/*
        $colorIds = $product->getUpSellProductIds();

        $colors = array();
        foreach($colorIds as $colorId) {
            $colors[] = Mage::getModel('catalog/product')->load($colorId);
        }
        return $colors;
        */
    	return array();
    }

    /**
     * Get category thumbnail image url
     *
     * @return String
     */
    public function getCategoryThumbUrl()
    {
        return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'catalog/category/';
    }

    /**
     * Retrieve CMS Block data and return this data.
     *
     * @return Array
     */
    public function getCmsBlockData($identifier)
    {
        $store = Mage::app()->getStore();

        $store = Mage::app()->getStore();
        $block = Mage::getModel('cms/block')->setStoreId($store->getId())
            ->load(Mage::app()->getLayout()->createBlock('cms/block')
            ->setBlockId($identifier)->getBlockId());

        return $block;
    }

    /**
     * Return child categories
     *
     * Created a custom function of this, because the core getChildCategories
     * don't include a descripion. In some cases we need this description.
     *
     * @param Mage_Catalog_Model_Category $category
     * @return unknown
     */
    public function getCustomChildCategories($category)
    {
        $collection = $category->getCollection();
        /* @var $collection Mage_Catalog_Model_Resource_Eav_Mysql4_Category_Collection */
        $collection->addAttributeToSelect('url_key')
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('all_children')
            ->addAttributeToSelect('is_anchor')
            ->addAttributeToSelect('description')
            ->addAttributeToFilter('is_active', 1)
            ->addIdFilter($category->getChildren())
            ->setOrder('position', 'ASC')
            ->load();
        return $collection;
    }

    /**
     * Get the parsed content
     * Magento variables (eg {{media}} ) will be parsed correctly
     *
     * @param html to be formatted
     * @return parsed html
     */
    public function getParsedContent($content)
    {
        $cmsHelper = Mage::helper('cms');
        $processor = $cmsHelper->getBlockTemplateProcessor();

        return $processor->filter($content);
    }

	/**
     * Get color chart filled with config hex colors.
     * The returned array is an array with the frontend option label
     * as index, and the color hex code from the config as value.
     *
     * @param int $itemId Option ID of attribute
     * @return array $colorChart
     */
    public function getColorChart()
    {
    	$colorChart = array();

    	$attributeCode = 'color';
    	$options = $colors = array();

    	$colorConfig = unserialize(Mage::getStoreConfig('extrasettings/catalog_filters/color_chart'));
    	foreach ($colorConfig as $color) {
    		$colors[$color['attribute_code']] = $color['color_hex'];
    	}

		$attribute = Mage::getModel('eav/config')->getAttribute('catalog_product', $attributeCode);
		$attributeOptions = $attribute->getSource()->getAllOptions();

		foreach ($attributeOptions as $option) {
			if ($option['value']) {
				$adminLabel = $this->_getAttributeAdminLabel($attributeCode, $option['value']);
				$color= array_key_exists($adminLabel, $colors) ? $colors[$adminLabel] : false;
				$colorChart[$option['label']] = $color;
			}
		}

    	return $colorChart;
    }

    /**
     * Get admin label of attribute by attribute code and option ID
     *
     * @param string $attributeCode Code of attribute
     * @param string $attributeValueId Option ID of attribute
     * @return string|bool Admin label of attribute option, false if none is found
     */
    private function _getAttributeAdminLabel($attributeCode, $attributeValueId)
    {
		if (is_null($this->_adminColorLabels)) {
			$attribute = Mage::getResourceModel('eav/entity_attribute_collection')->setCodeFilter($attributeCode)->getFirstItem();

			$_collection = Mage::getResourceModel('eav/entity_attribute_option_collection')
				->setStoreFilter(0)
				->setAttributeFilter($attribute->getId())
				->load();

			foreach ($_collection->toOptionArray() as $option) {
				$this->_adminColorLabels[$option['value']] = $option['label'];
			}
		}

		return array_key_exists($attributeValueId, $this->_adminColorLabels) ? $this->_adminColorLabels[$attributeValueId] : false;
	}

    /**
     * Get category URL of category defined in config
     *
     * @param string $path Config path of category
     * @return string Url of category
     */
    public function getCustomCategoryUrl($path)
    {
    	$categoryId = Mage::getStoreConfig('extrasettings/category_ids/' . $path);
    	$category = Mage::getModel('catalog/category')->load($categoryId);

		return Mage::getBaseUrl() . $category->getUrlPath();
    }

	/**
     * Getting the product specific labels from the image
     * server to display on list or detail page.
     */
     public function getLabelsFromImageserve($labelValue)
     {
		$labelUrl = Mage::getStoreConfig('catalog/externalimage/label_path');
		$labelExtension = Mage::getStoreConfig('catalog/externalimage/label_extension');

		$labelValue = preg_replace("![^a-z0-9]+!i", "-", strtolower($labelValue));

		$labelImage = $labelUrl.$labelValue.$labelExtension;

		if (Mage::helper('init')->curlFileExists($labelImage)) {
            return $labelImage;
        }

		return $labelUrl.$labelValue.$labelExtension;
     }
}