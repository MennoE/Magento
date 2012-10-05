<?php
/**
 * Directories
 *
 * @category   Kega
 * @package    Kega_URapidFlow
 */
class Kega_URapidFlow_Block_Adminhtml_Form_Field_Directories extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{

    public function __construct()
    {
        $this->addColumn('directory', array(
            'label' => Mage::helper('core')->__('Directory path'),
            'style' => 'width:350px',
        ));

        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('core')->__('Add Directory');

        parent::__construct();
    }


}
