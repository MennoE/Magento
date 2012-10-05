<?php
/**
 *
 * @category Kega
 * @package  Kega_ProductAttributeDefault
 */
?>
<?php
class Kega_ProductAttributeDefault_Block_Adminhtml_Productattributedefault_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('productattributedefaultGrid');
        $this->setDefaultSort('created_on');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('kega_productattributedefault/productattributedefault')->getCollection();
        //$collection->joinCategory();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('productattributedefault_id', array(
            'header'    => Mage::helper('kega_productattributedefault')->__('ID'),
            'align'     =>'right',
            'width'     => '50px',
            'index'     => 'productattributedefault_id',
        ));

        $this->addColumn('created_on', array(
            'header'    => Mage::helper('kega_productattributedefault')->__('Added On'),
            'align'     =>'left',
            'type'	=> 'datetime',
            'width'     => '120px',
            'index'     => 'created_on',
        ));


        $this->addColumn('is_enabled', array(
            'header'    => Mage::helper('kega_productattributedefault')->__('Status'),
            'align'     =>'left',
            'index'     => 'is_enabled',
            'width'     => '60px',
            'type'  => 'options',
            'options' => array(
                '0' => Mage::helper('kega_productattributedefault')->__('Inactive'),
                '1' => Mage::helper('kega_productattributedefault')->__('Active'),
            )
        ));


        $this->addColumn('dry_run', array(
            'header'    => Mage::helper('kega_productattributedefault')->__('Dry Run'),
            'align'     =>'left',
            'index'     => 'dry_run',
            'width'     => '60px',
            'type'  => 'options',
            'options' => array(
                '0' => Mage::helper('kega_productattributedefault')->__('No'),
                '1' => Mage::helper('kega_productattributedefault')->__('Yes'),
            )
        ));


        $this->addColumn('rule_name', array(
            'header'    => Mage::helper('kega_productattributedefault')->__('Regelnaam'),
            'align'     =>'left',
            'index'     => 'rule_name',
            'width'     => '100px',
        ));

        //'Categories - Add'Product toevoegen aan
        //Categories - RemoveProduct verwijderen uit
        $this->addColumn('categories_add', array(
            'header'    => Mage::helper('kega_productattributedefault')->__('Categories'),
            'align'     =>'left',
            'index'     => 'categories',
            'sortable'  => false,
            'filter' => false,
            'width'     => '220px',
            'renderer'  => 'kega_productattributedefault/adminhtml_productattributedefault_grid_renderer_categories',
        ));

        $this->addColumn('attributes', array(
            'header'    => Mage::helper('kega_productattributedefault')->__('Attributes'),
            'align'     =>'left',
            'index'     => 'attributes',
            'width'     => '220px',
            'sortable'  => false,
            'filter' => false,
            'renderer'  => 'kega_productattributedefault/adminhtml_productattributedefault_grid_renderer_attributes',
        ));
        
        $this->addColumn('attributes_dynamic', array(
            'header'    => Mage::helper('kega_productattributedefault')->__('Dynamic Attributes'),
            'align'     =>'left',
            'index'     => 'attributes_dynamic',
            'width'     => '250px',
            'sortable'  => false,
            'filter' => false,
            'renderer'  => 'kega_productattributedefault/adminhtml_productattributedefault_grid_renderer_attributesdynamic',
        ));
        
        $stores =  Mage::app()->getStores();
        
        $optionsHash = array(
        	0 => Mage::helper('adminhtml')->__('All Store Views'),
        );
        
        foreach ($stores as $store) {
        	$optionsHash[$store->getId()] = $store->getWebsite()->getName().'/'.$store->getGroup()->getName().'/'.$store->getName();
        }
        
    	if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_views',
                array(
                    'header'=> Mage::helper('catalog')->__('StoreViews'),
                    'width' => '200px',
                    'sortable'  => false,
                    'index'     => 'store_views',
                    'type'      => 'options',
                    'options'   => $optionsHash,
                	'filter_condition_callback' => array($this, '_filterStoreViewCondition'),
                	'renderer'  => 'kega_productattributedefault/adminhtml_productattributedefault_grid_renderer_storeviews',
            ));
        }

        return parent::_prepareColumns();
    }
    
    
	/**
     * Builds condition for store view filtering
     *
     */
    public function _filterStoreViewCondition($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return;
        }
        $collection->getSelect()->where('main_table.apply_to_stores LIKE \'%"'.(int)$value.'"%\'');
    }


    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('productattributedefault');
        $this->getMassactionBlock()->setFormFieldName('productattributedefault');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('kega_productattributedefault')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('kega_productattributedefault')->__('Are you sure?')
        ));

        $statusOptions = Mage::getModel('kega_productattributedefault/productattributedefault')->getStatusOptions();
        $statusOptionsTypeSelect = array(array('label'=>'', 'value'=>''));
        foreach ($statusOptions as $statusOptionValue => $statusOptionLabel) {
                $statusOptionsTypeSelect[] = array(
                    'label' => $statusOptionLabel,
                    'value' => $statusOptionValue,
                );
        }
        $this->getMassactionBlock()->addItem('update_status', array(
           'label'         => Mage::helper('kega_productattributedefault')->__('Update Status'),
           'url'           => $this->getUrl('*/*/massUpdateStatus'),
           'additional'    => array(
               'status'    => array(
                   'name'      => 'status',
                   'type'      => 'select',
                   'class'     => 'required-entry',
                   'label'     => Mage::helper('kega_productattributedefault')->__('Status'),
                   'values'    => $statusOptionsTypeSelect,
              )
           )
        ));


        $dryRunOptions = Mage::getModel('kega_productattributedefault/productattributedefault')->getDryRunOptions();
        $dryRunOptionsTypeSelect = array(array('label'=>'', 'value'=>''));
        foreach ($dryRunOptions as $dryRunOptionValue => $dryRunOptionLabel) {
                $dryRunOptionsTypeSelect[] = array(
                    'label' => $dryRunOptionLabel,
                    'value' => $dryRunOptionValue,
                );
        }
        $this->getMassactionBlock()->addItem('update_dry_run', array(
           'label'         => Mage::helper('kega_productattributedefault')->__('Update Dry Run'),
           'url'           => $this->getUrl('*/*/massUpdateDryRun'),
           'additional'    => array (
               'dry_run'    => array (
                   'name'      => 'dry_run',
                   'type'      => 'select',
                   'class'     => 'required-entry',
                   'label'     => Mage::helper('kega_productattributedefault')->__('Dry Run'),
                   'values'    => $dryRunOptionsTypeSelect,
               )
           )
        ));

        return $this;
    }


    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }



}
