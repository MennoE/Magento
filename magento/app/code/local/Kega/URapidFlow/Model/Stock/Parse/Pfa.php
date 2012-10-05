<?php
/**
 * This class is used to parse the raw stock file and build an array that is
 * later used by other classes to build the stock file required by the Urapidflow plugin.
 *
 */
class Kega_URapidFlow_Model_Stock_Parse_Pfa extends Kega_URapidFlow_Model_Parse_Abstract
{
	/**
	 * Placeholder for write connection.
	 * @var Varien_Db_Adapter_Pdo_Mysql
	 */
	protected $_writeConnection;

	/**
	 * Placeholder for name of stockorder stock table.
	 * @var string
	 */
	protected $_stockTable;

	/**
	 * Placeholder for barcode 2 sku (and id) mapping.
	 * @var unknown_type
	 */
	protected $_barcode2sku;

	/**
     * Placeholder for header columns of raw (CSV) file.
     * @var array
     */
    protected $_rawCsvHeaders = array('start_mark',
									  'barcode',
									  'store_code',
									  'qty',
									  'end_mark',
									  );

	/**
	 * When this class is constructed, we load a barcode 2 sku translation array.
	 * We need this mapping because uRapidFlow only allows updates on 'sku'.
	 */
	public function __construct()
	{
		parent::__construct();

		$this->_barcode2sku		= $this->getBarcode2Sku();
		$this->_writeConnection	= Mage::getSingleton("core/resource")->getConnection("core_write");
		$this->_stockTable		= Mage::getSingleton('core/resource')->getTableName('stock');
	}

    /**
     * Convert and add the raw stock data to $this->_data
     * If sku already in $this->_data, update the qty.
     *
     * Also write the converted stock data into the stockorders stock table during the conversion.
     * Is needed for the stockorders distribution script.
     */
    public function addData(array $rawData)
    {
    	if (!isset($this->_barcode2sku[$rawData['barcode']])) {
    		return;
    	}

		$this->insertStockordersStock($rawData['store_code'],
									  $this->_barcode2sku[$rawData['barcode']]['id'],
									  $rawData['qty']
									  );

		$sku = $this->_barcode2sku[$rawData['barcode']]['sku'];
		if (isset($this->_data[$sku])) {
			// If product already in array, update qty.
			$this->_data[$sku]['stock.qty'] += $rawData['qty'];
		} else {
			// New product.
			$this->_data[$sku] = array('sku' => $sku,
									   'stock.qty' => $rawData['qty'],
									   );
    	}
    }

    /**
     * Insert stockorder stock data.
     * If no data is set in $this->_data yet, we truncate the stockorders stock table.
     *
     * @param int $stockId
     * @param int $productId
     * @param float $qty
     */
    protected function insertStockordersStock($stockId, $productId, $qty)
    {
		// First product that we are processing, truncate stockorder stock table.
		if (empty($this->_data)) {
			$this->_writeConnection->query('TRUNCATE `' . $this->_stockTable . '`');
		}

		if ($qty <= 0) {
			return;
		}

		$query = 'INSERT INTO `' . $this->_stockTable . '` (`stock_id`, `product_id`, `qty`) values (?, ?, ?)';
		$this->_writeConnection->query($query, array($stockId, $productId, $qty));
    }

    /**
     * Load an array with barcode 2 sku mappings.
     * We do this by running a 'Product barcode2sku Export' uRapidFlow profile.
     * And loading this into memory.
     */
	public function getBarcode2Sku()
	{
		$barcode2sku = array();

		$profile = Mage::helper('urapidflow')
					->run('Product barcode2sku Export');

		if (!$profile->getId()) {
			Mage::throwException('Could\'t load profile: Product barcode2sku Export');
		}

		$filePath = $profile->getFileBaseDir() . DS . $profile->getFilename();
		$handle = fopen($filePath, 'r');

		$header = null;
		while (($data = fgetcsv($handle, 400000000, ",")) !== false) {
			if (!$header) {
				$header = $data;
				continue;
			}

			if (!empty($data[0])) {
				$barcode2sku[$data[0]] = array('sku' => $data[1], 'id' => $data[2]);
			}
		}

		fclose($handle);
		@unlink($filePath);

		return $barcode2sku;
	}
}