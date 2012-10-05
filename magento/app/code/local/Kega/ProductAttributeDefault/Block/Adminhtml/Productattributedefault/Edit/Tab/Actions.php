<?php
/**
 *
 * @category Kega
 * @package  Kega_ProductAttributeDefault
 */
?>
<?php
class Kega_ProductAttributeDefault_Block_Adminhtml_ProductAttributeDefault_Edit_Tab_Actions extends Mage_Adminhtml_Block_Template
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('kega_productattributedefault/productattributedefault/edit/tab/actions.phtml');
    }

    protected function _prepareLayout()
    {
    	$categoryAddIds = $this->getProductAttributeDefaultModel()->getCategoryAddId();
    	if (!is_array($categoryAddIds)) {
            $categoryAddIds = array();
        }
        
    	$categoryRemoveIds = $this->getProductAttributeDefaultModel()->getCategoryRemoveId();
    	if (!is_array($categoryRemoveIds)) {
            $categoryRemoveIds = array();
        }
       
    	
        $this->setChild( 'categories_add',
            $this->getLayout()
                ->createBlock('kega_productattributedefault/adminhtml_productattributedefault_edit_tab_categories')
                ->setCategoryIds($categoryAddIds)
                ->setTemplate('kega_productattributedefault/productattributedefault/edit/tab/categories_add.phtml')
                ->setProductAttributeDefaultModel($this->getProductAttributeDefaultModel()));

        $this->setChild( 'categories_remove',
            $this->getLayout()
                ->createBlock('kega_productattributedefault/adminhtml_productattributedefault_edit_tab_categories')
                ->setCategoryIds($categoryRemoveIds)
                ->setTemplate('kega_productattributedefault/productattributedefault/edit/tab/categories_remove.phtml')
                ->setProductAttributeDefaultModel($this->getProductAttributeDefaultModel()));        

        $this->setChild( 'attributes',
            $this->getLayout()
                ->createBlock('kega_productattributedefault/adminhtml_productattributedefault_edit_tab_attributes')
                ->setProductAttributeDefaultModel($this->getProductAttributeDefaultModel()));
                
        $this->setChild( 'attributes_dynamic',
            $this->getLayout()
                ->createBlock('kega_productattributedefault/adminhtml_productattributedefault_edit_tab_attributesdynamic')
                ->setProductAttributeDefaultModel($this->getProductAttributeDefaultModel()));        
                
        return parent::_prepareLayout();
    }



}
