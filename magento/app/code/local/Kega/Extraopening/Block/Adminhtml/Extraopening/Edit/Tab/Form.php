<?php

class Kega_Extraopening_Block_Adminhtml_Extraopening_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('extraopening_form', array('legend'=>Mage::helper('extraopening')->__('Item information')));

	  $fieldset->addField('title', 'text', array(
		'label'     => Mage::helper('extraopening')->__('Name'),
		'class'     => 'required-entry',
		'required'  => true,
		'name'      => 'title',
	  ));

	  $dateFormatIso = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);

	  $fieldset->addField('datetime_from', 'date', array(
		  'name'   => 'datetime_from',
		  'label'  => Mage::helper('extraopening')->__('Date From'),
		  'title'  => Mage::helper('extraopening')->__('Date From'),
		  'image'  => $this->getSkinUrl('images/grid-cal.gif'),
		  'input_format' => Varien_Date::DATE_INTERNAL_FORMAT,
		  'format'       => $dateFormatIso
	  ));

	  $fieldset->addField('datetime_to', 'date', array(
		  'name'   => 'datetime_to',
		  'label'  => Mage::helper('extraopening')->__('Date To'),
		  'title'  => Mage::helper('extraopening')->__('Date To'),
		  'image'  => $this->getSkinUrl('images/grid-cal.gif'),
		  'input_format' => Varien_Date::DATE_INTERNAL_FORMAT,
		  'format'       => $dateFormatIso
	  ));

	  $fieldset->addField('status', 'select', array(
		  'label'     => Mage::helper('extraopening')->__('Status'),
		  'required'  => false,
		  'name'      => 'status',
		  'values' => Mage::getModel('extraopening/extraopening')->getExtraOpeningStatuses(),
	  ));

	  $store_values = array();

	  foreach(Mage::getModel('store/store')->getCollection()
			->addAttributeToSelect('name')
			->load() as $Store) {
		$store_values[] = array('value' => $Store->entityId,  'label' => $Store->name );
	  }

	  $fieldset->addField('store_ids', 'multiselect', array(
		  'label'     => Mage::helper('extraopening')->__('Store'),
		  'required'  => false,
		  'name'      => 'store_ids',
		  'values' => $store_values,
		  'value' => Mage::registry('extraopening_data')->getStoreIds(),
	  ));

      if ( Mage::getSingleton('adminhtml/session')->getExtraopeningData() ){
          $form->setValues(Mage::getSingleton('adminhtml/session')->getExtraopeningData());
          Mage::getSingleton('adminhtml/session')->setExtraopeningData(null);
      } elseif ( Mage::registry('extraopening_data') ) {
          $form->setValues(Mage::registry('extraopening_data')->getData());
      }
      return parent::_prepareForm();
  }
}