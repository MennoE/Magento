<?php
/**
 *
 * @category Kega
 * @package  Kega_ProductAttributeDefault
 */
?>
<?php
class Kega_ProductAttributeDefault_Block_Adminhtml_ProductAttributeDefault_Edit_Tab_Rules extends Mage_Adminhtml_Block_Template
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('kega_productattributedefault/productattributedefault/edit/tab/rules.phtml');
    }
    
    
	public function getDayOptions()
    {
        return Mage::getModel('kega_productattributedefault/productattributedefault')->getDayOptions();
    }
    
    public function getDateOperatorOptions()
    {
        return Mage::getModel('kega_productattributedefault/productattributedefault')->getDateOperatorOptions();
    }
    
    public function getDefaultOperatorOptions()
    {
        return Mage::getModel('kega_productattributedefault/productattributedefault')->getDefaultOperatorOptions();
    }


    public function getOperatorOptions()
    {
        return Mage::getModel('kega_productattributedefault/productattributedefault')->getOperatorOptions();
    }

    public function getOperatorsValidAttrCodeAttrCodeOptions()
    {
        return Mage::getModel('kega_productattributedefault/productattributedefault')->getOperatorsValidAttrCodeAttrCodeOptions();
    }
    
	public function getOperatorsValidAttrCodeCategoryIdOptions()
    {
        return Mage::getModel('kega_productattributedefault/productattributedefault')->getOperatorsValidAttrCodeCategoryIdOptions();
    }


    public function getProductAttributesValues()
    {

        $values = Mage::helper('kega_productattributedefault')->getProductAttributesOptions();

        $additionalProductAttributeOptions = array(
            //this is not a real product attribute, we use some random value for id
            'category_id' => array(
                'attribute_id' => rand(100000,1000000),
                'code' => 'allow_message',
                'type' => 'text',
                'name' => 'category_id',
            ),
        );

        $values = array_merge($values, $additionalProductAttributeOptions);

        ksort($values);

        return $values;
    }

    public function getFormValues()
    {

        $formDefaultValues = array(
            'category_id' => array(),
            'rule_name' => '',
            'is_enabled' => 0,
            'dry_run' => 0,
            'rules' =>  array(),
        );

        $formValues = $formDefaultValues;

        $productAttributeDefaultModel = $this->getProductAttributeDefaultModel();

        if ($productAttributeDefaultModel) {
            $formValues = array_merge($formDefaultValues, $productAttributeDefaultModel->getData());
        }

        return $formValues;
    }





}
