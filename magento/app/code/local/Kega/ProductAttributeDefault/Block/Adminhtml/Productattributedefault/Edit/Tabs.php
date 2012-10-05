<?php
/**
 *
 * @category Kega
 * @package  Kega_ProductAttributeDefault
 */
?>
<?php
class Kega_ProductAttributeDefault_Block_Adminhtml_Productattributedefault_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('productattributedefault_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('kega_productattributedefault')->__('General'));
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
    }


    protected function _beforeToHtml()
    {
        $this->addTab('form_section', array(
            'label'     => Mage::helper('kega_productattributedefault')->__('Regelgegevens'),
            'title'     => Mage::helper('kega_productattributedefault')->__('Regelgegevens'),
            'content'   => $this->getLayout()->createBlock('kega_productattributedefault/adminhtml_productattributedefault_edit_tab_form')->toHtml(),
        ));

        $this->addTab('rules', array(
            'label'     => Mage::helper('kega_productattributedefault')->__('Voorwaarden'),
            'title'     => Mage::helper('kega_productattributedefault')->__('Voorwaarden'),
            'url'       => $this->getUrl('*/*/rules', array('_current' => true)),
            'class'     => 'ajax',
        ));

        $this->addTab('actions', array(
            'label'     => Mage::helper('kega_productattributedefault')->__('Acties'),
            'title'     => Mage::helper('kega_productattributedefault')->__('Acties'),
            'url'       => $this->getUrl('*/*/actions', array('_current' => true)),
            'class'     => 'ajax',
        ));


        return parent::_beforeToHtml();
    }
}
