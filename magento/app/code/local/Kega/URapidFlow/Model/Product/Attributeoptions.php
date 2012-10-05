<?php
/**
 * This class is used build the csv file with the products attribute options(1)
 * in a format required by the rapidflow plugin
 *
 * (1)EAO: EAV Attribute Option
 *
 * @see http://www.unirgy.com/wiki/urapidflow/fixed_row_format
 *
 */
class Kega_URapidFlow_Model_Product_Attributeoptions extends Mage_Core_Model_Abstract
{
	// Todo: Check if we can load the default attributeset from DB.
	public function getDefaultAttributeSet()
    {
        // this doesn't work: there are more than one attribute Set with name Default - don't know why
        /*$attrSetName = 'Default';
		$attributeSetId = Mage::getModel('eav/entity_attribute_set')
			->load($attrSetName, 'attribute_set_name')
			->getAttributeSetId();

        return  $attributeSetId;*/

        return 4;
    }


	/**
	 * Returns an array with attributes and options (only attributes that have options)
	 * @return array
	 */
	public function getProductAttributesOptions()
    {
        $setId = self::getDefaultAttributeSet();
        $attributes = Mage::getModel('catalog/product')->getResource()
                ->loadAllAttributes()
                ->getSortedAttributes();
        $skipAttributes = array('media_image', 'gallery',
                                'type', 'sku',
                                'required_options', 'has_options',
                                'image_label', 'thumbnail_label',
                                'old_id',
                                );

        $result = array();

        foreach ($attributes as $attribute) {
            /* @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
            if ( (!$attribute->getId() || $attribute->isInSet($setId))
                && !in_array($attribute->getName(), $skipAttributes)) {

                if (!$attribute->getId()) continue;

				if (!$attribute->usesSource()) continue;

                $result[$attribute->getAttributeCode()] = array(
                    'attribute_id' => $attribute->getId(),
                    'code'         => $attribute->getAttributeCode(),
                    'type'         => ($attribute->getFrontendInput() == 'select')? 'select': 'text',
                    'name'     => $attribute->getName(),
                );

                $options = array();
                $attrOptions = $attribute->getSource()->getAllOptions(false);

				foreach ($attrOptions as $option) {
					if($option['value'] === '' || is_array($option['value'])) continue;

					// we use label as a key so we can easyly find out when we parse the product import
					// if an attribute option name exists or not in magento
					$result[$attribute->getAttributeCode()]['options'][$option['label']] = $option['value'];
				}
            }
        }

        return $result;

    }


}