<?php

class Kega_Vacancytype_Block_Adminhtml_Vacancytype_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'vacancytype';
        $this->_controller = 'adminhtml_vacancytype';
        
        $this->_updateButton('save', 'label', Mage::helper('vacancytype')->__('Save Item'));
        $this->_updateButton('delete', 'label', Mage::helper('vacancytype')->__('Delete Item'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('vacancytype_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'vacancytype_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'vacancytype_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }
    
	protected function _prepareLayout()
	{
		parent::_prepareLayout();
		if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled() && ($block = $this->getLayout()->getBlock('head'))) {
		    $block->setCanLoadTinyMce(true);
		}
		
	}
	
    public function getHeaderText()
    {
        if( Mage::registry('vacancytype_data') && Mage::registry('vacancytype_data')->getId() ) {
            return Mage::helper('vacancytype')->__("Edit Item '%s'", $this->htmlEscape(Mage::registry('vacancytype_data')->getTitle()));
        } else {
            return Mage::helper('vacancytype')->__('Add Item');
        }
    }
}