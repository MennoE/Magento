<?php
class Kega_Touch_Block_Adminhtml_Core_Stores
    extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected $_addRowButtonHtml = array();
    protected $_removeRowButtonHtml = array();

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);

        $html = '<select name="' . $this->getElement()->getName() . '" id="' . $this->getElement()->getId() . '">';
        $html .= '<option value="">' . $this->__('*Select store') . '</option>';
        foreach ($this->getStores() as $store) {
            $html .= '<option value="' . $store['value'] . '" ' . ($this->getElement()->getValue() == $store['value'] ? 'selected="selected"' : '') . ' style="background:white;">' . $store['label'] . '</option>';
        }
        $html .= '</select>';

        return $html;
    }

    protected function getStores()
    {
        if (!$this->hasData('stores')) {
            $stores = Mage::getModel('core/store')->getCollection()->toOptionArray();
            $this->setData('stores', $stores);
        }
        return $this->getData('stores');
    }
}