<?php

class Kega_Vacancytype_Block_Adminhtml_Vacancytype_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
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
            $this->getLayout()->createBlock('vacancytype/adminhtml_vacancytype_form_renderer_fieldset_element')
        );
	}

	protected function _prepareForm()
	{

	  $form = new Varien_Data_Form();
	  $this->setForm($form);
	  $fieldset = $form->addFieldset('vacancytype_form', array('legend'=>Mage::helper('vacancytype')->__('Vacancy type information')));


	  $wysiwygConfig = Mage::getSingleton('cms/wysiwyg_config')->getConfig(array(
	      'add_variables' => false,
	      'add_widgets' => false,
	      'files_browser_window_url' => Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/cms_wysiwyg_images/index'),
	      'directives_url' => Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/cms_wysiwyg/directive')
	  ));

	  $fieldset->addField('title', 'text', array(
	      'label'     => Mage::helper('vacancytype')->__('Title'),
	      'class'     => 'required-entry',
	      'required'  => true,
	      'name'      => 'title',
      	  'scope'	  => $this->getScope('title'),
	  ));

	  $fieldset->addField('status', 'select', array(
	      'label'     => Mage::helper('vacancytype')->__('Status'),
	      'name'      => 'status',
	      'values'    => array(
	          array(
	              'value'     => 1,
	              'label'     => Mage::helper('vacancytype')->__('Enabled'),
	          ),

	          array(
	              'value'     => 2,
	              'label'     => Mage::helper('vacancytype')->__('Disabled'),
	          ),
	      ),
      	  'scope'	  => $this->getScope('status'),
	  ));


	  $fieldset->addField('text', 'editor', array(
	      'name'      => 'text',
	      'label'     => Mage::helper('vacancytype')->__('Content'),
	      'title'     => Mage::helper('vacancytype')->__('Content'),
	      'style'     => 'width:700px; height:500px;',
	      'state'     => 'html',
	      'config' 	  => $wysiwygConfig,
	      'wysiwyg'   => true,
	      'required'  => true,
      	  'scope'	  => $this->getScope('text'),
	  ));

	  $fieldset->addField('vacancy_form_type', 'select', array(
	      'name'      => 'vacancy_form_type',
	      'label'     => Mage::helper('vacancytype')->__('Vacancy Form Type'),
	      'title'     => Mage::helper('vacancytype')->__('Vacancy Form Type'),

	      'values'    => array(
	          array(
	              'value'     => 1,
	              'label'     => Mage::helper('vacancytype')->__('Form type 1'),
	          ),
	          array(
	              'value'     => 2,
	              'label'     => Mage::helper('vacancytype')->__('Form type 2'),
	          ),
	          array(
	              'value'     => 3,
	              'label'     => Mage::helper('vacancytype')->__('Form type 3'),
	          ),
	      ),
      	  'scope'	  => $this->getScope('vacancy_form_type'),
	  ));

		$fieldset->addField('meta_keywords', 'textarea', array(
			'label'     => Mage::helper('vacancy')->__('Meta keywords'),
			'name'      => 'meta_keywords',
      	  	'scope'	  	=> $this->getScope('meta_keywords'),
		));

		$fieldset->addField('meta_description', 'textarea', array(
			'label'     => Mage::helper('vacancy')->__('Meta description'),
			'name'      => 'meta_description',
      	  	'scope'	  	=> $this->getScope('meta_description'),
		));

	  if ( Mage::getSingleton('adminhtml/session')->getFormData() )
	  {
	      $formData = Mage::getSingleton('adminhtml/session')->getFormData();	
          $form->setValues($formData);    
          if(isset($formData['use_default'])){
          	$form->setUseDefault($formData['use_default']);     
          }

          Mage::getSingleton('adminhtml/session')->setFormData(null);
	  } elseif ( Mage::registry('vacancytype_data') ) {
	      $form->setValues(Mage::registry('vacancytype_data')->getData());

	      if(Mage::registry('vacancytype_data')->getData('use_default')){
          	$form->setUseDefault(Mage::registry('vacancytype_data')->getData('use_default'));
          }
	  }

	  $form->setStoreId($this->getRequest()->getParam('store', 0));
      $form->setObjectId($this->getRequest()->getParam('id', null));

	  return parent::_prepareForm();
	}
	
	private function getScope($columnName) 
	{
  		$storeViewColumns = Kega_Vacancytype_Model_Vacancytype::getStoreViewColumns();

  		if (in_array($columnName, $storeViewColumns)) {
  			return Kega_Vacancytype_Block_Adminhtml_Vacancytype_Form_Renderer_Fieldset_Element::SCOPE_STORE_VIEW;
  		}
  		return Kega_Vacancytype_Block_Adminhtml_Vacancytype_Form_Renderer_Fieldset_Element::SCOPE_GLOBAL;
	}
}