<?php
/**
 *
 * @category Kega
 * @package  Kega_ProductAttributeDefault
 */
?>
<?php
class Kega_ProductAttributeDefault_Block_Adminhtml_Productattributedefault_Grid_Renderer_Attributesdynamic
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    /**
     * Renders grid column category_filters
     *
     * @param   Varien_Object $row
     * @return  string
     */
    public function render(Varien_Object $row)
    {
        
        $rowHtml = '';

        // the category data is not in the collection because it's added only when a record is loaded
        $productAttributeDefaultModel = Mage::getModel('kega_productattributedefault/productattributedefault')->load($row->getId());

        $attributes = $productAttributeDefaultModel->getData('attributes_dynamic');

        foreach ($attributes as $attributeData) {
            $attribute = Mage::getSingleton('eav/config')->getAttribute('catalog_product', $attributeData['attribute_code']);
            
            if (!$attribute) continue;
            
            $attributeName = $attribute->getName();
			$product = Mage::getModel('catalog/product');
            $attributeDynamicValuesData = Mage::helper('kega_productattributedefault')->getDynamicAttributeSection($product, $attributeData['attribute_value']);
            $pattern = $attributeData['attribute_value'];
            // replace section name with attribute name
            foreach ($attributeDynamicValuesData as $sectionName => $attributeDynamicCode) {
            	$attributeDynamic = Mage::getSingleton('eav/config')->getAttribute('catalog_product', $attributeDynamicCode);
            		            		
            	if (!$attributeDynamic) continue;
            		
            	$pattern = str_replace($sectionName, '<em>{'.$attributeDynamic->getName().'}</em>', $pattern);
            }
            
            $rowHtml .= '<strong>' . $attributeName. '</strong>: ' . $pattern;

            $rowHtml .= '<br />';            
        }

        return $rowHtml;
    }
}