<?php
class Kega_AutoShipping_Block_Adminhtml_Shipping_Methods
    extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected $_addRowButtonHtml = array();
    protected $_removeRowButtonHtml = array();

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);

        $html = '<select name="' . $this->getElement()->getName() . '" id="autoshipping_settings_method_code">';
        $html .= '<option value="">' . $this->__('* Select shipping method') . '</option>';
        foreach ($this->getShippingMethods() as $carrierCode=>$carrier) {
            $html .= '<optgroup label="' . $carrier['title'] . '" style="border-top:solid 1px black; margin-top:3px;">';
            foreach ($carrier['methods'] as $code=>$method) {
                $methodCode = $carrierCode . '_' . $code;
                $html .= '<option value="' . $methodCode . '" ' . ($this->getElement()->getValue()==$methodCode ? 'selected="selected"' : '') . ' style="background:white;">' . $method['title'] . '</option>';
            }
            $html .= '</optgroup>';
        }
        $html .= '</select>';

        return $html;
    }

    protected function getShippingMethods()
    {
        if (!$this->hasData('shipping_methods')) {
            $website = $this->getRequest()->getParam('website');
            $store   = $this->getRequest()->getParam('store');

            $storeId = null;
            if (!is_null($website)) {
                $storeId = Mage::getModel('core/website')->load($website, 'code')->getDefaultGroup()->getDefaultStoreId();
            } elseif (!is_null($store)) {
                $storeId = Mage::getModel('core/store')->load($store, 'code')->getId();
            }

            $methods = array();
            $carriers = Mage::getSingleton('shipping/config')->getActiveCarriers($storeId);
            foreach ($carriers as $carrierCode=>$carrierModel) {
                if (!$carrierModel->isActive()) {
                    continue;
                }
                $carrierMethods = $carrierModel->getAllowedMethods();
                if (!$carrierMethods) {
                    continue;
                }
                $carrierTitle = Mage::getStoreConfig('carriers/'.$carrierCode.'/title', $storeId);
                $methods[$carrierCode] = array(
                    'title'   => $carrierTitle,
                    'methods' => array(),
                );
                foreach ($carrierMethods as $code=>$methodTitle) {
                    $methods[$carrierCode]['methods'][$code] = array(
                        'title' => '['.$code.'] '.$methodTitle,
                    );
                }
            }
            $this->setData('shipping_methods', $methods);
        }
        return $this->getData('shipping_methods');
    }
}