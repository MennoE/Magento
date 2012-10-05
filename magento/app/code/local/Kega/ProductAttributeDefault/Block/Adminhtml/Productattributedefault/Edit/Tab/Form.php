<?php
/**
 *
 * @category Kega
 * @package  Kega_ProductAttributeDefault
 */
?>
<?php
class Kega_ProductAttributeDefault_Block_Adminhtml_Productattributedefault_Edit_Tab_Form extends Mage_Adminhtml_Block_Template
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('kega_productattributedefault/productattributedefault/edit/tab/form.phtml');
    }


    public function getStatusOptions()
    {
        return Mage::getModel('kega_productattributedefault/productattributedefault')->getStatusOptions();
    }


    public function getDryRunOptions()
    {
        return Mage::getModel('kega_productattributedefault/productattributedefault')->getDryRunOptions();
    }



    /**
     * Gets Form Values (uses default values if there are no values entered previously)
     *
     * @return array
     */
    public function getFormValues()
    {
        $formDefaultValues = array(
            'category_id' => array(),
            'attribute_name' => '',
            'rule_name' => '',
            'rules' =>  array(),
            'is_enabled' => 0,
            'dry_run' => 0,
        	'overwrite_product_manual_changes' => 0,
        	'apply_to_stores' => array(),
            'product_types' => array(),
        );

        $formValues = $formDefaultValues;

        $productAttributeDefaultModel = $this->getProductAttributeDefaultModel();

        if ($productAttributeDefaultModel) {
            $formValues = array_merge($formDefaultValues, $productAttributeDefaultModel->getData());
        }

        return $formValues;
    }

    public function getProductAttributeDefaultModel()
    {
        return Mage::registry('product_attribute_default');
    }

    public function getAttributesUrl()
    {
        $productAttributeDefaultModel = $this->getProductAttributeDefaultModelModel();

        if ($productAttributeDefaultModel) {
            return $this->getUrl('*/*/attributes', array('id' => $productAttributeDefaultModel->getId()));
        }

        return $this->getUrl('*/*/attributes');
    }
    
    
    public function getProductTypesOptions()
    {
        return Mage::getModel('catalog/product_type')->getOptionArray();
    }

}
