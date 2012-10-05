<?php
/**
 *
 * @category Kega
 * @package  Kega_ProductAttributeDefault
 */
?>
<?php
class Kega_ProductAttributeDefault_Model_Productattributedefault extends Mage_Core_Model_Abstract
{
    const IS_ENABLED = '1';
	const IS_NOT_ENABLED = '0';

	const IS_DRY_RUN = '1';
	const IS_NOT_DRY_RUN = '0';

	const DAY_OPTION_TODAY = 'day-today';
	const DAY_OPTION_YESTERDAY = 'day-yesterday';

    const OPERATOR_OPTION_IS = 'is';
    const OPERATOR_OPTION_IS_NOT = 'is_not';
    const OPERATOR_OPTION_IS_GREATER_THAN = 'greater_than';
    const OPERATOR_OPTION_IS_LESS_THAN = 'less_than';
    const OPERATOR_OPTION_EQUAL_OR_GREATER_THAN = 'equal_or_greater_than';
    const OPERATOR_OPTION_EQUAL_OR_LESS_THAN = 'equal_or_less_than';
    const OPERATOR_OPTION_CONTAINS_NOT = 'contains_not';
    const OPERATOR_OPTION_CONTAINS = 'contains';
    const OPERATOR_OPTION_IS_NULL = 'is_null';
    const OPERATOR_OPTION_BELONGS_TO = 'belongs_to';
    const OPERATOR_OPTION_BELONGS_TO_NOT = 'belongs_to_not';

    const OPERATOR_OPTION_DATE_IS = 'date_is';
    const OPERATOR_OPTION_DATE_BEFORE = 'before';
    const OPERATOR_OPTION_DATE_AFTER = 'after';
    const OPERATOR_OPTION_DATE_X_DAYS_BEFORE = 'x_days_before';
    const OPERATOR_OPTION_DATE_X_DAYS_AFTER = 'x_days_after';

    // this is only used for dates selected from dayOptions to replace the is operator
    // because the actual date saved can be a date-time and then 'is' doesn't match the value;
    // Eg: day=2011-10-05 and attribute_value=2011-10-05 00:05:01
    const OPERATOR_OPTION_FROM_TO = 'from_to';

    protected $_removeCategoryLines = array();

    /**
     * Logging will be done in this file
     * @var string
     */
    protected $_logfile;

    /**
     * Indicates if current run is test run.
     * @var int
     */
    protected $_testRun;

    /**
     * TestRun output.
     * @var array
     */
    protected $_testRunOutput;

    protected function _construct()
    {
        $this->_init('kega_productattributedefault/productattributedefault');
    }

    /**
     * remove some properties - the clone this is used to duplicate a profile
     */
    public function __clone()
    {
    	$this->setLogFile(null);
        $this->setId(null);
        $this->setRuleName(null);
    }


	public function getStatusOptions()
	{
		$statuses = array (
			self::IS_ENABLED =>  Mage::helper('kega_productattributedefault')->__('Active'),
			self::IS_NOT_ENABLED =>  Mage::helper('kega_productattributedefault')->__('Inctive'),
		);

		return $statuses;
	}

	public function getDayOptions()
	{
		$options = array (
			self::DAY_OPTION_TODAY =>  Mage::helper('kega_productattributedefault')->__('today'),
			self::DAY_OPTION_YESTERDAY =>  Mage::helper('kega_productattributedefault')->__('yesterday'),
		);

		return $options;
	}

	/**
	 * Returns a date from the day - the date depends on the local timezone
	 *
	 * @throws Mage_Core_Exception if day case is not found
	 *
	 * @param string $day
	 * @return string $date
	 */
	public function getDateFromDay($day)
	{
		$date = '';
		switch ($day) {
 	    	case self::DAY_OPTION_TODAY:
 	    		   $nowTimestamp = Mage::getModel('core/date')->timestamp(time());
 		           $date = date('Y-m-d', $nowTimestamp);
 		    break;
 		    case self::DAY_OPTION_YESTERDAY:
 		          $yesterdayTimestamp = Mage::getModel('core/date')->timestamp('-1 day');
 		    	  $date = date('Y-m-d', $yesterdayTimestamp);
 		    break;
 		    default:
 		          Mage::throwException($this->__('Invalid day option: %s', $day));
 		}

 		return $date;
	}


    /**
	 * Returns a date from the day - the date depends on the local timezone
	 *
	 * @throws Mage_Core_Exception if day case is not found
	 *
	 * @param string $day
	 * @param string $attributeOperator
	 * @param int $attributeOperatorDays
	 * @return string $date
	 */
	public function getDateFromDayAndOperator($day, $attributeOperator, $attributeOperatorDays = null)
	{
		$date = '';

        //Zend_Debug::dump($day, 'day');
        //Zend_Debug::dump($attributeOperator, 'attribute operator');
        //Zend_Debug::dump($attributeOperatorDays, 'attribute operator days');

        if (empty($attributeOperatorDays)) {
            $attributeOperatorDays = 0;
        }

        switch ($attributeOperator) {
            case self::OPERATOR_OPTION_DATE_IS:
            case self::OPERATOR_OPTION_DATE_BEFORE:
            case self::OPERATOR_OPTION_DATE_AFTER:
                if ($day == 'today') {
                    $nowTimestamp = Mage::getModel('core/date')->timestamp(time());
                    $date = date('Y-m-d', $nowTimestamp);
                } else {
                    $date = $day;
                }
            break;
            case self::OPERATOR_OPTION_DATE_X_DAYS_BEFORE:
                if ($day == 'today') {
                    $xDaysBeforeTimestamp = Mage::getModel('core/date')->timestamp('-' . $attributeOperatorDays . ' day');
                    $date = date('Y-m-d', $xDaysBeforeTimestamp);
                } else {
                    $xDaysBeforeTimestamp = Mage::getModel('core/date')->timestamp($day . ' -' . $attributeOperatorDays . ' day');
                    $date = date('Y-m-d', $xDaysBeforeTimestamp);
                }
            break;
            case self::OPERATOR_OPTION_DATE_X_DAYS_AFTER:
                if ($day == 'today') {
                    $xDaysAfterTimestamp = Mage::getModel('core/date')->timestamp('+' . $attributeOperatorDays . ' day');
                    $date = date('Y-m-d', $xDaysAfterTimestamp);
                } else {
                    $xDaysAfterTimestamp = Mage::getModel('core/date')->timestamp($day . ' +' . $attributeOperatorDays . ' day');
                    $date = date('Y-m-d', $xDaysAfterTimestamp);
                }
            break;
            default:
                  Mage::throwException($this->__('Invalid day option: %s', $day));
        }

        //Zend_Debug::dump($date, 'the result');

 		return $date;
	}


	public function getDryRunOptions()
	{
		$statuses = array (
			self::IS_DRY_RUN =>  Mage::helper('kega_productattributedefault')->__('Yes'),
			self::IS_NOT_DRY_RUN =>  Mage::helper('kega_productattributedefault')->__('No'),
		);

		return $statuses;
	}


    /**
     * All available operators
     * Note: belongs to and not belongs to are not used at the moment
     *
     * @return array
     */
    public static function getOperatorOptions()
    {
        $operatorOptions = array(
						self::OPERATOR_OPTION_IS => Mage::helper('core')->__('is'),
                        self::OPERATOR_OPTION_IS_NOT => Mage::helper('core')->__('is not'),
                        self::OPERATOR_OPTION_EQUAL_OR_GREATER_THAN => Mage::helper('core')->__('equal or greater than'),
                        self::OPERATOR_OPTION_EQUAL_OR_LESS_THAN => Mage::helper('core')->__('equal or less than'),
                        self::OPERATOR_OPTION_IS_GREATER_THAN => Mage::helper('core')->__('greater than'),
                        self::OPERATOR_OPTION_IS_LESS_THAN => Mage::helper('core')->__('is less than'),
                        self::OPERATOR_OPTION_CONTAINS => Mage::helper('core')->__('contains'),
                        self::OPERATOR_OPTION_IS_NULL => Mage::helper('core')->__('is null'),
                        /*self::OPERATOR_OPTION_BELONGS_TO => Mage::helper('core')->__('belongs to'),
                        self::OPERATOR_OPTION_BELONGS_TO_NOT => Mage::helper('core')->__('does not belongs to'),*/
					);

        return array_merge($operatorOptions, self::getDateOperatorOptions());
    }


    public static function getDefaultOperatorOptions()
    {
        $operatorOptions = array(
						self::OPERATOR_OPTION_IS => Mage::helper('core')->__('is'),
                        self::OPERATOR_OPTION_IS_NOT => Mage::helper('core')->__('is not'),
                        self::OPERATOR_OPTION_EQUAL_OR_GREATER_THAN => Mage::helper('core')->__('equal or greater than'),
                        self::OPERATOR_OPTION_EQUAL_OR_LESS_THAN => Mage::helper('core')->__('equal or less than'),
                        self::OPERATOR_OPTION_IS_GREATER_THAN => Mage::helper('core')->__('greater than'),
                        self::OPERATOR_OPTION_IS_LESS_THAN => Mage::helper('core')->__('is less than'),
                        self::OPERATOR_OPTION_CONTAINS => Mage::helper('core')->__('contains'),
                        self::OPERATOR_OPTION_IS_NULL => Mage::helper('core')->__('is null'),
                        /*self::OPERATOR_OPTION_BELONGS_TO => Mage::helper('core')->__('belongs to'),
                        self::OPERATOR_OPTION_BELONGS_TO_NOT => Mage::helper('core')->__('does not belongs to'),*/
					);

        return $operatorOptions;
    }


    /**
     * Only operators available with a date type field
     *
     * @return array
     */
    public static function getDateOperatorOptions()
    {
        $operatorOptions = array(
						self::OPERATOR_OPTION_DATE_IS => Mage::helper('core')->__('is'),
                        self::OPERATOR_OPTION_DATE_BEFORE => Mage::helper('core')->__('before'),
                        self::OPERATOR_OPTION_DATE_AFTER => Mage::helper('core')->__('after'),
                        self::OPERATOR_OPTION_DATE_X_DAYS_BEFORE => Mage::helper('core')->__('x days before'),
                        self::OPERATOR_OPTION_DATE_X_DAYS_AFTER => Mage::helper('core')->__('x days after'),
					);

        return $operatorOptions;
    }

    /**
     * Operators valid with attribute-code operator attribute-code rule type
     *
     * @return array
     *
     */
    public function getOperatorsValidAttrCodeAttrCodeOptions()
    {
        $operatorOptions = array(
        				self::OPERATOR_OPTION_IS => Mage::helper('core')->__('is'),
                        self::OPERATOR_OPTION_EQUAL_OR_GREATER_THAN => Mage::helper('core')->__('equal or greater than'),
                        self::OPERATOR_OPTION_EQUAL_OR_LESS_THAN => Mage::helper('core')->__('equal or less than'),
                        self::OPERATOR_OPTION_IS_GREATER_THAN => Mage::helper('core')->__('greater than'),
                        self::OPERATOR_OPTION_IS_LESS_THAN => Mage::helper('core')->__('is less than'),
					);

        return $operatorOptions;
    }


	/**
     * Operators valid with attribute-code operator attribute-code rule type
     *
     * @return array
     *
     */
    public function getOperatorsValidAttrCodeCategoryIdOptions()
    {
        $operatorOptions = array(
                        self::OPERATOR_OPTION_IS => Mage::helper('core')->__('is'),
		);

        return $operatorOptions;
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
        return $this->getResource()->saveManualProductAttributeChanges($productId, $changes, $storeId);
    }


	/**
     * Gets the manual changed product attributes
     *
     * @param int $productId
     * @param int $storeId
     * @return array
     */
    public function getManualProductAttributeChanges($productId, $storeId = 0)
    {
        return $this->getResource()->getManualProductAttributeChanges($productId, $storeId);
    }


    /**
     * Apply the rules to the product catalog and makes the product updates
     *
     * @param string logfile
     */
    public function runProfile($logfile, $testRun = false)
    {
        $this->_logfile = $logfile;
        $this->_testRun = $testRun;

        if ($this->getData('is_enabled')
             != Kega_ProductAttributeDefault_Model_Productattributedefault::IS_ENABLED) {
             $this->_log(sprintf("Skipped rule %s - %s - not enabled",
                 $this->getId(),
                 $this->getRuleName()));
             return;
        }

        $applyToStores = $this->getData('apply_to_stores');

        $newProductDataAll = array();
        foreach ($applyToStores as $storeId) {

            // get product collection that matches the rule
			$productCollection = Mage::helper('kega_productattributedefault/rule')->matchProductsByRule($this, $storeId);
			$this->_log(sprintf('Start matching for rule (id: %s, name: %s); storeId %s',
                $this->getId(),
                $this->getRuleName(),
                $storeId));

		    $this->_log(sprintf('Sql Query Product Collection: %s',
                $productCollection->getSelectSql()),
                Zend_Log::DEBUG);

		    if (!$productCollection->getSize()) {
		         $this->_log(sprintf('End rule matching for store %s: no products found', $storeId));
		         continue;
		    }

		    $newProductData = $this->applyActionsToProductCollection($productCollection, $storeId);

		    // return testrun data if $testRun = true
		    if($testRun) {
		        return $this->_testRunOutput;
		    }

            if (Mage::helper('kega_productattributedefault')->getUseURapidflow() && !empty($newProductData)) {

                $urapidflowHelper = Mage::helper('kega_productattributedefault/urapidflow');
                $urapidflowHelper->setDefaultUrapidflowFilename($this->getId(), $storeId);
                $header = array_keys($newProductData[0]);
                $urapidflowHelper->generateProductFile($header, $newProductData);

				/**
				 * If there are categories to remove, create a seperate file.
				 * Category removal needs to be done by a Products Extra Rapidflow import.
				 * Lines in these files are build as follows:
				 * -CCP,[category-id],[product-sku]
				 */
				$removeCategories = $this->getData('category_remove_id');
				$productsExtra = !empty($removeCategories);
				if ($productsExtra) {
					$removeCategoryData = array();
					foreach ($removeCategories as $categoryId) {
						// Removal line, i.e.: -CCP,25,103005
						foreach ($newProductData as $data) {
							$removeCategoryData[] = array(
								'type'		=> '-CCP',
								'category'	=> $categoryId,
								'sku'		=> $data['sku'],
							);
						}
						$urapidflowHelper->setDefaultUrapidflowFilename($this->getId(), $storeId, $productsExtra);
						$header = array();
						$urapidflowHelper->generateProductFile($header, $removeCategoryData);
					}
				}
            }
        }
    }

    /***
     * Applies the actions to every product in the product collection
     * (simple products that are linked to configurable products are skipped)
     *
     * @param Mage_Catalog_Model_Product_Collection $productCollection
     * @param int $storeId
     */
    public function applyActionsToProductCollection($productCollection, $storeId)
    {
    	$attributes = $this->getData('attributes');
	    $dynamicAttributes =  $this->getData('attributes_dynamic');
		$categoryAddIds = $this->_checkExistingCategories($this->getData('category_add_id'));

	    $categoryRemoveIds = $this->getData('category_remove_id');
	    $changedAttributes =  $this->getData('changed_attributes'); // the attributes that are going to be updated by the current profile

        $newProductData = array();

        foreach ($productCollection as $product) {

            if (!in_array($product->getTypeId(), $this->getProductTypes())) {
                $this->_log(sprintf('Skipped updating product %s with sku %s: product type %s not in selected product types: %s',
                    $product->getName(),
                    $product->getSku(),
                    $product->getTypeId(),
                    implode(',', $this->getProductTypes())),
                Zend_Log::DEBUG);
                continue;
            }

            //update only the products that are not linked to a configurable product
            $parentIds = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($product->getId());


            if (!empty($parentIds)) {
                $this->_log(sprintf('Skipped updating product %s with sku %s: it\'s part of a configurable product with id %s',
                    $product->getName(),
                    $product->getSku(),
                    implode(',', $parentIds)));
                continue;
            }

            // load product to get access to all attributes
            $product = $product->setStoreId($storeId)->load($product->getId());

            $skipUpdate = array();

            //check if the current profile changed attributes are previously manually changed
            if (!$this->getData('overwrite_product_manual_changes')) {

                 $changedProductAttributes = Mage::getModel('kega_productattributedefault/productattributedefault')
                                                ->getManualProductAttributeChanges($product->getId(), $product->getStoreId());

                 foreach ($changedAttributes as $attributeCode) {
                       if (!empty($changedProductAttributes['changed_attributes'][$attributeCode])) {
                           $skipUpdate[] = $attributeCode;
                       }
                 }

                 //Zend_Debug::dump($skipUpdate, 'skip to update');
            }

            $this->_log('');
            $this->_log(sprintf('###### sku:%s "%s" ######',
                $product->getSku(),
                $product->getName()));

            try {

                $this->updateProductAttributes($attributes, $product, $skipUpdate);
                $this->updateProductAttributesDynamic($dynamicAttributes, $product, $skipUpdate);
                $this->addProductCategories($categoryAddIds, $product, $skipUpdate);
                $this->removeProductCategories($categoryRemoveIds, $product, $skipUpdate);

                $product->setData('updated_by_product_attribute_default_at', Mage::getModel('core/date')->date('Y-m-d h:i:s'));


                if ($this->getData('dry_run')
                             === Kega_ProductAttributeDefault_Model_Productattributedefault::IS_NOT_DRY_RUN) {

                    if (Mage::helper('kega_productattributedefault')->getUseURapidflow()) {
                        $changedProductData['sku'] = $product->getSku();
                        foreach ($changedAttributes as $attributeCode) {
                            $changedProductData[$attributeCode] = $product->getData($attributeCode);
                        }
                        $this->_removeCategoryLines[] = implode(',', $categoryRemoveIds);
                        $newProductData[] = $changedProductData;
                    } else {
                        $newProductData[] = $product->getData();
                        $product->save();
                    }
                } else {
                    $this->_log(sprintf('Product %s with sku %s not saved: dry run',
                        $product->getName(),
                        $product->getSku()));
                }

            } catch (Exception $e) {
                $this->_log(
                    sprintf('Error when updating product %s: %s ', $product->getSku(), $e->getMessage()),
                    Zend_Log::ERR);
            }
        }

        return $newProductData;
    }

    /**
     * Check for non-existing categories in the product attribute
     * profile. Save the profile afterwards with all the categories
     * that do exist.
     *
     * @param array $categoryIds
     * @return array $addCategories
     */
	protected function _checkExistingCategories($categoryIds)
	{
		$addCategories = array();

		foreach ($categoryIds as $categoryId) {
			$category = Mage::getModel('catalog/category')->load($categoryId);
			if ($category->getId()) {
				$addCategories[] = $categoryId;
			}
		}

		$productAttributeDefaultModel = $this;
		$productAttributeDefaultModel
			->setCategoryAddId($addCategories)
			->save();

		return $addCategories;
	}

    /**
     * Update product attributes - override
     * @param array $attributes
     * @param Mage_Catalog_Model_Product $product
     * @param array $skipUpdate - contains attributes that should be skipped from updating
     */
    public function updateProductAttributes($attributes, $product, $skipUpdate)
    {
    	// update product attributes - override
	    foreach ( $attributes as $attribute ) {
			if (empty ( $attribute ['attribute_code'] )) {
				continue;
			}

			if (in_array ( $attribute ['attribute_code'], $skipUpdate)) {
				$this->_log(sprintf('Skip attribute update: %s (changed manually)',
				    $attribute['attribute_code']));
				continue;
			}

			$product->setData($attribute['attribute_code'], $attribute['attribute_value']);

			$attributeValueLabel = Mage::helper('kega_productattributedefault')->getAttributeValueLabel(
			    $attribute['attribute_code'],
			    $attribute['attribute_value'],
			    $product->getStoreId()
			);

            $this->_log(sprintf('Set attribute: %s => "%s";',
                $attribute ['attribute_code'],
    			$attributeValueLabel));
	    }
    }

    /**
     * update product attributes - dynamic - they depend on other product attribute values
     * @param array $attributes
     * @param Mage_Catalog_Model_Product $product
     * @param array $skipUpdate - contains attributes that should be skipped from updating
     */
    public function updateProductAttributesDynamic($dynamicAttributes, $product, $skipUpdate)
    {
        foreach ( $dynamicAttributes as $dynamicAttribute ) {
			if (empty ( $dynamicAttribute ['attribute_code'] )) {
				continue;
			}

			if (in_array ( $dynamicAttribute ['attribute_code'], $skipUpdate )) {
				$this->_log(sprintf('Skip attribute update: %s (changed manually)',
				    $dynamicAttribute['attribute_code']));

				continue;
			}

			$pattern = $dynamicAttribute['attribute_value'];
			$newAttributeValue = Mage::helper('kega_productattributedefault')->getDynamicAttributeValue($product, $pattern);

			$product->setData($dynamicAttribute['attribute_code'], $newAttributeValue);

			$this->_log(sprintf('Set dynamic attribute: %s => "%s";',
    			$dynamicAttribute ['attribute_code'],
                $newAttributeValue));
		}
    }

    /**
     * set product categories - action add (does not override)
     * @param array $categoryAddIds
     * @param Mage_Catalog_Model_Product $product
     * @param array $skipUpdate - contains attributes that should be skipped from updating
     */
    public function addProductCategories($categoryAddIds, $product, $skipUpdate)
    {
    	if(empty($categoryAddIds)) {
    	    return;
    	}

		if(in_array('category_ids', $skipUpdate)) {
			$this->_log('Skip adding categories. (changed manualy)');
		} else {
			$currentCategoryIds = $product->getCategoryIds ();

			if(!empty($currentCategoryIds)) {
				$newCategoryIds = array_unique(array_merge($categoryAddIds, $currentCategoryIds));
			} else {
                $newCategoryIds = $categoryAddIds;
            }
			$newCategoryIds = implode(',', $newCategoryIds);

			$product->setCategoryIds($newCategoryIds);
			$this->_log(sprintf('Add to category ids: %s; ',
			    implode(', ',$categoryAddIds)));
		}
    }


    /**
     * set product categories - action remove
     * @param array $categoryRemoveIds
     * @param Mage_Catalog_Model_Product $product
     * @param array $skipUpdate - contains attributes that should be skipped from updating
     */
    public function removeProductCategories($categoryRemoveIds, $product, $skipUpdate)
    {
	    if(empty($categoryRemoveIds)) {
            return;
	    }

		if(in_array('category_ids', $skipUpdate)) {
			$this->_log('Skipped removing categories. (changed manually)');
		} else {
			$currentCategoryIds = $product->getCategoryIds ();
			$newCategoryIds = array();

			foreach($currentCategoryIds as $currentCategoryId) {
				if(!in_array($currentCategoryId, $categoryRemoveIds)) {
					$newCategoryIds[] = $currentCategoryId;
				}
			}

			$newCategoryIds = implode(',', $newCategoryIds);

			$product->setCategoryIds($newCategoryIds);
			$this->_log(sprintf('Remove category ids: %s;', implode(', ', $categoryRemoveIds)));
		}
    }

    protected function _log($message, $level = Zend_Log::INFO, $file = false)
    {
        if(!$file) {
            $file = $this->_logfile;
        }

        if($this->_testRun && $level != Zend_Log::DEBUG) {
            $this->_testRunOutput .= $message . PHP_EOL;
        }

        Mage::log($message, $level, $file);
    }
}
