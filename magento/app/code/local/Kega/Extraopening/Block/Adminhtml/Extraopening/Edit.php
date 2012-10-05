<?php

class Kega_Extraopening_Block_Adminhtml_Extraopening_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'extraopening';
        $this->_controller = 'adminhtml_extraopening';
        
        $this->_updateButton('save', 'label', Mage::helper('extraopening')->__('Save Item'));
        $this->_updateButton('delete', 'label', Mage::helper('extraopening')->__('Delete Item'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('extraopening_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'extraopening_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'extraopening_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('extraopening_data') && Mage::registry('extraopening_data')->getId() ) {
            return Mage::helper('extraopening')->__("Edit Item '%s'", $this->htmlEscape(Mage::registry('extraopening_data')->getTitle()));
        } else {
            return Mage::helper('extraopening')->__('Add Item');
        }
    }
}