<?php
class Kega_Store_Block_Adminhtml_Store_Edit_Tab_Opening extends Mage_Adminhtml_Block_Widget_Form
{

	public function __construct()
	{
		parent::__construct();
	}
    
       
      protected function _prepareForm()

      {

          $form = new Varien_Data_Form();

          $this->setForm($form);

          $fieldset = $form->addFieldset('store_form_opening', array('legend'=>Mage::helper('store')->__('Opening time details')));

			$fieldset->addField('mondayopen1', 'text', array(
				'label'     => Mage::helper('store')->__('Monday open'),
				'class'     => 'default_value_text',
				'required'  => false,
				'index'      => 'mondayopen1',
				'name'      => 'opening[mondayopen1]',
			));
			
			$fieldset->addField('mondayclose1', 'text', array(
				'label'     => Mage::helper('store')->__('Monday close'),
				'class'     => 'default_value_text',
				'required'  => false,
				'name'      => 'opening[mondayclose1]',
			));
			
			$fieldset->addField('mondayopen2', 'text', array(
				'label'     => Mage::helper('store')->__('Monday evening open'),
				'class'     => 'default_value_text',
				'required'  => false,
				'name'      => 'opening[mondayopen2]',
			));
			
			$fieldset->addField('mondayclose2', 'text', array(
				'label'     => Mage::helper('store')->__('Monday evening close'),
				'class'     => 'default_value_text',
				'required'  => false,
				'name'      => 'opening[mondayclose2]',
			));
			
			/******Tuesday*******************/
			
			$fieldset->addField('tuesdayopen1', 'text', array(
				'label'     => Mage::helper('store')->__('Tuesday open'),
				'class'     => 'default_value_text',
				'required'  => false,
				'name'      => 'opening[tuesdayopen1]',
			));
			
			$fieldset->addField('tuesdayclose1', 'text', array(
				'label'     => Mage::helper('store')->__('Tuesday close'),
				'class'     => 'default_value_text',
				'required'  => false,
				'name'      => 'opening[tuesdayclose1]',
			));
			
			$fieldset->addField('tuesdayopen2', 'text', array(
				'label'     => Mage::helper('store')->__('Tuesday evening open'),
				'class'     => 'default_value_text',
				'required'  => false,
				'name'      => 'opening[tuesdayopen2]',
			));
			
			$fieldset->addField('tuesdayclose2', 'text', array(
				'label'     => Mage::helper('store')->__('Tuesday evening close'),
				'class'     => 'default_value_text',
				'required'  => false,
				'name'      => 'opening[tuesdayclose2]',
			));
			
			/*******Wednesday******************/
			
			$fieldset->addField('wednesdayopen1', 'text', array(
				'label'     => Mage::helper('store')->__('Wednesday open'),
				'class'     => 'default_value_text',
				'required'  => false,
				'name'      => 'opening[wednesdayopen1]',
			));
			
			$fieldset->addField('wednesdayclose1', 'text', array(
				'label'     => Mage::helper('store')->__('Wednesday close'),
				'class'     => 'default_value_text',
				'required'  => false,
				'name'      => 'opening[wednesdayclose1]',
			));
			
			$fieldset->addField('wednesdayopen2', 'text', array(
				'label'     => Mage::helper('store')->__('Wednesday evening open'),
				'class'     => 'default_value_text',
				'required'  => false,
				'name'      => 'opening[wednesdayopen2]',
			));
			
			$fieldset->addField('wednesdayclose2', 'text', array(
				'label'     => Mage::helper('store')->__('Wednesday evening close'),
				'class'     => 'default_value_text',
				'required'  => false,
				'name'      => 'opening[wednesdayclose2]',
			));
			
			/*******Thursday**********************/
          			
			$fieldset->addField('thursdayopen1', 'text', array(
				'label'     => Mage::helper('store')->__('Thursday open'),
				'class'     => 'default_value_text',
				'required'  => false,
				'name'      => 'opening[thursdayopen1]',
			));
			
			$fieldset->addField('thursdayclose1', 'text', array(
				'label'     => Mage::helper('store')->__('Thursday close'),
				'class'     => 'default_value_text',
				'required'  => false,
				'name'      => 'opening[thursdayclose1]',
			));
			
			$fieldset->addField('thursdayopen2', 'text', array(
				'label'     => Mage::helper('store')->__('Thursday evening open'),
				'class'     => 'default_value_text',
				'required'  => false,
				'name'      => 'opening[thursdayopen2]',
			));
			
			$fieldset->addField('thursdayclose2', 'text', array(
				'label'     => Mage::helper('store')->__('Thursday evening close'),
				'class'     => 'default_value_text',
				'required'  => false,
				'name'      => 'opening[thursdayclose2]',
			));
			
			/*******Friday**********************/
          			
			$fieldset->addField('fridayopen1', 'text', array(
				'label'     => Mage::helper('store')->__('Friday open'),
				'class'     => 'default_value_text',
				'required'  => false,
				'name'      => 'opening[fridayopen1]',
			));
			
			$fieldset->addField('fridayclose1', 'text', array(
				'label'     => Mage::helper('store')->__('Friday close'),
				'class'     => 'default_value_text',
				'required'  => false,
				'name'      => 'opening[fridayclose1]',
			));
			
			$fieldset->addField('fridayopen2', 'text', array(
				'label'     => Mage::helper('store')->__('Friday evening open'),
				'class'     => 'default_value_text',
				'required'  => false,
				'name'      => 'opening[fridayopen2]',
			));
			
			$fieldset->addField('fridayclose2', 'text', array(
				'label'     => Mage::helper('store')->__('Friday evening close'),
				'class'     => 'default_value_text',
				'required'  => false,
				'name'      => 'opening[fridayclose2]',
			));
			
			/*******Saturday**********************/
          			
			$fieldset->addField('saturdayopen1', 'text', array(
				'label'     => Mage::helper('store')->__('Saturday open'),
				'class'     => 'default_value_text',
				'required'  => false,
				'name'      => 'opening[saturdayopen1]',
			));
			
			$fieldset->addField('saturdayclose1', 'text', array(
				'label'     => Mage::helper('store')->__('Saturday close'),
				'class'     => 'default_value_text',
				'required'  => false,
				'name'      => 'opening[saturdayclose1]',
			));
			
			$fieldset->addField('saturdayopen2', 'text', array(
				'label'     => Mage::helper('store')->__('Saturday evening open'),
				'class'     => 'default_value_text',
				'required'  => false,
				'name'      => 'opening[saturdayopen2]',
			));
			
			$fieldset->addField('saturdayclose2', 'text', array(
				'label'     => Mage::helper('store')->__('Saturday evening close'),
				'class'     => 'default_value_text',
				'required'  => false,
				'name'      => 'opening[saturdayclose2]',
			));
			
			/*******Sunday**********************/
          			
			$fieldset->addField('sundayopen1', 'text', array(
				'label'     => Mage::helper('store')->__('Sunday open'),
				'class'     => 'default_value_text',
				'required'  => false,
				'name'      => 'opening[sundayopen1]',
			));
			
			$fieldset->addField('sundayclose1', 'text', array(
				'label'     => Mage::helper('store')->__('Sunday close'),
				'class'     => 'default_value_text',
				'required'  => false,
				'name'      => 'opening[sundayclose1]',
			));
			
			$fieldset->addField('sundayopen2', 'text', array(
				'label'     => Mage::helper('store')->__('Sunday evening open'),
				'class'     => 'default_value_text',
				'required'  => false,
				'name'      => 'opening[sundayopen2]',
			));
			
			$fieldset->addField('sundayclose2', 'text', array(
				'label'     => Mage::helper('store')->__('Sunday evening close'),
				'class'     => 'default_value_text',
				'required'  => false,
				'name'      => 'opening[sundayclose2]'
			));
         

          if ( Mage::getSingleton('adminhtml/session')->getStoreData() )

          {

              $form->setValues(Mage::getSingleton('adminhtml/session')->getStoreData());

              Mage::getSingleton('adminhtml/session')->setStoreData(null);

          } elseif ( Mage::registry('store_data') ) {

              $form->setValues(Mage::registry('store_data')->getData());

          }

          return parent::_prepareForm();

      }
      
      
      
      

  }