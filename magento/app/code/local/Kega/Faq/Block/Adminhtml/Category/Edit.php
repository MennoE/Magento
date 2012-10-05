<?php

class Kega_Faq_Block_Adminhtml_Category_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->_objectId = 'id';
        $this->_blockGroup = 'faq';
        $this->_controller = 'adminhtml_category';

        $this->_updateButton('save', 'label', Mage::helper('faq')->__('Save Category'));
        $this->_updateButton('delete', 'label', Mage::helper('faq')->__('Delete Category'));

        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('faq_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'faq_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'faq_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('faq_category_data') && Mage::registry('faq_category_data')->getId() ) {
            return Mage::helper('faq')->__("Edit Category '%s'", $this->htmlEscape(Mage::registry('faq_category_data')->getName()));
        } else {
            return Mage::helper('faq')->__('Add Category');
        }
    }
}