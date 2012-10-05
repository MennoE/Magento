<?php
/**
 *
 * @category Kega
 * @package  Kega_ProductAttributeDefault
 */
?>
<?php
class Kega_ProductAttributeDefault_Helper_Data extends Mage_Core_Helper_Abstract
{

    /**
     * Get folder for logfiles
     */
	public function getLogDir()
	{
	    $logFolder = 'kega_productattributedefault/' . date('Ymd');

	    // create subfolder if not exists
		Mage::getConfig()->getVarDir('log/' . $logFolder);
		return $logFolder;
	}

	/**
	 * Get path to log file.
	 *
	 * /var/log/kega_productattributedefault/yyyymmdd/hhmm.log
	 * Logfile is automatically created before it's returned.
	 *
	 * @return String
	 */
    public function getLogfile()
	{
		$logFile = $this->getLogDir() . '/run-' . date('Hi') . '.log';

		Mage::log('Logfile created', null, $logFile);
		return $logFile;
	}

    public function getDefaultAttributeSet()
    {
        // this doesn't work: there are more than one attribute Set with name Default - don't know why
        /*$attrSetName = 'Default';
	$attributeSetId = Mage::getModel('eav/entity_attribute_set')
	    ->load($attrSetName, 'attribute_set_name')
	    ->getAttributeSetId();

        return  $attributeSetId;
        */

        return 4;
    }

    /**
     * Use urapidflow to save the products
     */
    public function getUseURapidflow()
    {
        return true;
    }

    /**
	 * Retrieves the magento configuration for extra attributes visibility
	 * @return array
	 */
	public function getExtraAttributesVisibilityConfig()
	{
		$config = @unserialize(Mage::getStoreConfig('kega_productattributedefault/general_settings/extra_attributes_visibility'));

        return $config;
	}

	/**
	 * Get all product attributes + product options for select attributes
	 * If $visibilityInProductEnricher is set then it returns only the attributes that have
	 * Kan worden ingesteld in Kega Product Verrijker in $visibilityInProductEnricher
	 * @param array $visibilityInProductEnricher
	 */
    public function getProductAttributesOptions($visibilityInProductEnricher = array())
    {
        $setId = $this->getDefaultAttributeSet();
        $attributes = Mage::getModel('catalog/product')->getResource()
                ->loadAllAttributes()
                ->getSortedAttributes();
        $skipAttributes = array('media_image', 'gallery',
                                'type', 'sku_type', 'media_gallery',
                                'required_options', 'has_options',
                                'image_label', 'thumbnail_label',
                                'old_id','created_by_urapidflow',
                                'enable_googlecheckout','updated_by_product_attribute_default_at',
        						'upsell_targetrule_position_behavior', 'upsell_targetrule_position_limit',
        						'custom_design_from','custom_layout_update','small_image','small_image_label',
                                );

        $result = array();

        $extraAttributesVisibilityConfig = $this->getExtraAttributesVisibilityConfig();

        //Zend_Debug::dump($extraAttributesVisibilityConfig);

        foreach ($attributes as $attribute) {
            /* @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
            if ( (!$attribute->getId() || $attribute->isInSet($setId))
                && !in_array($attribute->getName(), $skipAttributes)) {

                $skipAttribute = false;
                if (!$attribute->getId()) {
                    $skipAttribute = true;
                }

                $attributeType = '';
                switch ($attribute->getFrontendInput()) {
                    case 'multiselect':
                    case 'select':
                        $attributeType = 'select';
                    break;
                    default:
                        $attributeType = $attribute->getFrontendInput();
                    break;
                }

                if (!empty($visibilityInProductEnricher)) {
                    if (!$attribute->getData('is_visible')) {
                        if (empty($extraAttributesVisibilityConfig['option'])) {
                            $skipAttribute = true;
                        } else {
                            switch ($extraAttributesVisibilityConfig['option']) {
                                case 'show_all':
                                    // do nothing
                                break;
                                case 'show_none':
                                    $skipAttribute = true;
                                break;
                                case 'show_selected_type':
                                   if (!in_array($attributeType, $extraAttributesVisibilityConfig['details'])) {
                                        //Zend_Debug::dump($attribute->getData(), $attribute->getAttributeCode());
                                        $skipAttribute = true;
                                    }
                                break;
                            }
                        }
                    } elseif (!in_array($attribute->getData('product_enricher'), $visibilityInProductEnricher)) {
                        $skipAttribute = true;
                    }
                }


                if (!$skipAttribute) {
                    $result[$attribute->getAttributeCode()] = array(
                        'attribute_id' => $attribute->getId(),
                        'code'         => $attribute->getAttributeCode(),
                        'type'         => (in_array($attribute->getFrontendInput(), array('multiselect', 'select')))? 'select': 'text',
                        'input_type'    => $attribute->getFrontendInput(),
                        'name'     => $attribute->getName(),
                        'product_enricher' => $attribute->getData('product_enricher'),
                        'is_visible' => $attribute->getData('is_visible'),
                        'frontend_input' => $attribute->getFrontendInput(),
                    );

                    $options = array();
                    if ($attribute->usesSource()) {
                        $attrOptions = $attribute->getSource()->getAllOptions(false);

                        foreach ($attrOptions as $option) {
                            if($option['value'] === '' || is_array($option['value'])) continue;
                            $options[$option['value']] = $option['label'];
                        }
                    }

                    if ($options) {
                        $result[$attribute->getAttributeCode()]['options'] = $options;
                    }
                }
            }
        }

        ksort($result);

        return $result;

    }

	/**
	 * Returns the attribute value for text attributes or the attribute label for select attributes
	 *
	 * @param string $attribute_code
	 * @param string $attribute_value
	 * @return string
	 */
	public function getAttributeValueLabel($attribute_code, $attribute_value, $storeId = 0)
    {
        $_product = Mage::getModel('catalog/product')->setStoreId($storeId);

        $attribute = $_product->getResource()->getAttribute($attribute_code);
        $attribute->setStoreId($storeId);
        if ($attribute->usesSource()) {
            return $attribute->getSource()->getOptionText($attribute_value);
        } else {
            return $attribute_value;
        }

    }

    /**
     * Gets the attribute value based on a pattern (the pattern can contain other product attributes + fixed strings)
     *
     * @param Mage_Catalog_Model_Product $product
     * @param string $pattern Eg: {{prod_attr value='hakvorm'}}-{{prod_attr value='is_redeemable'}}
     * @return string
     */
	public function getDynamicAttributeValue ($product, $pattern)
	{
		$newAttributeValue = $pattern;


		// match product_attr part and the value
		// Result:
		// $matches = array(3) {
		//	  [0] => array(2) {
		//	    [0] => string(29) "{{prod_attr value='hakvorm'}}"
		//	    [1] => string(35) "{{prod_attr value='is_redeemable'}}"
		//	  }
		//	  [1] => array(2) {
		//	    [0] => string(9) "prod_attr"
		//      [1] => string(9) "prod_attr"
		//	  }
		//	  [2] => array(2) {
		//	    [0] => string(7) "hakvorm"
		//	    [1] => string(13) "is_redeemable"
		//	  }
		//	}

		$regex = "@{{(.*?) value='(.*?)'}}@";
		$matches = array();
		preg_match_all($regex, $newAttributeValue, $matches);

		// get attribute value for prod_attr sections
		$parsedData = array();
		foreach ($matches[0] as $key => $attrSection) {
			$attr_code = $matches[2][$key];
			$type = $matches[1][$key];
			$attr_value = '';

			if ($type != 'prod_attr') {
				Mage::throwException($this->__("Invalid tocken type found: %s", $type));
			}
			if(!$product->getResource()->getAttribute($attr_code)) continue;

			$attrValue = $this->getAttributeValueLabel($attr_code, $product->getData($attr_code), $product->getStoreId());

			$parsedData[$attrSection] = $attrValue;
		}

		// replace {{prod_attr}} sections with the attribute value
		foreach ($parsedData as $attrSection => $attrValue) {
			$newAttributeValue = str_replace($attrSection, $attrValue, $newAttributeValue);
		}

		return $newAttributeValue;
	}

	/**
     * Gets the attribute value based on a pattern (the pattern can contain other product attributes + fixed strings)
     *
     * @param Mage_Catalog_Model_Product $product
     * @param string $pattern Eg: {{prod_attr value='hakvorm'}}-{{prod_attr value='is_redeemable'}}
     * @return array
     */
	public function getDynamicAttributeSection($product, $pattern)
	{
		$regex = "@{{(.*?) value='(.*?)'}}@";
		$matches = array();
		preg_match_all($regex, $pattern, $matches);

		// get attribute for prod_attr sections
		$parsedData = array();
		foreach ($matches[0] as $key => $attrSection) {
			$attr_code = $matches[2][$key];
			$type = $matches[1][$key];
			$attr_value = '';

			if ($type != 'prod_attr') {
				Mage::throwException($this->__("Invalid tocken type found: %s", $type));
			}
			if(!$product->getResource()->getAttribute($attr_code)) continue;

			$attrValue = $this->getAttributeValueLabel($attr_code, $product->getData($attr_code));

			$parsedData[$attrSection] = $attr_code;
		}

		return $parsedData;
	}

    /**
     * Gets the fronted input for an attribute
     * @param string $attributeCode
     * @return mixed - attribute frontend type or false if attribute is not found
     */
    public function getAttributeType($attributeCode)
    {

        $attribute = Mage::getModel('catalog/product')->getResource()
                        ->getAttribute($attributeCode);
        if (!$attribute || !$attribute->getId()) return false;

        return $attribute->getFrontendInput();
    }

    /**
	* Move processed file to backup dir - every day a new backup dir,
	* a timestamp is added in front of the original filename
	*
	* the date&time is GMT
	*
	* @param String $sourceFilePath -
	* @param String $destinationDir
	* @return void
	*/
	public function backupFile($sourceFilePath, $destinationDir)
	{
        $fileName = basename($sourceFilePath);
        $backupFolderName = Mage::getModel('core/date')->gmtDate('d.m.Y');
        $backupDirName = $destinationDir . DS . $backupFolderName;

        if (!is_dir($backupDirName)) {
            mkdir($backupDirName, 0777);
        }

        $target = $backupDirName . DS. Mage::getModel('core/date')->gmtTimestamp().'-'.$fileName;
		rename($sourceFilePath, $target);
	}

}