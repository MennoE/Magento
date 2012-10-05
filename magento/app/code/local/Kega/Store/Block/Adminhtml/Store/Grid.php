<?php

class Kega_Store_Block_Adminhtml_Store_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct()
	{
		parent::__construct();
		$this->setId('storeGrid');
		$this->setDefaultSort('entity_id');
		$this->setDefaultDir('ASC');
		$this->setSaveParametersInSession(true);
	}


	protected function _getStore()
    {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }

	protected function _prepareCollection()
	{
		$store = $this->_getStore();

		$collection = Mage::getModel('store/store')->getCollection()
			->addAttributeToSelect('address')
			->addAttributeToSelect('number')
			->addAttributeToSelect('number_ext')
			->addAttributeToSelect('name')
			->addAttributeToSelect('phone')
			->addAttributeToSelect('city')
			->addAttributeToSelect('email')
			->addAttributeToSelect('lat')
			->addAttributeToSelect('lng')
			->addAttributeToSelect('zipcode');

		if($store->getId())
		{
			$collection->joinAttribute('custom_name', 'store/name', 'entity_id', null, 'inner', $store->getId());

			$collection->joinAttribute('is_active', 'store/is_active', 'entity_id', null, 'inner', $store->getId());
		}
		else
		{
			$collection->addAttributeToSelect('is_active');
		}

		$this->setCollection($collection);

		return parent::_prepareCollection();
	}



	protected function _prepareColumns()
	{

		$this->addColumn('entity_id', array(
			'header'    => Mage::helper('store')->__('ID'),
			'width'     => '50px',
			'index'     => 'entity_id',
			'type'  => 'number',
		));

		$this->addColumn('name', array(
			'header'    => Mage::helper('store')->__('Name'),
			'index'     => 'name'
		));

		$store = $this->_getStore();
        if ($store->getId()) {
            $this->addColumn('custom_name',
                array(
                    'header'=> Mage::helper('catalog')->__('Name In %s', $store->getName()),
                    'index' => 'custom_name',
            ));
        }

		$this->addColumn('address', array(
			'header'    => Mage::helper('store')->__('Address'),
			'align'     =>'left',
			'index'     => 'address',
			)
		);

		$this->addColumn('number', array(
			'header'    => Mage::helper('store')->__('Number'),
			'align'     =>'left',
			'index'     => 'number',
			)
		);

		$this->addColumn('number_ext', array(
			'header'    => Mage::helper('store')->__('Ext.'),
			'align'     =>'left',
			'index'     => 'number_ext',
			)
		);

		$this->addColumn('city', array(
			'header'    => Mage::helper('store')->__('City'),
			'align'     =>'left',
			'index'     => 'city',
			)
		);

		$this->addColumn('zipcode', array(
			'header'    => Mage::helper('store')->__('Zipcode'),
			'align'     =>'left',
			'index'     => 'zipcode',
			)
		);

		$this->addColumn('phone', array(
			'header'    => Mage::helper('store')->__('Phone'),
			'align'     =>'left',
			'index'     => 'phone',
			)
		);

		$this->addColumn('email', array(
			'header'    => Mage::helper('store')->__('Email'),
			'align'     =>'left',
			'index'     => 'email',
			)
		);

		$this->addColumn('lng', array(
			'header'    => Mage::helper('store')->__('Longitude'),
			'align'     =>'left',
			'index'     => 'lng',
			)
		);

		$this->addColumn('lat', array(
			'header'    => Mage::helper('store')->__('Latitude'),
			'align'     =>'left',
			'index'     => 'lat',
			)
		);

		$this->addColumn('active', array(
			'header'    => Mage::helper('store')->__('Tonen op website'),
			'align'     => 'left',
			'width'     => '80px',
			'index'     => 'is_active',
			'type'      => 'options',
			'options'   => array(
					1 => 'Ja',
					0 => 'Nee',
				),
			)
		);

		$this->addExportType('*/*/exportCsv', Mage::helper('customer')->__('CSV'));
		return parent::_prepareColumns();

	}



	public function getRowUrl($row)
	{
		return $this->getUrl('*/*/edit', array(
			'store'=>$this->getRequest()->getParam('store'),
			'id'=>$row->getId())
		);

	}





}