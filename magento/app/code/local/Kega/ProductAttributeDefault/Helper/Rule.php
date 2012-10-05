<?php
/**
 * The class it's used to match product attribute default rules
 * @category Kega
 * @package  Kega_ProductAttributeDefault
 */
?>
<?php
class Kega_ProductAttributeDefault_Helper_Rule extends Mage_Core_Helper_Abstract
{

	private $_logFileDir;


    public function __construct()
    {
        $this->_logFileDir = Mage::getBaseDir('var').'/log/kega_productattributedefault';

        if (!is_dir($this->_logFileDir)) {
            mkdir($this->_logFileDir);
            chmod($this->_logFileDir, 0777);
        }
    }

	/**
	 * Returns a product collection that matches the rule
	 *
	 * @throws Mage_Core_Exception
	 *
	 * @param Kega_ProductAttributeDefault_Model_Productattributedefault
	 * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection
	 *
	 */
	public function matchProductsByRule($productAttributeDefaultModel, $storeId=0)
    {
		$rules = $productAttributeDefaultModel->getRules();

		$store = Mage::app()->getStore($storeId);

		$productCollection = Mage::getModel('catalog/product')->getCollection()
								->addAttributeToSelect('name');

		$productCollection->addStoreFilter($store);

		if (!is_array($rules) || empty($rules)) {
			Mage::throwException(sprintf("There are no conditions added for rule %s - %s. Rule processing stopped",
										 $productAttributeDefaultModel->getId(),
										 $productAttributeDefaultModel->getRuleName()
										 )
								);
		}

		foreach ($rules as $rule) {
			$attributeName = $rule['attribute_name'];
			$attributeOperator = $rule['attribute_operator'];
			$attributePattern = $rule['attribute_pattern_code'];

            // attribute pattern is a day or an attribute code
			if ($attributePattern) {
				// is a day
				if (array_key_exists($attributePattern, $productAttributeDefaultModel->getDayOptions())) {
					$date = $productAttributeDefaultModel->getDateFromDay($attributePattern);
					$productCollection->addAttributeToSelect($attributeName);
					// we use a different operator if the attribute pattern is a date value and operator is 'is'
					// @see Kega_ProductAttributeDefault_Model_Productattributedefault::OPERATOR_OPTION_FROM_TO
					if ($attributeOperator == Kega_ProductAttributeDefault_Model_Productattributedefault::OPERATOR_OPTION_IS) {
						$attributeOperator = Kega_ProductAttributeDefault_Model_Productattributedefault::OPERATOR_OPTION_FROM_TO;
					}
					$this->matchAttributeCodeAttributeValue($attributeName, $date, $attributeOperator, $productCollection);
				} else { // is an attribute
					$this->matchAttributeCodeAttributeCode($attributeName, $attributePattern, $attributeOperator, $productCollection, $storeId);
				}
			} else {

                // get attribute type
                $attributeType = Mage::helper('kega_productattributedefault')->getAttributeType($attributeName);

                // we use a different operator if the attribute pattern is a date value and operator is 'is'
				// @see Kega_ProductAttributeDefault_Model_Productattributedefault::OPERATOR_OPTION_FROM_TO
                if ($attributeType == 'date' || $attributeType == 'datetime') {
                    if ($attributeOperator == Kega_ProductAttributeDefault_Model_Productattributedefault::OPERATOR_OPTION_IS) {
						$attributeOperator = Kega_ProductAttributeDefault_Model_Productattributedefault::OPERATOR_OPTION_FROM_TO;
					}
                }

                $attributePattern = $rule['attribute_pattern_value'];

                $dateOperatorOptions = Mage::getModel('kega_productattributedefault/productattributedefault')->getDateOperatorOptions();
                if (array_key_exists($attributeOperator, $dateOperatorOptions)) {
                    $attributeOperatorDays = empty($rule['attribute_operator_days'])? '': $rule['attribute_operator_days'];
                    //Zend_Debug::dump($attributeOperator, 'attribute operator inside');
                    //Zend_Debug::dump($attributeOperatorDays, 'attribute operator days inside');
                    //Zend_Debug::dump($rule);
                    $attributePattern = $productAttributeDefaultModel->getDateFromDayAndOperator($attributePattern,
                                                                                                 $attributeOperator,
                                                                                                 $attributeOperatorDays);
                }

				$productCollection->addAttributeToSelect($attributeName);

				// use this match if attributePattern is 0,empty, etc (the attribute code cannot be empty)
				$this->matchAttributeCodeAttributeValue($attributeName, $attributePattern, $attributeOperator, $productCollection);
			}

		}

		return $productCollection;

    }


	/**
	 * Matches a rule of type: attribute_code operator attribute_value
	 *
	 * @throws Mage_Core_Exception
	 *
	 * @param string $attributeName
	 * @param string $attributePattern
	 * @param string $attributeOperator
	 *
	 * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection
	 */
	public function matchAttributeCodeAttributeValue($attributeName, $attributePattern, $attributeOperator, $productCollection)
	{
		// this is not a product attribute so we use a different query
		// "SELECT `e`.*, `cat_pro`.`position` AS `cat_index_position` FROM `catalog_product_entity` AS `e`
		// INNER JOIN `catalog_category_product` AS `cat_pro` ON cat_pro.product_id=e.entity_id AND cat_pro.category_id in ('171', '32')"
		if ($attributeName == 'category_id') {
			$categoryIds = explode(', ',$attributePattern);

			// we use a random value for alias in case we have the same rule multiple times
			$catIndexTableName = 'cat_index_'.rand(1, 1000);

			$condition = $catIndexTableName.'.product_id=e.entity_id AND '.$catIndexTableName.'.category_id in (?)';

			$productCollection->getSelect()->join(
                array($catIndexTableName => $productCollection->getTable('catalog/category_product_index')),
                $productCollection->getConnection()->quoteInto($condition, $categoryIds),
                array()
            );

            // we add DISTINCT because a product can be added to more than one $categoryIds
            // and we have identical product records in the result set -> this will cause errors in the product collection
            $productCollection->getSelect()->distinct();

			return;
		}

		switch ($attributeOperator) {
			case Kega_ProductAttributeDefault_Model_Productattributedefault::OPERATOR_OPTION_CONTAINS:
				return $this->matchContains($attributeName, $attributePattern, $productCollection);
			break;

			case Kega_ProductAttributeDefault_Model_Productattributedefault::OPERATOR_OPTION_CONTAINS_NOT:
				return $this->matchContainsNot($attributeName, $attributePattern, $productCollection);
			break;

			case Kega_ProductAttributeDefault_Model_Productattributedefault::OPERATOR_OPTION_IS_GREATER_THAN:
				return $this->matchIsGreaterThan($attributeName, $attributePattern, $productCollection);
			break;

			case Kega_ProductAttributeDefault_Model_Productattributedefault::OPERATOR_OPTION_IS_LESS_THAN:
				return $this->matchIsLessThan($attributeName, $attributePattern, $productCollection);
			break;

			case Kega_ProductAttributeDefault_Model_Productattributedefault::OPERATOR_OPTION_EQUAL_OR_LESS_THAN:
				return $this->matchEqualOrIsLessThan($attributeName, $attributePattern, $productCollection);
			break;

			case Kega_ProductAttributeDefault_Model_Productattributedefault::OPERATOR_OPTION_EQUAL_OR_GREATER_THAN:
				return $this->matchEqualOrIsGreaterThan($attributeName, $attributePattern, $productCollection);
			break;

			case Kega_ProductAttributeDefault_Model_Productattributedefault::OPERATOR_OPTION_IS:
				return $this->matchIs($attributeName, $attributePattern, $productCollection);
			break;

			case Kega_ProductAttributeDefault_Model_Productattributedefault::OPERATOR_OPTION_IS_NOT:
				return $this->matchIsNot($attributeName, $attributePattern, $productCollection);
			break;

			case Kega_ProductAttributeDefault_Model_Productattributedefault::OPERATOR_OPTION_FROM_TO:
				return $this->matchFromTo($attributeName, $attributePattern, $productCollection);
			break;

            case Kega_ProductAttributeDefault_Model_Productattributedefault::OPERATOR_OPTION_DATE_IS:
				return $this->matchFromTo($attributeName, $attributePattern, $productCollection);
			break;

            case Kega_ProductAttributeDefault_Model_Productattributedefault::OPERATOR_OPTION_DATE_BEFORE:
				return $this->matchDateBefore($attributeName, $attributePattern, $productCollection);
			break;

            case Kega_ProductAttributeDefault_Model_Productattributedefault::OPERATOR_OPTION_DATE_AFTER:
				return $this->matchDateAfter($attributeName, $attributePattern, $productCollection);
			break;

            case Kega_ProductAttributeDefault_Model_Productattributedefault::OPERATOR_OPTION_DATE_X_DAYS_BEFORE:
				return $this->matchFromTo($attributeName, $attributePattern, $productCollection);
			break;

            case Kega_ProductAttributeDefault_Model_Productattributedefault::OPERATOR_OPTION_DATE_X_DAYS_AFTER:
				return $this->matchFromTo($attributeName, $attributePattern, $productCollection);
			break;

			case Kega_ProductAttributeDefault_Model_Productattributedefault::OPERATOR_OPTION_IS_NULL:
				return $this->matchIsNull($attributeName, $attributePattern, $productCollection);

			default:
				$msg = $this->__('Invalid attribute operator: %s', $attributeOperator);
				throw new Mage_Core_Exception($msg);
		}
	}


	/**
	 * Matches a rule of type: attribute_code operator attribute_code
	 * This only works with EAV type attributes at the moment
	 *
	 * @throws Mage_Core_Exception
	 *
	 * @param string $attributeName
	 * @param string $attributePattern
	 * @param string $attributeOperator
	 *
	 * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection
	 **/
	public function matchAttributeCodeAttributeCode($attributeName, $attributePattern, $attributeOperator, $productCollection, $storeId = 0)
	{
		$productResource = Mage::getResourceSingleton('catalog/product');

		$attribute1 = $attributeName;
		$attribute2 = $attributePattern;

		$attributesToJoin = array(
			$attribute1,
			$attribute2,
		);

		// we define random table names to have different table names in case the same attribute_name is selected in more than one rule
		// else we have an error such as: You cannot define a correlation name '_table_special_price' more than once
		$attributeTables = array(
			$attribute1 => '_table_'.$attribute1.'_'.substr(md5(microtime()), rand(1, 10), 7),
			$attribute2 => '_table_'.$attribute2.'_'.substr(md5(microtime()), rand(11, 20), 7),
		);


		// EAV :(
		// It builds an sql like the one below (the exact format depends on the attributes are static or not):
		// In this case: sku static, price not static
		//SELECT `_table_price`.`value` AS `price`, `e`.`sku`, `e`.*
		//FROM `catalog_product_entity` AS `e`
		//INNER JOIN `catalog_product_entity` AS `_table_sku`
		//	ON _table_sku.entity_id=e.entity_id
		//INNER JOIN `catalog_product_entity_decimal` AS `_table_price`
		//	ON _table_price.entity_id=e.entity_id
		//	AND _table_price.store_id = 0
		//	AND _table_price.attribute_id = 64
		//WHERE (e.sku <= _table_price.value)
		foreach ($attributesToJoin as $attributeToJoin) {

			$attr_code = $attributeToJoin;
			$attribute = $productResource->getAttribute($attr_code);
			$attrAttrId = $attribute->getAttributeId();

			$attrTableName = $attributeTables[$attr_code];

			$attrTable = $attribute->getBackend()->getTable();

			if ($attribute->getBackend()->isStatic()) {
				$attrField = $attr_code;
				$attrCondition = '';
				$from = array($attr_code => 'e.'.$attr_code);

			} else {
				$attrField = 'value';
				$attrCondition = ' AND '.$attrTableName.'.store_id = ' . $storeId . ' AND '.$attrTableName.'.attribute_id = '.$attrAttrId;
				$from = array($attr_code => $attrTableName.'.value');
			}

			$productCollection->getSelect()->joinInner(
				array($attrTableName => $attrTable),
				$attrTableName.'.entity_id=e.entity_id'
					.$attrCondition,
			   array()
			);

			$productCollection->getSelect()->from("", $from);
		}

		if ($productResource->getAttribute($attribute1)->getBackend()->isStatic()) {
			$attribute1Operand = 'e.'.$attribute1;
		} else {
			$attribute1Operand = $attributeTables[$attribute1].'.value';
		}

		if ($productResource->getAttribute($attribute2)->getBackend()->isStatic()) {
			$attribute2Operand = 'e.'.$attribute2;
		} else {
			$attribute2Operand = $attributeTables[$attribute2].'.value';
		}

		switch ($attributeOperator) {
			case Kega_ProductAttributeDefault_Model_Productattributedefault::OPERATOR_OPTION_IS:
				$productCollection->getSelect()->where($attribute1Operand. ' = ' . $attribute2Operand);
			break;

			case Kega_ProductAttributeDefault_Model_Productattributedefault::OPERATOR_OPTION_IS_GREATER_THAN:
				$productCollection->getSelect()->where($attribute1Operand. ' > ' . $attribute2Operand);
			break;

			case Kega_ProductAttributeDefault_Model_Productattributedefault::OPERATOR_OPTION_IS_LESS_THAN:
				$productCollection->getSelect()->where($attribute1Operand . ' < ' . $attribute2Operand);
			break;

			case Kega_ProductAttributeDefault_Model_Productattributedefault::OPERATOR_OPTION_EQUAL_OR_LESS_THAN:
				$productCollection->getSelect()->where($attribute1Operand . ' <= ' . $attribute2Operand);
			break;

			case Kega_ProductAttributeDefault_Model_Productattributedefault::OPERATOR_OPTION_EQUAL_OR_GREATER_THAN:
				$productCollection->getSelect()->where($attribute1Operand . ' >= ' . $attribute2Operand);
			break;

			default:
				$msg = $this->__('Invalid attribute operator: %s', $attributeOperator);
				throw new Mage_Core_Exception($msg);
		}

		return $productCollection;
	}


	public function matchContains($attributeName, $attributePattern, $productCollection)
	{
		// get value ids that have labels that match the pattern
		$attributeValueIds = $this->getAttributeOptionValueIdsByLabel($attributeName, $attributePattern);
		// not a select attribute
		if (!$attributeValueIds) {
			if (strpos($attributePattern, '*') !== FALSE
				|| strpos($attributePattern, '?') !== FALSE) {

				//  10009??6* -> ^10009..6.*$
				$attributePattern = str_replace(array('*','?'), array('.*','.'), $attributePattern);
				$attributePattern = '^'.$attributePattern.'$';
				$productCollection->addFieldToFilter($attributeName, array('regexp' => $attributePattern));
			} else {
				$productCollection->addFieldToFilter($attributeName, array('eq' => $attributePattern));
			}
		} else {
			$productCollection->addFieldToFilter($attributeName, array('in' => $attributeValueIds));
		}

		return $productCollection;
	}

	public function matchContainsNot($attributeName, $attributePattern, $productCollection)
	{
		// get value ids that have labels that match the pattern
		$attributeValueIds = $this->getAttributeOptionValueIdsByLabel($attributeName, $attributePattern);
		// not a select attribute
		if (!$attributeValueIds) {
			if (strpos($attributePattern, '*') !== FALSE
				|| strpos($attributePattern, '?') !== FALSE) {

				//  10009??6* -> ^10009..6.*$
				$attributePattern = str_replace(array('*','?'), array('.*','.'), $attributePattern);
				$attributePattern = '^'.$attributePattern.'$';
				$productCollection->addFieldToFilter($attributeName, array('notregexp' => $attributePattern));
			} else {
				$productCollection->addFieldToFilter($attributeName, array('neq' => $attributePattern));
			}
		} else {
			$productCollection->addFieldToFilter($attributeName, array('nin' => $attributeValueIds));
		}

		return $productCollection;
	}


	public function matchIsLessThan($attributeName, $attributePattern, $productCollection)
	{
		$productCollection->addFieldToFilter($attributeName, array('lt' => $attributePattern));


		return $productCollection;
	}

	public function matchIsGreaterThan($attributeName, $attributePattern, $productCollection)
	{
		$productCollection->addFieldToFilter($attributeName, array('gt' => $attributePattern));


		return $productCollection;
	}


	public function matchEqualOrIsLessThan($attributeName, $attributePattern, $productCollection)
	{
		$productCollection->addFieldToFilter($attributeName, array('lteq' => $attributePattern));

		return $productCollection;
	}


	public function matchEqualOrIsGreaterThan($attributeName, $attributePattern, $productCollection)
	{
		$productCollection->addFieldToFilter($attributeName, array('gteq' => $attributePattern));

		return $productCollection;
	}

	public function matchIsNull($attributeName, $attributePattern, $productCollection)
	{
		$productCollection->addAttributeToFilter($attributeName, array('null'=>true), 'left');

		return $productCollection;
	}

	public function matchFromTo($attributeName, $attributePattern, $productCollection)
	{
        if (strpos($attributeName, ':') !== FALSE) {
            Mage::throwException("Invalid date value %s for attribute name %s", $attributePattern, $attributeName);
        }
		$productCollection->addFieldToFilter($attributeName,
						array(
							'from' => $attributePattern. ' 00:00:00',
							'to' => $attributePattern. ' 23:59:59',
							'datetime' => true,
						)
					);
		return $productCollection;
	}

	/**
	 * Handle "is" conditions.
	 *
	 * Try to find options-value's that have a label that matches the pattern.
	 * if matches are found use id of option-value to filter.
	 *
	 * If no matches are found, check attribute type:
	 * - For Select attributes return empty collection (filter option-id -1)
	 * - For other attributes filter on pattern.
	 *
	 * @param string $attributeName
	 * @param string $attributePattern
	 * @param Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection $productCollection
	 * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection
	 */
	public function matchIs($attributeName, $attributePattern, $productCollection)
	{
		$attribute = Mage::getModel('catalog/product')->getResource()->getAttribute($attributeName);

		// get value ids that have labels that match the pattern
		$attributeValueIds = $this->getAttributeOptionValueIdsByLabel($attributeName, $attributePattern);

		// not a select attribute
		if (!$attributeValueIds) {

			// for select attributes, no matching values are found. return empty collection.. (filter for option_is == -1)
			if($attribute->getFrontendInput() == 'select' && $attribute->getBackendType() == 'int') {
				$productCollection->addFieldToFilter($attributeName, array('in' => array(-1)));
			} else {
				$productCollection->addFieldToFilter($attributeName, array('eq' => $attributePattern));
			}
		} else {
			// Multiselect values are comma seperated stored in the database,
			// so we have to use MySQL find_in_set to match the attribute pattern.
			$productCollection->addFieldToFilter($attributeName, array('finset' => array(implode(',', $attributeValueIds))));
		}

		return $productCollection;
	}

	/**
	 * Handle "isNot" conditions.
	 *
	 * Try to find options-value's that have a label that matches the pattern.
	 * if matches are found use id of option-value to filter.
	 *
	 * If no matches are found, check attribute type:
	 * - For Select attributes return empty collection (filter option-id -1)
	 * - For other attributes filter on pattern.
	 *
	 * @param string $attributeName
	 * @param string $attributePattern
	 * @param Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection $productCollection
	 * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection
	 */
	public function matchIsNot($attributeName, $attributePattern, $productCollection)
	{
		$attribute = Mage::getModel('catalog/product')->getResource()->getAttribute($attributeName);

		// get value ids that have labels that match the pattern
		$attributeValueIds = $this->getAttributeOptionValueIdsByLabel($attributeName, $attributePattern);

		if (!$attributeValueIds) {

			// for select attributes, no matching values are found. return empty collection.. (filter for option_is == -1)
			if($attribute->getFrontendInput() == 'select' && $attribute->getBackendType() == 'int') {
				$productCollection->addFieldToFilter($attributeName, array('in' => array(-1)));
			} else {
				$productCollection->addFieldToFilter($attributeName, array('neq' => $attributePattern));
			}
		} else {
			$productCollection->addFieldToFilter($attributeName, array('nin' => $attributeValueIds));
		}

		return $productCollection;
	}

    public function matchDateBefore($attributeName, $attributePattern, $productCollection)
	{
        if (strpos($attributeName, ':') !== FALSE) {
            Mage::throwException("Invalid date value %s for attribute name %s", $attributePattern, $attributeName);
        }
		$productCollection->addFieldToFilter($attributeName,
						array(
							'lt' => $attributePattern. ' 00:00:00',
							'datetime' => true,
						)
					);
		return $productCollection;
	}

    public function matchDateAfter($attributeName, $attributePattern, $productCollection)
	{
        if (strpos($attributeName, ':') !== FALSE) {
            Mage::throwException("Invalid date value %s for attribute name %s", $attributePattern, $attributeName);
        }
		$productCollection->addFieldToFilter($attributeName,
						array(
							'gt' => $attributePattern. ' 00:00:00',
							'datetime' => true,
						)
					);
		return $productCollection;
	}




	/**
     * Retrieves the attribute option values ids of $attributeCode
     * with option labels that matches the $attributeLabelPattern pattern
     * Eg: $attributeCode = color; $attributeLabelPattern = 'X*' returns an array
     * with the option value ids for the following option labels X BLUE, XLIGHTGREEN...
     *
     * Returns empty array if no match is found. returns false if attribute is not a select type
     *
     * @throws Exeption if attribute with $attributeCode is not found
     *
     * @param string $attributeCode
     * @param string $attributeValue - this is a pattern Eg: $attributeValue = 'X*';
     *
     * return array|bool - returns false if attribute is not a select type
     *
     */
    public function getAttributeOptionValueIdsByLabel($attributeCode, $attributeLabelPattern)
    {
        $logFilePath = $this->_logFileDir. DS .'attributes_'.date('Y.m.d.H.i.s').'.log';
        if (is_file($logFilePath)) {
            sleep(1);
            $logFilePath = $this->_logFileDir. DS .'attributes_'.date('Y.m.d.H.i.s').'.log';
        }
        $writer = new Zend_Log_Writer_Stream($logFilePath);
        $logger = new Zend_Log($writer);

        $attribute = Mage::getModel('catalog/product')->getResource()->getAttribute($attributeCode);

        $attribute->setStoreId(0);// we use the admin values for rule matching

        if (!$attribute) throw new Exception(sprintf("Invalid attribute code: %s", $attributeCode));

        if (!$attribute->usesSource()) return false;

        $valueIds = array();

        // check if attribute label matches the pattern
        $msg = '';
        $attrOptions = $attribute->getSource()->getAllOptions(false);

        foreach ($attrOptions as $option) {
            $pattern = str_replace(array('*', '?'), array('.*', '.'), $attributeLabelPattern);
            $pattern = '/^'.$pattern.'$/';
            preg_match($pattern, $option['label'], $matches);

            if (!empty($matches)) {
                $valueIds[] = $option['value'];
                $msg .= sprintf('Match found for pattern %s - label: %s, value: %s ', $pattern, $option['label'], $option['value']).PHP_EOL;
            }
        }

        if ($msg) {
           $logger->info($msg);
        }

        return $valueIds;
    }


}