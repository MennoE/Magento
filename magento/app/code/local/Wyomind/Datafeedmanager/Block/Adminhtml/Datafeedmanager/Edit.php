<?php

class Wyomind_Datafeedmanager_Block_Adminhtml_Datafeedmanager_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
	public function __construct()
	{
		parent::__construct();
		 
		$this->_objectId = 'feed_id';
		$this->_blockGroup = 'datafeedmanager';
		$this->_controller = 'adminhtml_datafeedmanager';
			
		
		if(Mage::registry('datafeedmanager_data')->getFeedId()){
		    	$this->_addButton('generate', array(
		            'label'   => Mage::helper('adminhtml')->__('Save & Generate'),
		            'onclick' => "$('generate').value=1; editForm.submit();",
		            'class'   => 'add',
		        ));
		        $this->_addButton('continue', array(
		            'label'   => Mage::helper('adminhtml')->__('Save & Continue'),
		            'onclick' => "$('continue').value=1; editForm.submit();",
		            'class'   => 'add',
		        ));
		        $this->_addButton('copy', array(
		            'label'   => Mage::helper('adminhtml')->__('Copy'),
		            'onclick' => "$('feed_id').remove(); editForm.submit();",
		            'class'   => 'add',
		        ));
		    }    
		
	}

	public function getHeaderText()
	{
		if( Mage::registry('datafeedmanager_data') && Mage::registry('datafeedmanager_data')->getFeedId() ) {
			return Mage::helper('datafeedmanager')->__("Edit data feed  '%s'", $this->htmlEscape(Mage::registry('datafeedmanager_data')->getFeed_name()));
		} else {
			return Mage::helper('datafeedmanager')->__('Add data feed');
		}
	}
}