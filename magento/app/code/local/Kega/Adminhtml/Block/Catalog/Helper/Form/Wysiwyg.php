<?php

/**
 * @category   Kega
 * @package    Kega_Adminhtml
 */
class Kega_Adminhtml_Block_Catalog_Helper_Form_Wysiwyg extends Mage_Adminhtml_Block_Catalog_Helper_Form_Wysiwyg
{
    /**
     * Check whether wysiwyg enabled or not
     * Extended: to show the wysiwyg on category admin form
     *
     * @return boolean
     */
    public function getIsWysiwygEnabled()
    {
		$attributeModel = $this->getEntityAttribute()->getData('attribute_model');
		$attributeCode = $this->getEntityAttribute()->getData('attribute_code');

        if ($attributeModel == 'catalog/resource_eav_attribute' && $attributeCode == 'description') {
            $descriptionIsWysiwygEnabled = true;
            return (bool)(Mage::getSingleton('cms/wysiwyg_config')->isEnabled() && $descriptionIsWysiwygEnabled);
        }

		return (bool)(Mage::getSingleton('cms/wysiwyg_config')->isEnabled() && $this->getEntityAttribute()->getIsWysiwygEnabled());
    }
}
