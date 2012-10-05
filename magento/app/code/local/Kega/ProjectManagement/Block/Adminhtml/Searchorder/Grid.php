<?php
/**
 * Adminhtml search order grid
 *
 * @category   Kega
 * @package    Kega_ProjectManagement
 */
class Kega_ProjectManagement_Block_Adminhtml_Searchorder_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('search_order_grid');
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);


    }

    protected function _prepareLayout()
    {
        // used a different block to set the search block template
        $this->setTemplate('projectmanagement/searchorder/grid.phtml');



        $searchBlock = $this->getLayout()->createBlock('adminhtml/template',
            $this->_controller . '.grid.search');

        $searchBlock->setTemplate('projectmanagement/searchorder/search.phtml');
        $searchBlock->setSaveUrl($this->getUrl('*/*/searchOrder'));

        $productSku = $this->getProductSku();

        if ($productSku) {
            $searchBlock->setSelectedProductSku($productSku);
        }

        $this->setChild('search', $searchBlock);

        return parent::_prepareLayout();
    }

    public function getProductSku()
    {
        return Mage::registry('product_sku');
    }


    public function getOrderIdsByProductSku()
    {
        $productSku = $this->getProductSku();

        if (!$productSku) return array();

        $collection = Mage::getModel('sales/order_item')->getCollection()
                        ->addFieldToFilter('sku', $productSku);
        $orderIds = array();

        foreach ($collection as $orderItem) {
            $orderIds[] = $orderItem->getOrderId();
        }

        return $orderIds;
    }

    protected function _prepareCollection()
    {
        // TODO: add full name logic
        // TODO: encapsulate this logic into collection
        $collection = Mage::getResourceModel('sales/order_collection')
            ->addAttributeToSelect('*')
            ->joinAttribute('billing_firstname', 'order_address/firstname', 'billing_address_id', null, 'left')
            ->joinAttribute('billing_lastname', 'order_address/lastname', 'billing_address_id', null, 'left')
            ->joinAttribute('shipping_firstname', 'order_address/firstname', 'shipping_address_id', null, 'left')
            ->joinAttribute('shipping_lastname', 'order_address/lastname', 'shipping_address_id', null, 'left')
            ->addExpressionAttributeToSelect('billing_name',
                'CONCAT({{billing_firstname}}, " ", {{billing_lastname}})',
                array('billing_firstname', 'billing_lastname'))
            ->addExpressionAttributeToSelect('shipping_name',
                'CONCAT({{shipping_firstname}},  IFNULL(CONCAT(\' \', {{shipping_lastname}}), \'\'))',
                array('shipping_firstname', 'shipping_lastname'));


        $orderIds = $this->getOrderIdsByProductSku();
        // filter by order id
        if ($orderIds) {
            $collection->addFieldToFilter('entity_id', array('in' => $orderIds));
        }

        $this->setCollection($collection);


        //echo $collection->getSelectSql();

        return parent::_prepareCollection();
    }




    protected function _prepareColumns()
    {

        $this->addColumn('real_order_id', array(
            'header'=> Mage::helper('sales')->__('Order #'),
            'width' => '80px',
            'type'  => 'text',
            'index' => 'increment_id',
        ));

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_id', array(
                'header'    => Mage::helper('sales')->__('Purchased from (store)'),
                'index'     => 'store_id',
                'type'      => 'store',
                'store_view'=> true,
                'display_deleted' => true,
            ));
        }

        $this->addColumn('created_at', array(
            'header' => Mage::helper('sales')->__('Purchased On'),
            'index' => 'created_at',
            'type' => 'datetime',
            'width' => '100px',
        ));


        $this->addColumn('billing_name', array(
            'header' => Mage::helper('sales')->__('Bill to Name'),
            'index' => 'billing_name',
        ));


        $this->addColumn('shipping_name', array(
            'header' => Mage::helper('sales')->__('Ship to Name'),
            'index' => 'shipping_name',
        ));

        $this->addColumn('base_grand_total', array(
            'header' => Mage::helper('sales')->__('G.T. (Base)'),
            'index' => 'base_grand_total',
            'type'  => 'currency',
            'currency' => 'base_currency_code',
        ));

        $this->addColumn('grand_total', array(
            'header' => Mage::helper('sales')->__('G.T. (Purchased)'),
            'index' => 'grand_total',
            'type'  => 'currency',
            'currency' => 'order_currency_code',
        ));

        $this->addColumn('status', array(
            'header' => Mage::helper('sales')->__('Status'),
            'index' => 'status',
            'type'  => 'options',
            'width' => '70px',
            'options' => Mage::getSingleton('sales/order_config')->getStatuses(),
        ));

        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/view')) {
            $this->addColumn('action',
                array(
                    'header'    => Mage::helper('sales')->__('Action'),
                    'width'     => '50px',
                    'type'      => 'action',
                    'getter'     => 'getId',
                    'actions'   => array(
                        array(
                            'caption' => Mage::helper('sales')->__('View'),
                            'url'     => array('base'=>'adminhtml/sales_order/view'),
                            'field'   => 'order_id'
                        )
                    ),
                    'filter'    => false,
                    'sortable'  => false,
                    'index'     => 'stores',
                    'is_system' => true,
            ));
        }

        return parent::_prepareColumns();
    }


    /**
     * Retrive search block
     *
     * @return Mage_Adminhtml_Block_Template
     */
    public function getSearchBlock()
    {
        return $this->getChild('search');
    }

    public function getSearchBlockHtml()
    {
        return $this->getSearchBlock('search')->toHtml();
    }




    public function getRowUrl($row)
    {
        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/view')) {
            return $this->getUrl('adminhtml/sales_order/view', array('order_id' => $row->getId()));
        }
        return false;
    }


}
