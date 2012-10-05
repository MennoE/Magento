<?php
/**
 * Extra Attributes config element
 *
 */
class Kega_ProductAttributeDefault_Block_Adminhtml_System_Config_Form_Field_Extraattributes 
	extends Mage_Adminhtml_Block_System_Config_Form_Field
{
        
    public function getSelectOptions()
    {
        $selectOptions = array(
            array(
                'label' => $this->__('show all'),
                'value' => 'show_all',
            ),
            array(
                'label' => $this->__('show none'),
                'value' => 'show_none',
            ),
            array(
                'label' => $this->__('show only selected type'),
                'value' => 'show_selected_type',
            ),
        );        
        return $selectOptions;
    }
    
    public function getSelectedTypeOptions()
    {
        $selectOptions = array(
            array(
                'label' => $this->__('date'),
                'value' =>'date',
            ),
            array(
                'label' => $this->__('text'),
                'value' =>'text',
            ),
            array(
                'label' => $this->__('textarea'),
                'value' =>'textarea',
            ),
        );        
        return $selectOptions;
    }

 	protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {    	
        
        $selectOptions = $this->getSelectOptions();
        $selectTypeOptions = $this->getSelectedTypeOptions();

        $value = $element->getValue();

        if( empty($value) || !is_array($value) ) {
        	$value['option'] = '';
        	$value['details'] = '';
        }
        
        $html = '';
        
        $html.= '<select name="'. $element->getName() . '[option]" style="width: 150px" id="extraattributes_option">'."\n";
        $html.= '<option value=""> --' . $this->__('choose one') . '-- </option>';
        foreach($selectOptions as $selectOption) {            
            $html.= '<option value="'.$selectOption['value'].'" '. ( ($value['option'] == $selectOption['value']) ? 'selected="selected"' : '' ) .'>' . $selectOption['label'] . '</option>';
        }
        $html.= '</select>'."\n";

        $html .= '&nbsp;&nbsp;<select name="'. $element->getName() . '[details][]" id="extraattributes_option_details" multiple="multiple" size="3" style="width: 100px;vertical-align: top; visibility: hidden;">'."\n";
        foreach($selectTypeOptions as $selectOption) {            
            $html.= '<option value="'.$selectOption['value'].'" '. ( (is_array($value['details'])
                                                                      && in_array($selectOption['value'], $value['details'])) ?
                                                                    'selected="selected"' : '' ) .'>' . $selectOption['label'] . '</option>';
        }
        $html.= '</select>'."\n";
        
        $html .= "
        <script type=\"text/javascript\">
        function showOptionDetails() {
            if($('extraattributes_option').value == 'show_selected_type') {
                $('extraattributes_option_details').setStyle({visibility: 'visible'});
            } else {
                $('extraattributes_option_details').setStyle({visibility: 'hidden'});
            }
        }
        
        showOptionDetails();
        $('extraattributes_option').observe('change', showOptionDetails);
        
        </script>
        ";
        
        return $html;
    }

}
