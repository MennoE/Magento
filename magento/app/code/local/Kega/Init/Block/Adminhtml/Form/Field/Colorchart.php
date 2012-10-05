<?php
/**
 * Adminhtml backupfiles configuration input 
 *
 * @category   Kega
 * @package    Kega_ProjectManagement
 */
class Kega_Init_Block_Adminhtml_Form_Field_Colorchart extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{

    public function __construct()
    {
        $this->addColumn('attribute_code', array(
            'label' => Mage::helper('core')->__('Attribute code'),
            'style' => 'width:150px',
        ));
        $this->addColumn('color_hex', array(
            'label' => Mage::helper('core')->__('Color hex code'),
            'style' => 'width:150px',
        ));

        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('core')->__('Add Color');

        parent::__construct();
    }


}
