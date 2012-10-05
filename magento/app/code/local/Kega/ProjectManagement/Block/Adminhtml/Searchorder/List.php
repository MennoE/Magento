<?php
/**
 * Adminhtml search order block container
 *
 * @category   Kega
 * @package    Kega_ProjectManagement
 */
class Kega_ProjectManagement_Block_Adminhtml_Searchorder_List extends Mage_Adminhtml_Block_Widget_Container
{

    public function __construct()
    {
        parent::__construct();

        $this->_controller = 'projectmanagement';
        $this->_headerText = Mage::helper('projectmanagement')->__('Search Order by Product SKU');

        $this->setTemplate('widget/grid/container.phtml');
    }


    protected function _prepareLayout()
    {

        $gridBlock = $this->getLayout()->createBlock('projectmanagement/adminhtml_searchorder_grid',
                     $this->_controller . '.grid');
        $gridBlock->setSaveParametersInSession(true);


        $this->setChild('grid', $gridBlock);

        return parent::_prepareLayout();
    }

    public function getGridHtml()
    {
        return $this->getChildHtml('grid');
    }


    public function getHeaderCssClass()
    {
        return 'icon-head ' . parent::getHeaderCssClass();
    }

    public function getHeaderWidth()
    {
        return 'width:50%;';
    }




}
