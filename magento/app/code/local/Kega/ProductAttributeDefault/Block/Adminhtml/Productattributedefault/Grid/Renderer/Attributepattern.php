<?php
/**
 *
 * @category Kega
 * @package  Kega_ProductAttributeDefault
 */
?>
<?php
class Kega_ProductAttributeDefault_Block_Adminhtml_Productattributedefault_Grid_Renderer_Attributepattern
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    /**
     * Renders grid column categories
     *
     * @param   Varien_Object $row
     * @return  string
     */
    public function render(Varien_Object $row)
    {
        $attributePatternValue = $row->getData('attribute_pattern_value');
        $attributePatternCode = $row->getData('attribute_pattern_code');

        return empty($attributePatternValue)? $attributePatternCode : $attributePatternValue;
    }
}
?>