<?php
/**
 *
 * @category Kega
 * @package  Kega_ProductAttributeDefault
 */
?>
<?php
class Kega_ProductAttributeDefault_Block_Adminhtml_Productattributedefault_Grid_Renderer_Attributes
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

        $attributes = $productAttributeDefaultModel->getData('attributes');
        
        $attributeNames = array();

        foreach ($attributes as $attributeData) {
            $attribute = Mage::getSingleton('eav/config')->getAttribute('catalog_product', $attributeData['attribute_code']);
            
            if (!$attribute) continue;

            if ($attribute->usesSource()) {
                $options = $attribute->getSource()->getAllOptions(false);

                $selectedOptions = array();
                foreach ($options as $option) {
                    if ($option['value'] == $attributeData['attribute_value']) {
                        $rowHtml .= '<span class="filter-attribute-label">';
                        $rowHtml .= '<strong>' . $attribute->getName() . '</strong>: '. $option['label'];
                        $rowHtml .= '</span>';
                    }                    
                }
            } else {
                $rowHtml .= '<span class="filter-attribute-label">';
                $rowHtml .= '<strong>' . $attribute->getName() . '</strong>: '. $attributeData['attribute_value'];
                $rowHtml .= '</span>';
            }

            $rowHtml .= '<br />';
            
        }

        return $rowHtml;
    }
}