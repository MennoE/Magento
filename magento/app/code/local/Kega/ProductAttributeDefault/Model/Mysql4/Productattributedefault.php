<?php
/**
 *
 * @category Kega
 * @package  Kega_ProductAttributeDefault
 */
?>
<?php
class Kega_ProductAttributeDefault_Model_Mysql4_Productattributedefault extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('kega_productattributedefault/productattributedefault','productattributedefault_id');
    }

    /**
     * overrided to set the created_on, updated_on fields
     */
    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        if (!$object->getCreatedOn()) {
            $object->setCreatedOn($this->formatDate(time()));
        }
        $object->setUpdatedOn($this->formatDate(time()));

        $rules = $object->getData('rules');

        if (!is_array($rules) || empty($rules)) {
            Mage::throwException(Mage::helper('kega_productattributedefault')->__('Please add at least one condition'));
        }


        foreach ($rules as $index => $ruleInfo) {
            // we always have an empty condition - it's a css hidden condition
            // that holds the condition's html template used to add new conditions
            if (empty($rules[$index]['attribute_name'])) {
                unset($rules[$index]);
                continue;
            }

            if ($rules[$index]['attribute_name'] == $rules[$index]['attribute_pattern_code']) {
                $msg = Mage::helper('kega_productattributedefault')->__('The attribute codes must be different from one another.');
                Mage::throwException($msg);
            }

            if (empty($rules[$index]['attribute_operator'])) {
                $msg = Mage::helper('kega_productattributedefault')->__('Please select one of the values: %s.',
                                                                        implode(', ', Kega_ProductAttributeDefault_Model_Productattributedefault::getOperatorOptions())
                                                                        );
                Mage::throwException($msg);
            }

            if (!empty($rules[$index]['attribute_operator_days']) && !is_numeric($rules[$index]['attribute_operator_days'])) {
                $msg = Mage::helper('kega_productattributedefault')->__('Please add a numeric value for x days before/x days after value. Your value is %s.',
                                                                        $rules[$index]['attribute_operator_days']
                                                                        );
                Mage::throwException($msg);
            }

            $restrictedOperators = Mage::getModel('kega_productattributedefault/productattributedefault')
                                        ->getOperatorsValidAttrCodeAttrCodeOptions();
            if (!empty($rules[$index]['attribute_pattern_code']) && in_array($rules[$index]['attribute_pattern_code'], $restrictedOperators)) {
                $msg = Mage::helper('kega_productattributedefault')->__('Please select only one of these values: %s.',
                                                                        implode(', ', $restrictedOperators)
                                                                        );
                Mage::throwException($msg);
            }
        }
        $rules = array_values($rules);
        $object->setData('rules', json_encode($rules));
        $object->setData('apply_to_stores', json_encode($object->getData('apply_to_stores')));
        $object->setData('product_types', json_encode($object->getData('product_types')));

        parent::_beforeSave($object);
    }

    /**
     * overrided to save the attributes and categories
     */
    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {

        $this->_saveCategories($object);
        $this->_saveAttributes($object);
        $this->_saveDynamicAttributes($object);

        // restore format json->array
        $this->_loadRules($object);
        $this->_loadApplyToStores($object);
        $this->_loadProductTypes($object);

        return parent::_afterSave($object);
    }

    /**
     * overrided to set the rules, attributes and categories
     */
    protected function _afterLoad(Mage_Core_Model_Abstract $object)
    {
    	$this->_loadCategories($object);
        $this->_loadAttributes($object);
        $this->_loadApplyToStores($object);
        $this->_loadProductTypes($object);
        $this->_loadDynamicAttributes($object);
        $this->_loadRules($object);

        $this->_setChangedAttributes($object);


        return parent::_afterLoad($object);
    }

    protected function _setChangedAttributes($object)
    {
    	$changedAttributes = array();
    	if ($object->getData('category_add_id') || $object->getData('category_remove_id')) {
    		$changedAttributes[] = 'category_ids';
    	}

    	$attributes = $object->getData('attributes');
    	$attributesDynamic = $object->getData('attributes_dynamic');

    	foreach ($attributes as $attribute) {
    		$changedAttributes[] = $attribute['attribute_code'];
    	}

    	foreach ($attributesDynamic as $attribute) {
    		$changedAttributes[] = $attribute['attribute_code'];
    	}
    	$object->setData('changed_attributes', $changedAttributes);
    }


    private function _loadRules($object)
    {
        $rules = $object->getData('rules');
        if ($rules) {
            $rules = json_decode($rules, $assoc = true);

            $rules = (!is_array($rules))? array(): $rules;
            $object->setData('rules', $rules);
        } else {
            $object->setData('rules', array());
        }
    }


	private function _loadApplyToStores($object)
    {
        $applyToStores = $object->getData('apply_to_stores');
        if ($applyToStores) {
            $applyToStores = json_decode($applyToStores, $assoc = true);

            $applyToStores = (!is_array($applyToStores))? array(): $applyToStores;
            $object->setData('apply_to_stores', $applyToStores);
        } else {
            $object->setData('apply_to_stores', array());
        }
    }

    private function _loadProductTypes($object)
    {
        $productTypes = $object->getData('product_types');
        if ($productTypes) {
            $productTypes = json_decode($productTypes, $assoc = true);

            $productTypes = (!is_array($productTypes))? array(): $productTypes;
            $object->setData('product_types', $productTypes);
        } else {
            $object->setData('product_types', array());
        }
    }


    /**
     * Saves the attributes
     *
     */
    private function _saveAttributes($object)
    {
        $productAttributesTable = $this->getTable('kega_productattributedefault/productattributedefault_attributes');

        $attributes = $object->getData('attributes');

        // delete previous values
        $this->_getWriteAdapter()->delete($productAttributesTable,
            $this->_getWriteAdapter()->quoteInto('productattributedefault_id=?', $object->getId())
        );

        if (!is_array($attributes)) return;

        foreach ($attributes as $attribute) {
            if (empty($attribute['attribute_code'])) continue;
            $recordData['productattributedefault_id'] = $object->getId();
            $recordData['attribute_code'] = $attribute['attribute_code'];
            $recordData['attribute_value'] = $attribute['attribute_value'];
            $this->_getWriteAdapter()->insert($productAttributesTable, $recordData);
        }
    }

/**
     * Saves the dynamic attributes
     *
     */
    private function _saveDynamicAttributes($object)
    {
        $productAttributesTable = $this->getTable('kega_productattributedefault/productattributedefault_attributes_dynamic');

        $attributes = $object->getData('attributes_dynamic');

        // delete previous values
        $this->_getWriteAdapter()->delete($productAttributesTable,
            $this->_getWriteAdapter()->quoteInto('productattributedefault_id=?', $object->getId())
        );

        if (!is_array($attributes)) return;

        foreach ($attributes as $attribute) {
            if (empty($attribute['attribute_code']) || $attribute['attribute_value'] === '') continue;
            $recordData['productattributedefault_id'] = $object->getId();
            $recordData['attribute_code'] = $attribute['attribute_code'];
            $recordData['attribute_value'] = $attribute['attribute_value'];
            $this->_getWriteAdapter()->insert($productAttributesTable, $recordData);
        }
    }

    /**
     * Saves the add and remove categories
     *
     */
    private function _saveCategories($object)
    {
        $productCategoriesTable = $this->getTable('kega_productattributedefault/productattributedefault_categories');

        // delete previous values
        $this->_getWriteAdapter()->delete($productCategoriesTable,
            $this->_getWriteAdapter()->quoteInto('productattributedefault_id=?', $object->getId())
        );


        $categoryActionType = array(
        	'add' => $object->getData('category_add_id'),
        	'remove' => $object->getData('category_remove_id'),
        );

        foreach ($categoryActionType as $actionType => $categoryIds) {
        	if (empty($categoryIds)) continue;

	        if (!is_array($categoryIds)) {
	            $categoryIds = explode(',', $categoryIds);
	        }

	        if (!is_array($categoryIds)) return;

	        // we have duplicate category ids - don't know why
	        $categoryIds = array_unique($categoryIds);

	        foreach ($categoryIds as $categoryId) {
	            if (empty($categoryId)) continue;
	            $recordData['productattributedefault_id'] = $object->getId();
	            $recordData['category_id'] = $categoryId;
	            $recordData['action_type'] = $actionType;
	            $this->_getWriteAdapter()->insert($productCategoriesTable, $recordData);
	        }
        }
    }



    /**
     * Loads the categories
     *
     */
    private function _loadCategories($object)
    {
        $productCategoriesTable = $this->getTable('kega_productattributedefault/productattributedefault_categories');

        $categoryIds = array('add' => array(), 'remove' => array());

        $select = $this->_getReadAdapter()->select()
                        ->from($productCategoriesTable, array('category_id', 'action_type'))
                        ->where('productattributedefault_id = ?', $object->getId())
                        ;
        $result = $select->query();

        while ($row = $result->fetch(Zend_Db::FETCH_ASSOC)) {
            $categoryIds[$row['action_type']][] = $row['category_id'];
        }
		$object->setData('category_add_id', $categoryIds['add']);
		$object->setData('category_remove_id', $categoryIds['remove']);
    }


    /**
     * Loads the attributes
     *
     */
    private function _loadAttributes($object)
    {
        $productAttributesTable = $this->getTable('kega_productattributedefault/productattributedefault_attributes');

        $attributes = array();

        $select = $this->_getReadAdapter()->select()
                        ->from($productAttributesTable, array('productattributedefault_attribute_id',
                                                              'attribute_code',
                                                              'attribute_value'))
                        ->where('productattributedefault_id = ?', $object->getId())
                        ;
        $result = $select->query();

        while ($row = $result->fetch(Zend_Db::FETCH_ASSOC)) {
            $attributes[] = array('id' => $row['productattributedefault_attribute_id'],
                                  'attribute_code' => $row['attribute_code'],
                                  'attribute_value' => $row['attribute_value']);
        }


        $object->setData('attributes', $attributes);
    }


 	/**
     * Loads the dynamic attributes
     *
     */
    private function _loadDynamicAttributes($object)
    {
        $productAttributesTable = $this->getTable('kega_productattributedefault/productattributedefault_attributes_dynamic');

        $attributes = array();

        $select = $this->_getReadAdapter()->select()
                        ->from($productAttributesTable, array('productattributedefault_attribute_dynamic_id',
                                                              'attribute_code',
                                                              'attribute_value'))
                        ->where('productattributedefault_id = ?', $object->getId())
                        ;
        $result = $select->query();

        while ($row = $result->fetch(Zend_Db::FETCH_ASSOC)) {
            $attributes[] = array('id' => $row['productattributedefault_attribute_dynamic_id'],
                                  'attribute_code' => $row['attribute_code'],
                                  'attribute_value' => $row['attribute_value']);
        }

        $object->setData('attributes_dynamic', $attributes);
    }


    /**
     * Saves the manual changed product attributes
     *
     * @param int $productId
     * @param array $changes
     * @param int $storeId
     */
    public function saveManualProductAttributeChanges($productId, $changes, $storeId = 0)
    {
        $manualProductChangesTable = $this->getTable('kega_productattributedefault/manual_product_changes');

        // get previous values
        $previousData = $this->getManualProductAttributeChanges($productId, $storeId);

        $allChanges = $changes;

        // merge the new data with the old data
        if (!empty($previousData) && is_array($previousData['changed_attributes'])) {
        	$allChanges = array_merge($previousData['changed_attributes'], $changes);
        } else {
        	$allChanges = $changes;
        }

        $allChanges = Mage::helper('core')->jsonEncode($allChanges);

        if (!empty($previousData)) {
            $recordData['changed_attributes'] = $allChanges;
            $recordData['updated_at'] = date('Y-m-d h:i:s');
            $this->_getWriteAdapter()->update($manualProductChangesTable, $recordData, array('product_id' => $productId));
        } else {
        	$recordData['product_id'] = $productId;
        	$recordData['changed_attributes'] = $allChanges;
            $recordData['updated_at'] = date('Y-m-d h:i:s');
            $this->_getWriteAdapter()->insert($manualProductChangesTable, $recordData);
        }
    }


	/**
     * Gets the manual changed product attributes
     *
     * @param int $productId
     * @param array $changes
     * @param int $storeId
     * @return array
     */
    public function getManualProductAttributeChanges($productId, $storeId = 0)
    {
        $manualProductChangesTable = $this->getTable('kega_productattributedefault/manual_product_changes');

        // get previous values
        $select = $this->_getReadAdapter()->select()
                        ->from($manualProductChangesTable, array('product_id', 'changed_attributes', 'updated_at'))
                        ->where('product_id = ?', $productId)
                        ;
        $result = $select->query();

        $row = $result->fetch(Zend_Db::FETCH_ASSOC);

        if ($row) {
        	$row['changed_attributes'] = Mage::helper('core')->jsonDecode($row['changed_attributes']);
        }

        return $row;
    }



}