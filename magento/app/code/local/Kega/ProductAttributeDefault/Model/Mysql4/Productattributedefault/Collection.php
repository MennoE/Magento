<?php
/**
 *
 * @category Kega
 * @package  Kega_ProductAttributeDefault
 */
?>
<?php
class Kega_ProductAttributeDefault_Model_Mysql4_Productattributedefault_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{

    public function _construct()
    {
        $this->_init('kega_productattributedefault/productattributedefault');
    }


    /**
     * Adds the category name to the collection
     *
     * @return Kega_LayeredNavSeo_Model_Mysql4_Layerednavseo_Collection
     */
    public function joinCategory()
    {
    	$resource = Mage::getSingleton('core/resource');
    	$category_table = $resource->getTableName('catalog/category');

    	$categoryResource = Mage::getResourceSingleton('catalog/category');
    	$nameAttr = $categoryResource->getAttribute('name');
    	$nameAttrId = $nameAttr->getAttributeId();

    	$nameAttrTable = $nameAttr->getBackend()->getTable();

        // we also add the store id as a filter because we could have multiple
        // entries for the same category (different stores)
    	$this->getSelect()->joinInner(
        	array('_table_category_name' => $nameAttrTable),
            '_table_category_name.entity_id = main_table.category_id
                AND _table_category_name.store_id = 0
                AND _table_category_name.attribute_id = '.(int)$nameAttrId, array())
            ->from("",array(
                        'category_name' => "_table_category_name.value",
                        )
            );

        return $this;
    }



}
