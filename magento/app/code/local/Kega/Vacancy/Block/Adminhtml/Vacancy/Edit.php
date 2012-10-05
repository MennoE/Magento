<?php

class Kega_Vacancy_Block_Adminhtml_Vacancy_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'vacancy';
        $this->_controller = 'adminhtml_vacancy';
        
        $this->_updateButton('save', 'label', Mage::helper('vacancy')->__('Save Item'));
        $this->_updateButton('delete', 'label', Mage::helper('vacancy')->__('Delete Item'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('vacancy_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'vacancy_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'vacancy_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('vacancy_data') && Mage::registry('vacancy_data')->getId() ) {
            return Mage::helper('vacancy')->__("Edit Item '%s'", $this->htmlEscape(Mage::registry('vacancy_data')->getTitle()));
        } else {
            return Mage::helper('vacancy')->__('Add Item');
        }
    }
}