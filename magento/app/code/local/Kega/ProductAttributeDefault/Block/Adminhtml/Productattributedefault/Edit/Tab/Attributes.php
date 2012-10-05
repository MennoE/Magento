<?php
/**
 *
 * @category Kega
 * @package  Kega_ProductAttributeDefault
 */
?>
<?php
class Kega_ProductAttributeDefault_Block_Adminhtml_ProductAttributeDefault_Edit_Tab_Attributes extends Mage_Adminhtml_Block_Template
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('kega_productattributedefault/productattributedefault/edit/tab/attributes.phtml');
    }


    public function getProductAttributesValues()
    {

        $values = Mage::helper('kega_productattributedefault')->getProductAttributesOptions(array('static', 'static_and_dynamic'));

        return $values;
    }

    public function getFormValues()
    {
        
        $productAttributeDefaultModel = $this->getProductAttributeDefaultModel();

        $formValues = $productAttributeDefaultModel->getData('attributes');
        
        if (!is_array($formValues)) return array();

        return $formValues;
    }





}
