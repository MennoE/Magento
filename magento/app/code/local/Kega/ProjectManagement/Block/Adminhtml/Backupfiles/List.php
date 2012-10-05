<?php
/**
 * Adminhtml backup files list container block
 *
 * @category   Kega
 * @package    Kega_ProjectManagement
 */
class Kega_ProjectManagement_Block_Adminhtml_Backupfiles_List extends Mage_Adminhtml_Block_Widget_Container
{
    protected $_backButtonLabel = 'Back';
    protected $_blockGroup = 'adminhtml';


    public function __construct()
    {
        parent::__construct();

        $this->_controller = 'projectmanagement';
        $this->_headerText = Mage::helper('projectmanagement')->__('View Backup Files');

        $this->setTemplate('widget/grid/container.phtml');



    }

    protected function _prepareLayout()
    {
        $this->setChild( 'grid',
            $this->getLayout()->createBlock('projectmanagement/adminhtml_backupfiles_grid',
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
}