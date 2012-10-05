<?php

class Kega_Faq_Block_Adminhtml_Question_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
	 protected function _prepareLayout()
	 {
		 if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled() && ($block = $this->getLayout()->getBlock('head'))) {
			 $block->setCanLoadTinyMce(true);
		 }
		 
		 parent::_prepareLayout();
		 Varien_Data_Form::setElementRenderer(
            $this->getLayout()->createBlock('adminhtml/widget_form_renderer_element')
        );

        Varien_Data_Form::setFieldsetRenderer(
            $this->getLayout()->createBlock('adminhtml/widget_form_renderer_fieldset')
        );

  	 	Varien_Data_Form::setFieldsetElementRenderer(
            $this->getLayout()->createBlock('faq/adminhtml_question_form_renderer_fieldset_element')
        );
        
	 }

  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('faq_question_form', array('legend'=>Mage::helper('faq')->__('Question information')));

		$wysiwygConfig = Mage::getSingleton('cms/wysiwyg_config')->getConfig(array(
			'add_variables' => false,
			'add_widgets' => false,
			'files_browser_window_url' => Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/cms_wysiwyg_images/index'),
			'directives_url' => Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/cms_wysiwyg/directive')
		));

      $fieldset->addField('question', 'textarea', array(
          'label'     => Mage::helper('faq')->__('Question'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'question',
      ));

      $fieldset->addField('answer', 'editor', array(
          'name'      => 'answer',
		  'label'     => Mage::helper('faq')->__('Answer'),
          'class'     => 'required-entry',
          'wysiwyg'   => true,
		  'config'    => $wysiwygConfig,
		  'state'	  => 'html',
		  'theme' => 'advanced',
          'required'  => true,
      ));

      $category_values = array();

      $allStores = Mage::app()->getStores();
      $collection = Mage::getModel('faq/category')->getCollection();

      if (intval($this->getRequest()->getParam('store'))) {
          $collection->addStoreFilter($this->getRequest()->getParam('store'));
      }
      else {
          $collection->addStoreData();
      }

	  foreach($collection as $category)
	  {
        $storeId = $this->getRequest()->getParam('store')
            ? $this->getRequest()->getParam('store')
            : $category->getStoreId();

        if (isset($allStores[$storeId])) {
            $storeLabel = '[' . $allStores[$storeId]->getName() . ']';
        }
        else {
            $storeLabel = '[All]';
        }
	    $category_values[] = array('value' => $category->categoryId,  'label' => $category->name . ' ' . $storeLabel);
	  }

      $fieldset->addField('category_id', 'select', array(
          'label'     => Mage::helper('faq')->__('Category'),
          'required'  => false,
          'name'      => 'category_id',
		  'values' => $category_values,
		  'value' => Mage::registry('faq_question_data')->getCategoryId(),
	  ));

      $fieldset->addField('display_order', 'text', array(
          'label'     => Mage::helper('faq')->__('Display order'),
          'name'      => 'display_order',
      ));

      if ( Mage::getSingleton('adminhtml/session')->getFormData() ) {
      	  $formData = Mage::getSingleton('adminhtml/session')->getFormData();
          $form->setValues($formData);    
          if(isset($formData['use_default'])){
          	$form->setUseDefault($formData['use_default']);     
          }

          Mage::getSingleton('adminhtml/session')->setFormData(null);
      } elseif ( Mage::registry('faq_question_data') ) {
          $form->setValues(Mage::registry('faq_question_data')->getData());
          
      	  if(Mage::registry('faq_question_data')->getData('use_default')){
          	$form->setUseDefault(Mage::registry('faq_question_data')->getData('use_default'));
          }
      }

      $form->setStoreId($this->getRequest()->getParam('store', 0));
      $form->setObjectId($this->getRequest()->getParam('id', null));

      return parent::_prepareForm();
  }
}