<?php

class Kega_Faq_Block_Adminhtml_Category_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{

  protected function _prepareLayout()
  {
  		Varien_Data_Form::setElementRenderer(
            $this->getLayout()->createBlock('adminhtml/widget_form_renderer_element')
        );

        Varien_Data_Form::setFieldsetRenderer(
            $this->getLayout()->createBlock('adminhtml/widget_form_renderer_fieldset')
        );

  	 	Varien_Data_Form::setFieldsetElementRenderer(
            $this->getLayout()->createBlock('faq/adminhtml_category_form_renderer_fieldset_element')
        );
  }

  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('faq_category_form', array('legend'=>Mage::helper('faq')->__('Item information')));

      $fieldset->addField('name', 'text', array(
          'label'     => Mage::helper('faq')->__('Name'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'name',
      ));

      $fieldset->addField('permalink', 'text', array(
          'label'     => Mage::helper('faq')->__('Permalink'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'permalink',
      ));

      $fieldset->addField('display_order', 'text', array(
          'label'     => Mage::helper('faq')->__('Display order'),
          'name'      => 'display_order',
      ));

      $fieldset->addField('overview_image', 'image', array(
			'label'		=> Mage::helper('faq')->__('Overview image'),
      		'required'  => false,
      		'name'      => 'images[overview_image]',
      ));

      $fieldset->addField('category_image', 'image', array(
      		'label'		=> Mage::helper('faq')->__('Category image'),
      		'required'  => false,
      		'name'      => 'images[category_image]',
      ));
      
        $fieldset->addField('store_id', 'multiselect', array(
			'name'      => 'stores[]',
            'label'     => Mage::helper('cms')->__('Store View'),
            'title'     => Mage::helper('cms')->__('Store View'),
            'required'  => true,
            'values'    => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true),
      	  	'scope'	  	=> $this->getScope('store_id'),
		));

      if ( Mage::getSingleton('adminhtml/session')->getFormData() ) {
      	  $formData = Mage::getSingleton('adminhtml/session')->getFormData();	
          $form->setValues($formData);    
          if(isset($formData['use_default'])){
          	$form->setUseDefault($formData['use_default']);     
          }

          Mage::getSingleton('adminhtml/session')->setFormData(null);
      } elseif ( Mage::registry('faq_category_data') ) {
          $form->setValues(Mage::registry('faq_category_data')->getData());
          
      	  if(Mage::registry('faq_category_data')->getData('use_default')){
          	$form->setUseDefault(Mage::registry('faq_category_data')->getData('use_default'));
          }
      }
      
      $form->setStoreId($this->getRequest()->getParam('store', 0));
      $form->setObjectId($this->getRequest()->getParam('id', null));
      
      return parent::_prepareForm();
  }
  
}