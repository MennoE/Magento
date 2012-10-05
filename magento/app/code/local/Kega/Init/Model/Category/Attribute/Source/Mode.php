<?php

/**
 * Catalog category landing page attribute source
 *
 * @category   Mage
 * @package    Mage_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Kega_Init_Model_Category_Attribute_Source_Mode extends Mage_Catalog_Model_Category_Attribute_Source_Mode
{
    /**
     * Extended to add code to dispatch an event - this can be used later
     * in an observer to add new display modes
     */
    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options = array(
                array(
                    'value' => Mage_Catalog_Model_Category::DM_PRODUCT,
                    'label' => Mage::helper('catalog')->__('Products only'),
                ),
                array(
                    'value' => Mage_Catalog_Model_Category::DM_PAGE,
                    'label' => Mage::helper('catalog')->__('Static block only'),
                ),
                array(
                    'value' => Mage_Catalog_Model_Category::DM_MIXED,
                    'label' => Mage::helper('catalog')->__('Static block and products'),
                ),
            );

			Mage::dispatchEvent('adminhtml_category_display_mode_options', array('mode' => $this));
        }
        return $this->_options;
    }


    /**
     * New method - add new display mode option
     *
     * @param array array ('label' => 'option label', 'value' => 'option value')
     * @return Mage_Catalog_Model_Category_Attribute_Source_Mode
     */
    public function addOption($option)
    {
        $this->_options[] = $option;
        return $this;
    }
}
