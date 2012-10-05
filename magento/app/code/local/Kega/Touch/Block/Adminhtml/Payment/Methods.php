<?php
class Kega_Touch_Block_Adminhtml_Payment_Methods
    extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected $_addRowButtonHtml = array();
    protected $_removeRowButtonHtml = array();

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);

        $html = '<select name="' . $this->getElement()->getName() . '" id="' . $this->getElement()->getId() . '">';
        $html .= '<option value="">' . $this->__('*Select payment method') . '</option>';
        foreach ($this->getMethods() as $paymentCode => $paymentModel) {
            $html .= '<option value="' . $paymentCode . '" ' . ($this->getElement()->getValue() == $paymentCode ? 'selected="selected"' : '') . ' style="background:white;">' . $paymentModel->getTitle() . '</option>';
        }
        $html .= '</select>';

        return $html;
    }

    protected function getMethods()
    {
        if (!$this->hasData('payments')) {
			$payments = Mage::getSingleton('payment/config')->getActiveMethods();
			$this->setData('payments', $payments);
        }
        return $this->getData('payments');
    }
}