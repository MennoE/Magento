<?php

class Kega_Vacancy_Block_Adminhtml_Vacancy_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
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
            $this->getLayout()->createBlock('vacancy/adminhtml_vacancy_form_renderer_fieldset_element')
        );
	}

	protected function _prepareForm()
	{

		$form = new Varien_Data_Form();

		$this->setForm($form);

		$fieldset = $form->addFieldset('vacancy_form', array('legend'=>Mage::helper('vacancy')->__('Item information')));

		$fieldset->addField('title', 'text', array(
				'label'     => Mage::helper('vacancy')->__('Title'),
				'class'     => 'required-entry',
				'required'  => true,
				'name'      => 'title',
      	  		'scope'	  	=> $this->getScope('title'),
			));

		$fieldset->addField('number', 'text', array(
				'label'     => Mage::helper('vacancy')->__('Vacancy Number'),
				'required'  => false,
				'name'      => 'number',
      	  		'scope'	  	=> $this->getScope('number'),
			));

        $fieldset->addField('hours', 'text', array(
            'label'     => Mage::helper('vacancy')->__('Vacancy Hours'),
            'required'  => false,
            'name'      => 'hours',
        ));

		$vacancytype_values = array();

		foreach(Mage::getModel('vacancytype/vacancytype')->getCollection()->load() as $Vacancytype)
		{
			$vacancytype_values[] = array('value' => $Vacancytype->vacancytypeId,  'label' => $Vacancytype->title );
		}

		$fieldset->addField('vacancytype_id', 'select', array(
				'label'     => Mage::helper('vacancy')->__('Vacancy type'),
				'required'  => true,
				'name'      => 'vacancytype_id',
				'values' 	=> $vacancytype_values,
      	  		'scope'	  	=> $this->getScope('vacancytype_id'),
			));

		$store_values = array();

		foreach(Mage::getModel('store/store')->getCollection()->addAttributeToSelect('name')->load() as $Store )
		{
			$store_values[] = array('value' => $Store->entityId,  'label' => $Store->name );
		}

		$fieldset->addField('shop_id', 'select', array(
			'label'     => Mage::helper('vacancy')->__('Store'),
			'required'  => false,
			'name'      => 'shop_id',
			'values'	=> $store_values,
      	  	'scope'	  	=> $this->getScope('shop_id'),
		));

		//regions
		$vacancyRegion = Mage::getModel('vacancy/vacancyregion');
        $regions = $vacancyRegion->getActive();
        $region_values = array(array('value' => 0,  'label' => '') );


        foreach ($regions as $region){
        	$region_values[] = array('value' => $region['vacancyregion_id'],  'label' => $region['title'] );
        }

		$fieldset->addField('vacancyregion_id', 'select', array(
			'label'     => Mage::helper('vacancy')->__('Region'),
			'required'  => false,
			'name'      => 'vacancyregion_id',
			'values'	=> $region_values,
      	  	'scope'	  	=> $this->getScope('vacancyregion_id'),
		));

		$fieldset->addField('status', 'select', array(
			'label'     => Mage::helper('vacancy')->__('Status'),
			'name'      => 'status',
			'values'    => array(
				array(
					'value'     => 1,
					'label'     => Mage::helper('vacancy')->__('Enabled'),
				),

				array(
					'value'     => 2,
					'label'     => Mage::helper('vacancy')->__('Disabled'),
				),
			),
      	  	'scope'	  	=> $this->getScope('status'),
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
		} elseif ( Mage::registry('vacancy_data') ) {
			$form->setValues(Mage::registry('vacancy_data')->getData());
			
			if(Mage::registry('vacancy_data')->getData('use_default')){
          		$form->setUseDefault(Mage::registry('vacancy_data')->getData('use_default'));
          	}
		}

		$form->setStoreId($this->getRequest()->getParam('store', 0));
		$form->setObjectId($this->getRequest()->getParam('id', null));

		return parent::_prepareForm();
	}

	private function getScope($columnName)
	{
		$storeViewColumns = Kega_Vacancy_Model_Vacancy::getStoreViewColumns();

		if (in_array($columnName, $storeViewColumns)) {
			return Kega_Vacancy_Block_Adminhtml_Vacancy_Form_Renderer_Fieldset_Element::SCOPE_STORE_VIEW;
		}
		return Kega_Vacancy_Block_Adminhtml_Vacancy_Form_Renderer_Fieldset_Element::SCOPE_GLOBAL;
	}
}