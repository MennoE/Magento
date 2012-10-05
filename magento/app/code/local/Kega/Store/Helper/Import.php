<?php
class Kega_Store_Helper_Import extends Mage_Core_Helper_Abstract
{

    /**
     * Mapping between csv stores column names and magento store fields
     *
     * @return array key - csv, value - db store field
     *
     */
    public function getStoreCsvDbColumnMapping()
    {
        $csvDbMapping = array(
        	'store' => 'store',
        	'website' => 'website',
            'number' => 'storenummer',
        	'status' => 'is_active',
            'name' => 'name',
        	'country' => 'country',
            'city' => 'city',
            'address' => 'address',
            'zipcode' => 'zipcode',
            'phone' => 'phone',
        	'e-mail' => 'email',
            'fax' => 'fax',
            'mondayopen1' => 'mondayopen1',
            'mondayclose1' => 'mondayclose1',
            'tuesdayopen1' => 'tuesdayopen1',
            'tuesdayclose1' => 'tuesdayclose1',
            'wednesdayopen1' => 'wednesdayopen1',
            'wednesdayclose1' => 'wednesdayclose1',
            'thursdayopen1' => 'thursdayopen1',
            'thursdayclose1' => 'thursdayclose1',
            'fridayopen1' => 'fridayopen1',
            'fridayclose1' => 'fridayclose1',
            'saturdayopen1' => 'saturdayopen1',
            'saturdayclose1' => 'saturdayclose1',
            'sundayopen1' => 'sundayopen1',
            'sundayclose1' => 'sundayclose1',
        	'mondayopen2' => 'mondayopen2',
            'mondayclose2' => 'mondayclose2',
            'tuesdayopen2' => 'tuesdayopen2',
            'tuesdayclose2' => 'tuesdayclose2',
            'wednesdayopen2' => 'wednesdayopen2',
            'wednesdayclose2' => 'wednesdayclose2',
            'thursdayopen2' => 'thursdayopen2',
            'thursdayclose2' => 'thursdayclose2',
            'fridayopen2' => 'fridayopen2',
            'fridayclose2' => 'fridayclose2',
            'saturdayopen2' => 'saturdayopen2',
            'saturdayclose2' => 'saturdayclose2',
            'sundayopen2' => 'sundayopen2',
            'sundayclose2' => 'sundayclose2',
        );

        return $csvDbMapping;
    }


    /**
     * Mapping between csv extra opening column names and magento store fields
     *
     * @return array key - csv, value - db store field
     *
     */
    public function getExtraOpeningCsvDbColumnMapping()
    {
        $csvDbMapping = array(
        	'number' => 'storenummer',
        	'date from' => 'datetime_from',
            'date to' => 'datetime_to',
        	'description' => 'title',
            'status' => 'status',
        );

        return $csvDbMapping;
    }

    /**
     * Saves in the db the store records
     *
     * @throws Mage_Core_Exception if store code not found, website not found
     *
     * @param array $csvData - array with csv lines (the lines are also arrays)
     * @return void
     */
    public function importStores($csvData)
    {
		// deactivate all stores before updating, filters out old stores
		// we do not delete old stores, but dont want to display them
		$stores = Mage::getModel('store/store')->getCollection()
			->addAttributeToSelect('*');

		foreach($stores as $store) {
			$store->setIsActive(false);
			$store->save();
		}

        $csvMagentoMapping = $this->getStoreCsvDbColumnMapping();

		$counter = 0;
        $header = array();
		foreach ($csvData as $import_data) {

			//header line
			if ($counter == 0) {
                $header = array_flip($import_data);
				$counter++;
				continue;
			}

			// set csv line with key name as magento db name
			$csvLine = array();
			foreach ($header as $k => $val) {
				if (!isset($csvMagentoMapping[$k]) || !isset($import_data[$header[$k]])) continue;
				$csvLine[$csvMagentoMapping[$k]] = $import_data[$header[$k]];
			}

			if (empty($csvLine['name'])) {
				continue;
			}

			$storeId = 0;
			if ($csvLine['store']) {
				$mageStore = Mage::helper('store')->getMagentoStoreByCode($csvLine['store']);
				if (!$mageStore) {
					Mage::throwException($this->__('Invalid store code %s on line %s', $csvLine['store'], $counter+1));
				}
				$storeId = $mageStore->getId();
			}

			$storeModel = Mage::getModel('store/store');
			$storeModel->setStoreId($storeId);

			$storeData = $csvLine;
			$storeData = array_map('utf8_encode', $storeData);

			// update by default by storenummer
			if ($storeData['storenummer'] !== '') {
				$storeModel->loadStoreByStoreNumber($storeData['storenummer']);
                $storeModel->setStoreFilter($storeModel->getId());
			}

            $storeLng = $storeModel->getLng();
            $storeLat = $storeModel->getLat();
            if (empty($storeLng) || empty($storeLat)) {

                /**
                 * Prevent hammering google service.
                 * Otherwise we get blocked for a few seconds and only retrieve 0 values.
                 */
				sleep(2);

				$geodata = $this->getGeoLocation(
					$storeData['zipcode']
                    . ',' . $storeData['address']
					. ', ' . $storeData['country']
				);

				if ($geodata[0] == '200') {
					$storeData['lng'] = $geodata[2];
					$storeData['lat'] = $geodata[3];
				}
				else {
					$storeData['lng'] = 0;
					$storeData['lat'] = 0;
				}
			}

			$rawName = $storeData['name'] . ' ' . $storeData['address'];
			$strippedName = preg_replace('/[^a-z0-9- ]+/i', '', $rawName);
			$storeData['custom_url'] = strtolower(str_replace(' ', '-', $strippedName));

			//Zend_Debug::dump($csvLine);

			//get store id by websites
			if (empty($csvLine['website'])) {
				// all store views
				$storeData['storeview_ids'] = array(0);
			} else {
				$storeIds = Mage::helper('store')->getMagentoStoreIdsByWebsiteCode($csvLine['website']);
				if (!$storeIds) {
					Mage::throwException($this->__('Invalid website code %s on line %s
                                                   (check if the website code is defined and if it has store views)',
                                                   $csvLine['store'],
                                                   $counter+1));
				}
				$storeData['storeview_ids'] = array_values($storeIds);
			}

			//Zend_Debug::dump($storeModel->getData());


			$storeModel->addData($storeData);

			//Zend_Debug::dump($storeModel->getData());
			//die;

            // --------------------- OPENING DATA

			$opening = array(
				'mondayopen1' => '',
				'mondayclose1' => '',
				'tuesdayopen1' => '',
				'tuesdayclose1' => '',
				'wednesdayopen1' => '',
				'wednesdayclose1' => '',
				'thursdayopen1' => '',
				'thursdayclose1' => '',
				'fridayopen1' => '',
				'fridayclose1' => '',
				'saturdayopen1' => '',
				'saturdayclose1' => '',
				'sundayopen1' => '',
				'sundayclose1' => '',
				'mondayopen2' => '',
				'mondayclose2' => '',
				'tuesdayopen2' => '',
				'tuesdayclose2' => '',
				'wednesdayopen2' => '',
				'wednesdayclose2' => '',
				'thursdayopen2' => '',
				'thursdayclose2' => '',
				'fridayopen2' => '',
				'fridayclose2' => '',
				'saturdayopen2' => '',
				'saturdayclose2' => '',
				'sundayopen2' => '',
				'sundayclose2' => '',
			);

            foreach ($opening as $key => $value) {
                $opening[$key] = '';
                if (isset($csvLine[$key])) {
                     $opening[$key] = $csvLine[$key];
                }
            }

			$storeModel->setStoreOpeningData($opening);
			$storeModel->setStoreExtraopeningIds(array());
			$storeModel->save();
		}
    }

	/**
	 * Validate the extra opening file
	 *
	 * 1) File must be longer than a single line, otherwise only the header line is exported to us
	 * Block these files because otherwise we empty the DB without receiving new data
	 *
	 * 2) We do a check if the CSV does match our expected CSV column mapping
	 * We none of the columns map, we also park the file
	 *
	 */
	public function isValidExtraopeningFile($csvData)
	{
		if (count($csvData) === 1) {
			return false;
		}

		$counter = 0;
		$header = array();
		$csvMagentoMapping = $this->getExtraOpeningCsvDbColumnMapping();
		foreach ($csvData as $import_data) {
			if ($counter == 0) {
                $header = array_flip($import_data);
				$counter++;
				continue;
			}

			$matches = 0;

			// Search for matches
			$csvLine = array();
			foreach ($header as $k => $val) {
				if (!isset($csvMagentoMapping[$k])) continue;
				$csvLine[$csvMagentoMapping[$k]] = $import_data[$header[$k]];
				$matches++;
			}

			return $matches === count($csvMagentoMapping);
		}
	}

    /**
     * Saves in the db the extra opening records
	 * Purge our extraopening collection before importing the new batch.
	 *
	 * The batch is a full image of the data, not an update.
	 * Since we do not have any primary key relations we can only purge the data
	 * to prevent endless amounts of old records for each store.
     *
     * @throws Mage_Core_Exception if store number not found
     *
     * @param array $csvData - array with csv lines (the lines are also arrays)
     * @return void
     */
    public function importExtraOpenings($csvData)
    {
		if (!$this->isValidExtraopeningFile($csvData)) {
			Mage::throwException($this->__('Could not import file. No records found or records not matching expected mapping'));
		}

		$extraOpeningCollection = Mage::getModel('extraopening/extraopening')->getCollection();
		foreach($extraOpeningCollection as $extraOpening) {
			$extraOpening->delete();
		}

        $csvMagentoMapping = $this->getExtraOpeningCsvDbColumnMapping();

       	$counter = 0;
        $header = array();
		foreach ($csvData as $import_data) {
			//header line
			if ($counter == 0) {
                $header = array_flip($import_data);
				$counter++;
				continue;
			}

			// set csv line with key name as magento db name
			$csvLine = array();
			foreach ($header as $k => $val) {
				if (!isset($csvMagentoMapping[$k])) continue;
				$csvLine[$csvMagentoMapping[$k]] = $import_data[$header[$k]];
			}

            $storeModel = Mage::getModel('store/store')->loadStoreByStoreNumber($csvLine['storenummer']);

            if(!$storeModel->getId()) {
				if (!$storeModel) {
					printf("Extra opening data could not be matched to store, invalid storenummer delivered '%s'", $csvLine['storenummer']);
				}
				continue;
			}

            /**
             * @todo Anda.B 21.09.2011 - get the right format for the date in the csv file
             * the lines below are commented because we don't know the data format
             */
             $datetimeFrom = $csvLine['datetime_from']; //we suppose this is mysql format
             $datetimeTo = $csvLine['datetime_to']; //we suppose this is mysql format

			// convert to mysql format
			//$dateFormatIso = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
			//$_zendDate = new Zend_Date($csvLine['datetime_from'], $dateFormatIso);
			//$datetimeFrom =  $_zendDate->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);

			// convert to mysql format
			//$_zendDate = new Zend_Date($csvLine['datetime_to'], $dateFormatIso);
			//$datetimeTo =  $_zendDate->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);


			$model = Mage::getModel('extraopening/extraopening');
			$model->setTitle($csvLine['title'])
				->setDatetimeFrom($datetimeFrom)
				->setDatetimeTo($datetimeTo)
				->setStatus($csvLine['status'])
                ->setStoreIds(array($storeModel->getId()));
            $model->save();

            $counter++;
		}
    }

    public function getGeoLocation($criteria)
	{
		$url = sprintf(
			"http://maps.google.com/maps/geo?q=%s&output=csv&oe=utf8&sensor=true&key=%s",
			urlencode($criteria),
			'ABQIAAAAP8cM0a5GerqBsj_3zIk-NBTb_3-TxUy1LBXWwG4HHxwJVbRkYRTfvOiGUncAvg3Dtbxd_cKUoOMO_w'
		);
		$content = @file_get_contents($url);
		return explode(",", $content);
	}

}