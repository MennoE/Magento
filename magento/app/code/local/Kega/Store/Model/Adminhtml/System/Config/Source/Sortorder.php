<?php
/**
 * @category    Mage
 * @package     Mage_Shipping
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */
class Kega_Store_Model_Adminhtml_System_Config_Source_Sortorder
{
    public function toOptionArray()
    {
        return array(
            array(
				'value' => 'ASC',
				'label' => Mage::helper('store')->__('Ascending')
			),
            array(
				'value' => 'DESC',
				'label' => Mage::helper('store')->__('Descending')
			),
        );
    }
}
