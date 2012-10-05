<?php
/**
 * This class is used to get product info from a csv file created with urapidflow product export profile
 * Each csv line is a product info
 *
 */
class Kega_URapidFlow_Model_Product_File
{
    /**
     * path to file that contains the products csv data
     * @var string
     */
    protected static $_filePath;

    /**
     * Product data, key is the sku
     * @var array
     */
    protected static $_productData = array();

    /**
     * List of all simple products (only filled after running ::buildSimpleProductListing)
     * @var array
     */
	protected static $_simpleProducts = array();

    /**
     * List of all products structured for the rebuild proces (only filled after running ::buildColorPriceRelations)
     * @var array
     */
	protected static $_getRebuildData = array();

    public static function getProductFilePath()
    {
        return $this->_filePath;
    }

    public static function setProductFilePath($filePath)
    {
        $this->_filePath = $filePath;
    }


    /**
     * Reads the export products csv data in an array
	 * Creates a relation between simple and configurable level
     *
     * @throws Exception - if file path is invalid
     *
     * @param string $filePath - location of export file
     *
     */
    public static function loadCsv($filePath)
    {
        $row = 1;
        $header  = array();
        $records = array();

        if (($handle = fopen($filePath, "r")) === FALSE) {
            Mage::throwException(Mage::helper('core')->__("Cannot open file %s containing export products", $filePath));
        }


        while (($data = fgetcsv($handle, null, ",")) !== FALSE) {
            if ($row == 1) {
                $header = $data;
            } else {
				$record = array_combine($header, $data);

                $configurableProductSku = self::getConfigurableProductSku($record);
				$records[$configurableProductSku]['product_info'] = $record;

				if (!isset($records[$configurableProductSku]['associated_products'])) {
					$records[$configurableProductSku]['associated_products'] = array();
				}

                // add associated simple product to configurable product data
                if ($record['product.type'] == Mage_Catalog_Model_Product_Type::TYPE_SIMPLE) {
                    // get configurable product sku
                    $records[$configurableProductSku]['associated_products'][$record['sku']] = $record;
                }
            }

            $row++;
        }

        fclose($handle);
        self::$_productData = $records;
    }

	/**
	 * Create an array of  all known simple products and their related configurable product
	 * With this data to be able to activate configurable products when a underlying simple is restocked
	 *
	 * @param string $filePath
	 * @return void
	 */
	public static function buildSimpleProductListing($filePath)
	{
        $row = 1;
        $header  = array();
        $records = array();

        if (($handle = fopen($filePath, "r")) === FALSE) {
            Mage::throwException(Mage::helper('core')->__("Cannot open file %s containing export products", $filePath));
        }

        while (($data = fgetcsv($handle, null, ",")) !== FALSE) {
            if ($row == 1) {
                $header = $data;
            } else {
				$record = array_combine($header, $data);

				if ($record['product.type'] !== Mage_Catalog_Model_Product_Type::TYPE_SIMPLE) {
					continue;
				}

                $configurableProductSku = self::getConfigurableProductSku($record);
				$records[$record['sku']]['configurable_sku'] = $configurableProductSku;
				$records[$record['sku']]['stock'] = (int)$record['stock.qty'];
           }

            $row++;
        }

        fclose($handle);
        self::$_simpleProducts = $records;
	}

	/**
	 * Fetch the configurable SKU to build configurable->simple relation from the export file data
	 *
	 * NOTE: if you want to retrieve a configurable SKU during important,
	 * use Kega_URapidFlow_Model_Product_Parse->getConfigurableProductSku()
     *
     * @param array $productData
     * @return string
     */
    private static function getConfigurableProductSku($productData)
    {
		if ($productData['product.type'] === 'simple') {
			return substr($productData['sku'], 0, 14);
		}
		else {
			return substr($productData['sku'], 0, 10);
		}
    }

	public static function getRebuildProductData()
	{
        return self::$_getRebuildData;
	}

	public static function getSimpleProductData()
	{
        return self::$_simpleProducts;
	}


	public static function getAllProductData()
    {
        return self::$_productData;
    }


    public static function getProductData($sku)
    {
        if (isset(self::$_productData[$sku])) return self::$_productData[$sku];

        return '';
    }

    /**
     * returns an array with all configurable simple products data- existent in db and from the row import file
     *
     * @param string $configurableProductSku
     * @param array $parsedProductData - @see Kega_URapidFlow_Model_Product_Parse::parseCsv()
     * @param $existentProductData - @see Kega_URapidFlow_Model_Product_File::loadCsv()
     *
     * @return array
     */
    public static function getAllConfigurableSimpleProducts($configurableProductSku, $parsedProductData, $existentProductData)
    {
        $simpleProducts = array();

        $simpleProductsSku = $parsedProductData[$configurableProductSku]['simple_sku'];

        $existentSimpleProducts = array();
        if (!empty($existentProductData['associated_products'])) {
            $existentSimpleProducts = $existentProductData['associated_products'];
        }

        foreach ($simpleProductsSku as $sku) {

            //we remove existent product data so we can use the new imported data
            if (isset($existentSimpleProducts[$sku])) {
                unset($existentSimpleProducts[$sku]);
            }
            $simpleProducts[$sku] = $parsedProductData[$sku]['data'];
        }

        if (!empty($existentSimpleProducts)) {
            // we add all products existent and imported ones in simple products
            $simpleProducts= $existentSimpleProducts + $simpleProducts;
        }

        return $simpleProducts;
    }

    /**
     * Get configurable products lowest price and special price
     * @param array $configurableSimpleProducts - the result of Kega_URapidFlow_Model_Product_File::getAllConfigurableSimpleProducts()
     *
     * return array
     */
    public static function getLowestPrice($configurableSimpleProducts)
    {
        if (!is_array($configurableSimpleProducts)) return false;

        $lowestPrice = 0;
        $lowestProductSku = '';
        foreach ($configurableSimpleProducts as $simpleProductSku => $configurableSimpleProduct) {
            if ($configurableSimpleProduct['price'] < $lowestPrice || $lowestPrice === 0) {
                $lowestProductSku = $simpleProductSku;
                $lowestPrice = $configurableSimpleProduct['price'];
            }
        }

        if (!isset($configurableSimpleProducts[$lowestProductSku])) return false;
        $prices = array(
            'simple_product_sku' => $lowestProductSku,
            'price' => $configurableSimpleProducts[$lowestProductSku]['price'],
            'special_price' => $configurableSimpleProducts[$lowestProductSku]['special_price'],
            'special_from_date' => $configurableSimpleProducts[$lowestProductSku]['special_from_date'],
            'special_to_date' => $configurableSimpleProducts[$lowestProductSku]['special_to_date'],
        );

        return $prices;
    }

    /**
     * Gets the simple products price differences
     *
     * @param string $configurableProductSku
     * @param array  $configurableSimpleProducts - the result from Kega_URapidFlow_Model_Product_File::getAllConfigurableSimpleProducts()
     * @param array $configurablePrices - the resul from Kega_URapidFlow_Model_Product_File::getLowestPrice()
     * @param string $websiteCode
     *
     * @return array
     */
    public static function getSimpleProductsPriceDifferences($configurableProductSku, $configurableSimpleProducts, $configurablePrices, $websiteCode = '')
    {
        if (!isset($configurablePrices['special_price'])) {
            Mage::throwException(Mage::helper('core')->__('Inexistent special_price attribute for product with sku %s'),
                                 $configurableProductSku);
        }

        if (!isset($configurablePrices['price'])) {
            Mage::throwException(Mage::helper('core')->__('Inexistent price attribute for product with sku %s'),
                                 $configurableProductSku);
        }


		$configurablePrice = self::calculateCurrentPrice($configurablePrices);
		$priceDifferences = array();

        if (!is_array($configurableSimpleProducts)) return array();

        foreach ($configurableSimpleProducts as $simpleProductSku => $configurableSimpleProduct) {
			$simplePrice = self::calculateCurrentPrice($configurableSimpleProduct);

			$priceDifferences[$simpleProductSku] = array(
                'configurable_sku' => $configurableProductSku,
                'pricing_value' => $simplePrice - $configurablePrice,
                'is_percent' => 0,
                'sku' => $simpleProductSku,
                'size' => $configurableSimpleProduct['size'], //this is the attribute value label used in configurable product associated products

				# The underneath lines are not needed for price difference
				# Though we want to update these product 'price_updated' flags back to 0
				# To re-use the regular import we need the products price data
				'price' => $configurableSimpleProduct['price'],
				'special_price' => $configurableSimpleProduct['special_price'],
				'special_from_date' => $configurableSimpleProduct['special_from_date'],
				'special_to_date' => $configurableSimpleProduct['special_to_date'],
            );

			if (!empty($websiteCode)) {
				$priceDifferences[$simpleProductSku]['website'] = $websiteCode;
			}
        }

        return $priceDifferences;
    }

	/**
	 * Determines which price should be active according to given price data
	 * We only want the special price as leading price when:
	 * - the special is greater than 0
	 * - the current date falls between special_from_date and special_to_date
	 *
	 * NOTE: we use core/date to get the current time since Magento also applies the
	 * date/time zone we choose in the configuration. Otherwise your timestamp will be off by 2 hours!
	 *
	 * @param array $priceData
	 * @return string
	 */
	public static function calculateCurrentPrice($priceData)
	{
		$now = Mage::getModel('core/date')->date('U');

		if ($now >= strtotime($priceData['special_from_date']) && $now < strtotime($priceData['special_to_date'])
				&& $priceData['special_price'] !== 0) {
			return $priceData['special_price'];
		}
		return $priceData['price'];
	}
}