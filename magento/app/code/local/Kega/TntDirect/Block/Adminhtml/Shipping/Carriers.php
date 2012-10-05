<?php
class Kega_TntDirect_Block_Adminhtml_Shipping_Carriers
    extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);

        $selectedValues = explode(',', $element->getValue());

        $html = '<select class=" select multiselect" multiple="multiple" size="10" name="' . $this->getElement()->getName() . '" id="autoshipping_settings_method_code">';
        foreach ($this->getShippingCarriers() as $carrierCode => $carrierTitle) {
        	if ($carrierCode == 'kega_tnt_direct') {
        		continue;
        	}
            $html .= '<option value="' . $carrierCode . '" ' . (in_array($carrierCode, $selectedValues) ? 'selected="selected"' : '') . '>' . $carrierTitle . '</option>';
        }
        $html .= '</select>';

        return $html;
    }

    protected function getShippingCarriers()
    {
        if (!$this->hasData('shipping_carriers')) {
            $website = $this->getRequest()->getParam('website');
            $store   = $this->getRequest()->getParam('store');

            $storeId = null;
            if (!is_null($website)) {
                $storeId = Mage::getModel('core/website')->load($website, 'code')->getDefaultGroup()->getDefaultStoreId();
            } elseif (!is_null($store)) {
                $storeId = Mage::getModel('core/store')->load($store, 'code')->getId();
            }

            $carriers = array();
            foreach (Mage::getSingleton('shipping/config')->getActiveCarriers($storeId) as $carrierCode => $carrierModel) {
            	$carrierTitle = Mage::getStoreConfig('carriers/' . $carrierCode . '/title', $storeId);
                $carriers[$carrierCode] = (empty($carrierTitle) ? '[' . $carrierCode . ']' : $carrierTitle);
            }

            $this->setData('shipping_carriers', $carriers);
        }
        return $this->getData('shipping_carriers');
    }
}