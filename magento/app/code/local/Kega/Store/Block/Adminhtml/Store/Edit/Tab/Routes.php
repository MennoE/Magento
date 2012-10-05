<?php
class Kega_Store_Block_Adminhtml_Store_Edit_Tab_Routes extends Mage_Adminhtml_Block_Widget_Form
{

	public function __construct()
	{
		parent::__construct();

		$this->setTemplate('kega_store/routes.phtml');
	}


	public function getFieldSuffix()
	{
		return 'store_data[store_routes_data]';
	}


	public function getStoreModel()
	{
		return Mage::registry('store');
	}


	public function getFormData()
	{
		$defaultFormData = array(
            'mondayroute' => '',
            'tuesdayroute' => '',
            'wednesdayroute' => '',
            'thursdayroute' => '',
            'fridayroute' => '',
            'saturdayroute' => '',
            'sundayroute' => '',
        );

        $storeRoutesData = array();
        if ($this->getStoreModel()) {
            $storeRoutesData = $this->getStoreModel()->getStoreRoutesData();
            $storeRoutesData = (!is_array($storeRoutesData))? array() : $storeRoutesData;
        }

        return array_merge($defaultFormData, $storeRoutesData);
	}

}