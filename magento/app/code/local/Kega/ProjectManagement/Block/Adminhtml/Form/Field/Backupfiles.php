<?php
/**
 * Adminhtml backupfiles configuration input 
 *
 * @category   Kega
 * @package    Kega_ProjectManagement
 */
class Kega_ProjectManagement_Block_Adminhtml_Form_Field_Backupfiles extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{

    public function __construct()
    {
        $this->addColumn('file_path', array(
            'label' => Mage::helper('core')->__('File Path'),
            'style' => 'width:350px',
        ));

        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('projectmanagement')->__('Add Option');

        parent::__construct();
    }


}
