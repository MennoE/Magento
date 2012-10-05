<?php
/**
 *
 * @category Kega
 * @package  Kega_ProductAttributeDefault
 */
?>
<?php
class Kega_ProductAttributeDefault_Block_Adminhtml_ProductAttributeDefault_Edit_Tab_Attributesdynamic extends Mage_Adminhtml_Block_Template
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('kega_productattributedefault/productattributedefault/edit/tab/attributes_dynamic.phtml');
    }


    public function getProductAttributesValues()
    {
        $values = Mage::helper('kega_productattributedefault')->getProductAttributesOptions(array('static_and_dynamic'));

        return $values;
    }

    public function getFormValues()
    {        
        $productAttributeDefaultModel = $this->getProductAttributeDefaultModel();

        $formValues = $productAttributeDefaultModel->getData('attributes_dynamic');
        
        if (!is_array($formValues)) return array();

        return $formValues;
    }
    
    
    public function getDynamicVariables()
    {
    	$variables = array();    	
    	
    	$variables[0] = array(
    		'label' => 'Attributes',
    		'value' => array(),
    	);
    	
    	$attributeValues = Mage::helper('kega_productattributedefault')->getProductAttributesOptions();
    	foreach ($attributeValues as $attributeValue) {
    		$variables[0]['value'][] = array(
    			'value' => "{{prod_attr value='".$attributeValue['code']."'}}",
        		'label' => $attributeValue['name'],
    		);
    	} 

    	return $variables;
    }





}
