<?php
/**
 *
 * @category Kega
 * @package  Kega_ProductAttributeDefault
 */
?>
<?php
class Kega_ProductAttributeDefault_Block_Adminhtml_Productattributedefault_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
        $this->_objectId = 'id';

        /**
         * @see Mage_Adminhtml_Block_Widget_Form_Container::_prepareLayout()
         */
        $this->_blockGroup = 'kega_productattributedefault';
        $this->_controller = 'adminhtml_productattributedefault';

        $this->_updateButton('save', 'label', Mage::helper('kega_productattributedefault')->__('Save Entry'));
        $this->_updateButton('delete', 'label', Mage::helper('kega_productattributedefault')->__('Delete Entry'));

        $this->removeButton('reset');

        $this->_addButton('download_log', array(
            'label'     => Mage::helper('adminhtml')->__('Download Log'),
             'onclick'   => 'setLocation(\'' . $this->getDownloadLogUrl() . '\')',
        ),0);

        $this->_addButton('test_run', array(
            'label'     => Mage::helper('adminhtml')->__('TestRun'),
            'onclick'   => 'setLocation(\'' . $this->getTestRunUrl() . '\')',
        ),0);

        $this->_addButton('duplicate', array(
            'label'     => Mage::helper('adminhtml')->__('Duplicate'),
            'onclick'   => 'setLocation(\'' . $this->getDuplicateUrl() . '\')',
        	'class' => 'add',
        ),0);
    }

    public function getDownloadLogUrl()
    {
        return $this->getUrl('*/*/downloadLog', array($this->_objectId => $this->getRequest()->getParam($this->_objectId)));
    }

	public function getDuplicateUrl()
    {
        return $this->getUrl('*/*/duplicateProfile', array($this->_objectId => $this->getRequest()->getParam($this->_objectId)));
    }

    public function getTestRunUrl()
    {
        return $this->getUrl('*/*/testRunProfile', array($this->_objectId => $this->getRequest()->getParam($this->_objectId)));
    }

    public function getHeaderText()
    {
        if( Mage::registry('product_attribute_default') && Mage::registry('product_attribute_default')->getId() ) {
            return Mage::helper('kega_productattributedefault')->__("Edit Entry");
        } else {
            return Mage::helper('kega_productattributedefault')->__('New Entry');
        }
    }


}
