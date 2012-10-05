<?php
/**
 *
 * @category Kega
 * @package  Kega_ProductAttributeDefault
 */
?>
<?php
class Kega_ProductAttributeDefault_Block_Adminhtml_Productattributedefault_Grid_Renderer_Categories
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

        $rowHtml = '';

        // the category data is not in the collection because it's added only when a record is loaded
        $productAttributeDefaultModel = Mage::getModel('kega_productattributedefault/productattributedefault')->load($row->getId());

        $categoryIds = $productAttributeDefaultModel->getData('category_add_id');
        
        $categoryNames = array();

        foreach ($categoryIds as $categoryId) {
            $category = Mage::getModel('catalog/category')->load($categoryId);
            if ($category->getParentCategory()) {
                $categoryNames[] = $category->getParentCategory()->getName().'/'.'<strong>'.$category->getName().'</strong>'.' <em>(id '.$category->getId().')</em>';
            } else {
                $categoryNames[] = '<strong>'.$category->getName().'</strong>'.' <em>(id '.$category->getId().')</em>';
            }
        }
        
        $rowHtml .= '<strong>'.Mage::helper('kega_productattributedefault')->__('Add').':</strong><br />';
        $rowHtml .= implode($categoryNames, ', ');
        $rowHtml .= '<br />';
        
        $categoryIds = $productAttributeDefaultModel->getData('category_remove_id');
        
        $categoryNames = array();

        foreach ($categoryIds as $categoryId) {
            $category = Mage::getModel('catalog/category')->load($categoryId);
            if ($category->getParentCategory()) {
                $categoryNames[] = $category->getParentCategory()->getName().'/'.'<strong>'.$category->getName().'</strong>'.' <em>(id '.$category->getId().')</em>';
            } else {
                $categoryNames[] = '<strong>'.$category->getName().'</strong>'.' <em>(id '.$category->getId().')</em>';
            }
        }
        
        $rowHtml .= '<strong>'.Mage::helper('kega_productattributedefault')->__('Remove').':</strong><br />';
        $rowHtml .= implode($categoryNames, ', ');

        return $rowHtml;
    }
}
?>