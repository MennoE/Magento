<?php

class Kega_Store_Block_Adminhtml_Store_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

	public function __construct()
	{
		parent::__construct();
		$this->setId('store_tabs');
		$this->setDestElementId('edit_form');
		$this->setTitle(Mage::helper('store')->__('Store Information'));
	}



	protected function _beforeToHtml()
	{
 		$this->addTab('form_section', array(
            'label'     => Mage::helper('store')->__('Store Information'),
			'name'     => Mage::helper('store')->__('Store Information'),
			'title'     => Mage::helper('store')->__('Store Information'),
            'content'   => $this->getLayout()->createBlock('store/adminhtml_store_edit_tab_form')->initForm()->toHtml()
        ));

		$this->addTab('form_section_opening', array(
			'label'     => Mage::helper('store')->__('Opening time'),
			'name'     => Mage::helper('store')->__('Opening time'),
			'title'     => Mage::helper('store')->__('Opening time'),
			'content'   => $this->getLayout()->createBlock('store/adminhtml_store_edit_tab_opening')->toHtml(),

			)
		);

        $this->addTab('form_routes', array(
			'label'     => Mage::helper('store')->__('Routes'),
			'name'     => Mage::helper('store')->__('Routes'),
			'title'     => Mage::helper('store')->__('Routes'),
			'content'   => $this->getLayout()->createBlock('store/adminhtml_store_edit_tab_routes')->toHtml(),
			)
		);

		$this->addTab('form_extra_opening', array(
			'label'     => Mage::helper('store')->__('Extra Opening'),
			'name'     => Mage::helper('store')->__('Extra Opening'),
			'title'     => Mage::helper('store')->__('Extra Opening'),
			'content'   => $this->getLayout()->createBlock('store/adminhtml_store_edit_tab_extraopening')->toHtml(),
			)
		);

		//!!! delete code belong
		$storeId = 0;
		if ($this->getRequest()->getParam('store')) {
			$storeId = Mage::app()->getStore($this->getRequest()->getParam('store'))->getId();
		}

		return parent::_beforeToHtml();
	}
}