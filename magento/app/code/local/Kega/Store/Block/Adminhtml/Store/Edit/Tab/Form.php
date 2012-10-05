<?php
class Kega_Store_Block_Adminhtml_Store_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{


	public function __construct()
	{
		parent::__construct();
	}


	protected function _prepareLayout()
    {

        Varien_Data_Form::setElementRenderer(
            $this->getLayout()->createBlock('adminhtml/widget_form_renderer_element')
        );

        Varien_Data_Form::setFieldsetRenderer(
            $this->getLayout()->createBlock('adminhtml/widget_form_renderer_fieldset')
        );

        Varien_Data_Form::setFieldsetElementRenderer(
            $this->getLayout()->createBlock('store/adminhtml_store_form_renderer_fieldset_element')
        );
    }


	public function initForm()
	{

		$form = new Varien_Data_Form();

		$form->setFieldNameSuffix('store_data');

		$store = Mage::registry('store_data');

		$fieldset = $form->addFieldset('base_fieldset',
			array('legend'=>Mage::helper('store')->__('Store Information'))
		);

		$this->_setFieldset($store->getAttributes(), $fieldset);

		/**
		* Check is single store mode
		*/
		if (!Mage::app()->isSingleStoreMode())
		{
			$fieldset->addField('storeview_ids', 'multiselect', array(
					'name'      	=> 'storeview_ids',
					'label'     	=> Mage::helper('cms')->__('Store View'),
					'title'     	=> Mage::helper('cms')->__('Store View'),
					'required'  	=> true,
					'values'    	=> Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true),
				));
		}
		else
		{
			$fieldset->addField('storeview_ids', 'hidden', array(
					'name'      => 'storeview_ids',
					'value'     => Mage::app()->getStore(true)->getId()
				));

			$store->setStoreviewIds(Mage::app()->getStore(true)->getId());
		}

		$form->setValues($store->getData());

		$this->setForm($form);

		return $this;
	}

}