<?php
class Kega_TntDirect_Model_Files extends Mage_Core_Model_Abstract
{
	/**
	 * Export all not yet exported kega_tnt_direct shipments.
	 *
	 * We run 2 export cycles, one for NL and one for EU shipments.
	 */
    public function export2PostNL()
	{
		if ($this->_mustRunExport()) {
			// Export NL
        	$this->_export2PostNL();

	        // Export EU
    	    $this->_export2PostNL(true);
		}
    }

    /**
     * Check if one of the shops has kega_tnt_direct activated as shipping method.
     *
     * @return boolean
     */
    private function _mustRunExport()
    {
    	foreach (Mage::app()->getStores() as $store) {
			if (Mage::getStoreConfig('carriers/kega_tnt_direct/active', $store)) {
				return true;
			}
		}

		return false;
    }

    /**
     * Create shipment export for PostNL.
     *
     * @param boolean $international (false: country_id = NL, true: country_id <> NL)
     */
    private function _export2PostNL($international = false)
	{
		if ($storeRows = self::_getShipmentRows($international)) {
			$exportDir = Mage::app()->getConfig()->getTempVarDir() . '/kega_tnt_direct/exports/';
			// If export dir does not exists, create it.
			if (!file_exists($exportDir)) {
				mkdir($exportDir, 0777, true);
			}
			foreach ($storeRows as $storeId => $rows) {
				if (!Mage::getStoreConfig('carriers/kega_tnt_direct/active', $storeId)) {
					continue;
				}
				echo 'Export ' . count($rows) . ' rows for store: ' . Mage::getModel('core/store')->load($storeId)->getName() . PHP_EOL;
				echo 'A030:' . Mage::getStoreConfig('carriers/kega_tnt_direct/a030', $storeId) . PHP_EOL;
				echo 'A100:' . Mage::getStoreConfig('carriers/kega_tnt_direct/a100', $storeId) . PHP_EOL;

				$message = 'Exporting ' . count($rows) . ($international ? ' international' : '')
						 . ' rows for store: ' . Mage::getModel('core/store')->load($storeId)->getName() . '.';
	            $export = Mage::getModel('kega_tntdirect/export')->create($message);

	            $lines = self::_convert(self::_getHeaderRow($export->getFileId(), $storeId));
	            foreach($rows as $row) {
	                $lines = self::_convert($row, $lines);
	            }
	            $lines = self::_convert(self::_getFooterRow($rows), $lines);

	            $localFile = $exportDir . $export->getFilename();
	            try {
		            self::_writeFile($localFile, $lines);
		            Mage::helper('kega_tntdirect')->uploadFile($localFile, $export->getFilename());
				}
				catch (Kega_TntDirect_Exception $e) {
					// Move file (if exists) to backup dir.
	            	Mage::helper('kega_tntdirect')->backupFile($localFile, false);

					// Log Exception in export log.
					$export->error('Exception: ' . $e);

					// And make sure we throw the Exception to the cron log.
					throw $e;
				}

	            $export->uploaded('Uploaded ' . count($lines) . ($international ? ' international' : '') . ' lines.');
	            echo 'Uploaded ' . count($lines) . ($international ? ' international' : '') . ' lines.' . PHP_EOL;

	            // Move file to backup dir.
	            Mage::helper('kega_tntdirect')->backupFile($localFile);

	            echo '--------------------------------------------------------------------------------' . PHP_EOL;
			}
        } else {
            echo 'No pending' . ($international ? ' international' : '') . ' shipments found.' . PHP_EOL;
        }
    }

    private static function _getHeaderRow($fileId, $storeId)
    {
        return array('A010' => date('Ymd'),
                     'A011' => date('His'),
                     'A020' => 130,
                     'A021' => 810,
                     'A022' => 130,
                     'A030' => Mage::getStoreConfig('carriers/kega_tnt_direct/a030', $storeId),
                     'A040' => Mage::getStoreConfig('carriers/kega_tnt_direct/a030', $storeId) . $fileId,
                     'A060' => date('Ymd'),
                     'A100' => Mage::getStoreConfig('carriers/kega_tnt_direct/a100', $storeId),
                     'A130' => Mage::getStoreConfig('carriers/kega_tnt_direct/a130', $storeId),
                     'A139' => Mage::getStoreConfig('carriers/kega_tnt_direct/a139', $storeId),
                     'A140' => Mage::getStoreConfig('carriers/kega_tnt_direct/a140', $storeId),
                     'A141' => Mage::getStoreConfig('carriers/kega_tnt_direct/a141', $storeId),
                     'A150' => Mage::getStoreConfig('carriers/kega_tnt_direct/a150', $storeId),
					 'A151' => Mage::getStoreConfig('carriers/kega_tnt_direct/a151', $storeId),
                     'A220' => Mage::getStoreConfig('carriers/kega_tnt_direct/a220', $storeId),
                     'A230' => Mage::getStoreConfig('carriers/kega_tnt_direct/a230', $storeId),
                     'A999' => ''
                    );
    }

    /**
     *
     * Get shipment row data for export to PostNL.
     *
     * @param boolean $international (false: country_id = NL, true: country_id <> NL)
     */
    private static function _getShipmentRows($international = false)
    {
        $shipments = Mage::getResourceModel('sales/order_shipment_collection')
            ->addAttributeToSelect('*')
            ->addFieldToFilter('exported_at', array('null' => true))
            ;

        // Join the shipment info with adress info, tracking info AND order shipment_method.
		$shipments->getSelect()
       		->join(array('address' => 'sales_flat_order_address'),
       			   "main_table.shipping_address_id = address.entity_id
       			    AND
       			    	`address_type` = 'shipping'
       			    AND
       			    	`country_id` IS NOT NULL
       			    AND
       					`country_id` " . ($international ? '<>' : '=') . " 'NL'"
       			   ,
       			   array('firstname',
       			   		 'lastname',
       			   		 'prefix',
       			    	 'middlename',
       			   		 'suffix',
       			   		 'region',
       			   		 'postcode',
       			    	 'street',
       			   		 'city',
       			    	 'email',
       			   		 'telephone',
       			   		 'fax',
       			   		 'country_id',
   		 				 'company')
       			   )
       		->join(array('orders' => 'sales_flat_order'),
       			   'main_table.order_id = orders.entity_id',
       			   array('shipping_method', 'order_increment_id' => 'increment_id')
       			   )
       		->where("(
       				  SELECT
       					  count(`entity_id`)
					  FROM
					  	  `sales_flat_shipment_track`
					  WHERE
						  `parent_id` = `main_table`.`entity_id`
					  AND
       					  `carrier_code` = 'kega_tnt_direct'
       				  AND
       				  	  `number` IS NOT NULL
					  AND
						  `number` != 'envelope'
       				  ) > 0"
       				);

        $shipmentRows = array();
        if (count($shipments)) {
            foreach($shipments as $shipment)
            {
                // get additional data to check if it's a store pickup order
                $payment = $shipment->getOrder()->getPayment();
				$additionalData = $payment->getAdditionalData()
					? unserialize($payment->getAdditionalData())
					: array()
				;

				// set store addres as delivery addres if it is a store pickup order.
				if (!empty($additionalData['store_pickup'])) {
					// store pickup
					$firstName = '';
					$lastName = '';
					$fullName = $additionalData['store_pickup']['name'];
					$street = $additionalData['store_pickup']['address'];
					$postcode = $additionalData['store_pickup']['postcode'];
					$city = '';
					$telephone = '';
					$countryId = 'NL';
				} else {
					// traditional shipping
                    $firstName = $shipment->getFirstname();
					$lastName = ($shipment->getMiddlename() ? $shipment->getMiddlename() . ' ' : '')
	                            . $shipment->getLastname()
	                            . ($shipment->getSuffix() ? ' ' . $shipment->getSuffix() : '');
	                if ($shipment->getCompany()) {
	                	$fullName = $shipment->getCompany();
	                } else {
	                	$fullName = $firstName . ' ' . $lastName;
	                }
                    $street  = $shipment->getStreet();
                	$postcode = $shipment->getPostcode();
                	$city = $shipment->getCity();
                	$telephone = $shipment->getTelephone();
                	$countryId = $shipment->getCountryId();
				}

                $number        = '';
                $numberSuffix  = '';

                // Split street into street, number, addition.
                $matches = array();
                preg_match('/([^0-9]+)([0-9]+)(.*)/', $street, $matches);
                if (!empty($matches[1])) {
                    $street = trim($matches[1]);
                }
                if (!empty($matches[2])) {
                    $number = trim($matches[2]);
                }
                if (!empty($matches[3])) {
                    $numberSuffix = trim($matches[3]);
                }

                // If number contains 12-20 use only the first part.
                if (strpos($number, '-') !== false) {
                    $parts = explode('-', $number);
                    list($number) = array_slice($parts, 0, 1);
                    // Add the rest to housenumber addition field.
                    $numberSuffix = trim(implode('-', array_slice($parts, 1)) . ' ' . $numberSuffix);
                }

                // Default PostNL product code.
                if ($international) {
                	$v040 = '04944';
                } else {
					// Retreive PostNL product code from the selected shipping method.
		            if (substr($shipment->getShippingMethod(), 0, 16) == 'kega_tnt_direct_') {
						$v040 = substr($shipment->getShippingMethod(), 16);
					} else {
						// If not available, we use the default PostNL product code.
	                	$v040 = '03085'; // AVG
					}
                }

				$tracks = Mage::getResourceModel('sales/order_shipment_track_collection')
							->setShipmentFilter($shipment->getId())
							->addFieldToFilter('carrier_code', array('eq' => 'kega_tnt_direct'));

                $parentTrack = null;
                $trackCount = 0;
				foreach ($tracks as $curentTrack) {
					if (!$parentTrack) {
						$parentTrack = $curentTrack;
					}
					$trackCount++;

					$row = array('V010' => 'V',
	                             'V020' => $curentTrack->getNumber(),
	                             'V021' => $parentTrack->getNumber(),
	                             'V025' => $shipment->getOrderIncrementId(),
	                             'V040' => $v040,
	                             'V051' => 15,
                                 'V056' => $shipment->getEmail(),
                                 'V057' => preg_match('#^06#', $telephone) ? $telephone : '',
                                 'V058' => !preg_match('#^06#', $telephone) ? $telephone : '',
	                             'V060' => $trackCount, // collo volgnummer
	                             'V061' => count($tracks), // aantal collo
	                             // 'V070' => '',
	                             // 'V090' => '',
	                             // 'V110' => '',
	                             // 'V120' => '',
	                             // 'V121' => '',
	                             // 'V130' => '',
	                             // 'V150' => '',
	                             'V170' => $fullName,
				                 'V172' => $lastName,
				                 'V173' => $firstName,
	                             'V180' => $number,
	                             'V181' => $numberSuffix,
	                             'V190' => str_replace(' ', '', $postcode),
	                             'V191' => $city,
	                             'V200' => $countryId,
	                             //'V440' => '',
	                             //'V450' => '',
	                             'V999' => ''
	                            );

					$shipmentRows[$shipment->getStoreId()][] = $row;
				}

				// Shipment has been added to the export.
				// Add a comment and set exported_at date/time and email the customer.
				$shipment->addComment(Mage::helper('kega_tntdirect')->__('Exported shipment to PostNL'));
				$shipment->setExportedAt(date('Y-m-d H:i:s'))
						 ->save();
            }

            return $shipmentRows;
        }
    }

    private static function _getFooterRow($rows)
    {
        $totalAmount = 0;
        foreach($rows as $row) {
            if (isset($row['V070'])) {
                $totalAmount += $row['V070'];
            }
        }

        return array('Z001' => count($rows),
                     'Z002' => $totalAmount,
                     'Z999' => ''
                      );
    }

    private static function _convert($row, $lines = array())
    {
        foreach($row as $key=>$value) {
            $lines[] = trim($key . ' ' . $value);
        }

        return $lines;
    }

	private static function _writeFile($filename, $lines = null, $append = false)
    {
		$fh = @fopen($filename, ($append ? 'a' : 'w'));
		if (!$fh) {
			throw new Kega_TntDirect_Exception("Could not create file: '{$filename}'");
		}

		// Write rows.
		if ($lines !== null) {
			foreach ($lines as $line) {
				fwrite($fh, $line . "\n");
			}
		}
		fclose($fh);

		return $filename;
	}
}
