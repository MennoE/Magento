<?php
/**
 *
 * @category Kega
 * @package  Kega_ProductAttributeDefault
 */
?>
<?php
class Kega_ProductAttributeDefault_Block_Adminhtml_Productattributedefault_List extends Mage_Adminhtml_Block_Widget_Container
{

    protected $_backButtonLabel = 'Back';
    protected $_blockGroup = 'adminhtml';


    public function __construct()
    {
        parent::__construct();

        $this->_controller = 'productattributedefault';
        $this->_headerText = Mage::helper('kega_productattributedefault')->__('Manage Product Attribute Default Values Entries');

        $this->_addButtonLabel = Mage::helper('kega_productattributedefault')->__('Add New Entry');

        $this->setTemplate('widget/grid/container.phtml');

        $this->_addButton('add', array(
            'label'     => $this->getAddButtonLabel(),
            'onclick'   => 'setLocation(\'' . $this->getCreateUrl() .'\')',
            'class'     => 'add',
        ));
    }

    protected function _prepareLayout()
    {
        $this->setChild( 'grid',
            $this->getLayout()->createBlock('kega_productattributedefault/adminhtml_productattributedefault_grid',
            $this->_controller . '.grid')->setSaveParametersInSession(true) );
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

    protected function _addBackButton()
    {
        $this->_addButton('back', array(
            'label'     => $this->getBackButtonLabel(),
            'onclick'   => 'setLocation(\'' . $this->getBackUrl() .'\')',
            'class'     => 'back',
        ));
    }


    public function getCreateUrl()
    {
        return $this->getUrl('*/*/new');
    }

    protected function getAddButtonLabel()
    {
        return $this->_addButtonLabel;
    }
}
