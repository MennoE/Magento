<?php
/**
 * This class is used to parse the raw price file and build an array that is
 * later used by other classes to build the product file required by the Urapidflow plugin.
 *
 */
class Kega_URapidFlow_Model_Price_Parse_Pfa extends Kega_URapidFlow_Model_Parse_Abstract
{
	/**
     * Placeholder for header columns of raw (CSV) file.
     * @var array
     */
    protected $_rawCsvHeaders = array('start_mark',
								      'barcode',
    								  'currency_code',
    								  'country_code',
    								  'price',
    								  'special_price',
    								  'end_mark'
									  );

	/**
     * Convert and add the raw price data to $this->_data
     * If sku already in $this->_data, update the qty.
     *
     * Also write the converted stock data into the stockorders stock table during the conversion.
     * Is needed for the stockorders distribution script.
     */
    public function addData(array $rawData)
    {
    	$this->_data[$rawData['barcode']] = array('barcode' => $rawData['barcode'],
												  'price' => $rawData['price'],
												  'special_price' => $rawData['special_price'],
												  );
    }
}