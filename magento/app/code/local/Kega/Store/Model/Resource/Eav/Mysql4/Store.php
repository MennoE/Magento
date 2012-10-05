<?php
class Kega_Store_Model_Resource_Eav_Mysql4_Store extends Mage_Eav_Model_Entity_Abstract
{
	    public function __construct()
    {
    	parent::__construct();
        Mage::getSingleton('eav/config')->preloadAttributes('store', $this->_getDefaultAttributes());

        $resource = Mage::getSingleton('core/resource');
        $this->setType('store');
        $this->setConnection(
            $resource->getConnection('store_read'),
            $resource->getConnection('store_write')
        );
    }




    protected function _getDefaultAttributeModel()
    {
        return 'store/resource_eav_attribute';
    }

    protected function _getDefaultAttributes()
    {
        return array('entity_id', 'entity_type_id', 'attribute_set_id', 'type_id', 'created_at', 'updated_at');
    }

    public function getDefaultStoreId()
    {
        return Kega_Store_Model_Store::DEFAULT_STORE_ID;
    }

   	public function getExtraopenings($object){

		$read = $this->_getReadAdapter();

		$select = $read->select();

		if (($storeId = $object->getEntityId())) {

			$select->from(array('extraopening_store' => $this->getTable('extraopening/extraopening_store')), array('extraopening_store.extraopening_id','extraopening.datetime_to','extraopening.datetime_from', 'extraopening.status', 'extraopening.title'))
			->where('extraopening_store.store_id = ?', $storeId)
			->order('extraopening.datetime_from');

			$select->join(
				array('extraopening' => $this->getTable('extraopening/extraopening')),
				$read->quoteInto('extraopening.extraopening_id=extraopening_store.extraopening_id', null),
				array()
			);
			return $read->fetchAll($select);
		}

	}


	public function getExtraopeningIds($object){

		$read = $this->_getReadAdapter();

		$select = $read->select();

		if (($storeId = $object->getStoreFilter())) {

			$select->from(array('extraopening_store' => $this->getTable('extraopening/extraopening_store')), array('extraopening_store.extraopening_id'))
			->where('extraopening_store.store_id = ?', $storeId);

			return $read->fetchAssoc($select);
		}
	}




        /**
     * Retrieve select object for loading entity attributes values
     *
     * Join attribute store value
     *
     * @param   Varien_Object $object
     * @param   mixed $rowId
     * @return  Zend_Db_Select
     */
    protected function _getLoadAttributesSelect($object, $table)
    {
        $joinCondition = 'main.attribute_id=default.attribute_id AND '
            . $this->_read->quoteInto('main.store_id=? AND ', $object->getStoreId())
            . $this->_read->quoteInto('main.'.$this->getEntityIdField() . '=?', $object->getId());

        $select = $this->_read->select()
            ->from(array('default' => $table))
            ->joinLeft(array('main' => $table), $joinCondition, array(
                'store_value_id'=>'value_id',
                'store_value'=>'value'
            ))
            ->where('default.'.$this->getEntityIdField() . '=?', $object->getId())
            ->where('default.store_id=?', $this->getDefaultStoreId());

        return $select;
    }



    protected function _insertAttribute($object, $attribute, $value)
    {
        $entityIdField = $attribute->getBackend()->getEntityIdField();
        $row = array(
            $entityIdField  => $object->getId(),
            'entity_type_id'=> $object->getEntityTypeId(),
            'attribute_id'  => $attribute->getId(),
            'value'         => $this->_prepareValueForSave($value, $attribute),
            'store_id'      => $this->getDefaultStoreId()
        );
        $this->_getWriteAdapter()->insert($attribute->getBackend()->getTable(), $row);
        if ($object->getStoreId() != $this->getDefaultStoreId()) {
            $this->_updateAttribute($object, $attribute, $this->_getWriteAdapter()->lastInsertId(), $value);
        }
        return $this;
    }

     /**
     * Update entity attribute value
     *
     * @param   Varien_Object $object
     * @param   Mage_Eav_Model_Entity_Attribute_Abstract $attribute
     * @param   mixed $valueId
     * @param   mixed $value
     * @return  Mage_Eav_Model_Entity_Abstract
     */
    protected function _updateAttribute($object, $attribute, $valueId, $value)
    {
        /**
         * Update attribute value for store
         */
        if ($attribute->isScopeStore()) {
            $this->_updateAttributeForStore($object, $attribute, $value, $object->getStoreId());
        }

        /**
         * Update attribute value for website
         */
        //!!!
        elseif ($attribute->isScopeWebsite()) {
            if ($object->getStoreId() == 0) {
                $this->_updateAttributeForStore($object, $attribute, $value, $object->getStoreId());
            } else {
                if (is_array($object->getWebsiteStoreIds())) {
                    foreach ($object->getWebsiteStoreIds() as $storeId) {
                        $this->_updateAttributeForStore($object, $attribute, $value, $storeId);
                    }
                }
            }
        }
        else {
            $this->_getWriteAdapter()->update($attribute->getBackend()->getTable(),
                array('value' => $this->_prepareValueForSave($value, $attribute)),
                'value_id='.(int)$valueId
            );
        }
        return $this;
    }

    protected function _updateAttributeForStore($object, $attribute, $value, $storeId)
    {
        $entityIdField = $attribute->getBackend()->getEntityIdField();
        $select = $this->_getWriteAdapter()->select()
            ->from($attribute->getBackend()->getTable(), 'value_id')
            ->where('entity_type_id=?', $object->getEntityTypeId())
            ->where("$entityIdField=?",$object->getId())
            ->where('store_id=?', $storeId)
            ->where('attribute_id=?', $attribute->getId());
        /**
         * When value for store exist
         */
        if ($valueId = $this->_getWriteAdapter()->fetchOne($select)) {
            $this->_getWriteAdapter()->update($attribute->getBackend()->getTable(),
                array('value' => $this->_prepareValueForSave($value, $attribute)),
                'value_id='.$valueId
            );
        }
        else {
            $this->_getWriteAdapter()->insert($attribute->getBackend()->getTable(), array(
                $entityIdField  => $object->getId(),
                'entity_type_id'=> $object->getEntityTypeId(),
                'attribute_id'  => $attribute->getId(),
                'value'         => $this->_prepareValueForSave($value, $attribute),
                'store_id'      => $storeId
            ));
        }

        return $this;
    }

     /**
     * Initialize attribute value for object
     *
     * @param   Varien_Object $object
     * @param   array $valueRow
     * @return  Mage_Eav_Model_Entity_Abstract
     */
    protected function _setAttribteValue($object, $valueRow)
    {
        parent::_setAttribteValue($object, $valueRow);
        if ($attribute = $this->getAttribute($valueRow['attribute_id'])) {
            $attributeCode = $attribute->getAttributeCode();
            if (isset($valueRow['store_value'])) {
                $object->setAttributeDefaultValue($attributeCode, $valueRow['value']);
                $object->setData($attributeCode, $valueRow['store_value']);
                $attribute->getBackend()->setValueId($valueRow['store_value_id']);
            }
        }
        return $this;
    }

        /**
     * Delete entity attribute values
     *
     * @param   Varien_Object $object
     * @param   string $table
     * @param   array $info
     * @return  Varien_Object
     */
    protected function _deleteAttributes($object, $table, $info)
    {
        $entityIdField      = $this->getEntityIdField();
        $globalValues       = array();
        $websiteAttributes  = array();
        $storeAttributes    = array();

        /**
         * Separate attributes by scope
         */
        foreach ($info as $itemData) {
            $attribute = $this->getAttribute($itemData['attribute_id']);
            if ($attribute->isScopeStore()) {
                $storeAttributes[] = $itemData['attribute_id'];
            }
            elseif ($attribute->isScopeWebsite()) {
                $websiteAttributes = $itemData['attribute_id'];
            }
            else {
                $globalValues[] = $itemData['value_id'];
            }
        }

        /**
         * Delete global scope attributes
         */
        if (!empty($globalValues)) {
            $condition = $this->_getWriteAdapter()->quoteInto('value_id IN (?)', $globalValues);
            $this->_getWriteAdapter()->delete($table, $condition);
        }

        $condition = $this->_getWriteAdapter()->quoteInto("$entityIdField=?", $object->getId())
            . $this->_getWriteAdapter()->quoteInto(' AND entity_type_id=?', $object->getEntityTypeId());
        /**
         * Delete website scope attributes
         */
        if (!empty($websiteAttributes)) {
            $storeIds = $object->getWebsiteStoreIds();
            if (!empty($storeIds)) {
                $delCondition = $condition
                    . $this->_getWriteAdapter()->quoteInto(' AND attribute_id IN(?)', $websiteAttributes)
                    . $this->_getWriteAdapter()->quoteInto(' AND store_id IN(?)', $storeIds);
                $this->_getWriteAdapter()->delete($table, $delCondition);
            }
        }

        /**
         * Delete store scope attributes
         */
        if (!empty($storeAttributes)) {
            $delCondition = $condition
                . $this->_getWriteAdapter()->quoteInto(' AND attribute_id IN(?)', $storeAttributes)
                . $this->_getWriteAdapter()->quoteInto(' AND store_id =?', $object->getStoreId());
            $this->_getWriteAdapter()->delete($table, $delCondition);;
        }
        return $this;
    }

    protected function _getOrigObject($object)//!!!
    {
        $className  = get_class($object);
        $origObject = new $className();
        $origObject->setData(array());
        $origObject->setStoreId($object->getStoreId());
        $this->load($origObject, $object->getData($this->getEntityIdField()));
        return $origObject;
    }


    /**
     * Retrieve select object for loading base entity row
     *
     * @param   Varien_Object $object
     * @param   mixed $rowId
     * @return  Zend_Db_Select
     */
    protected function _getLoadRowSelect($object, $rowId)//!!!
    {
        $select = parent::_getLoadRowSelect($object, $rowId);
        if ($object->getWebsiteId() && $object->getSharingConfig()->isWebsiteScope()) {
            $select->where('website_id=?', (int) $object->getWebsiteId());
        }
        return $select;
    }





    protected function _collectOrigData($object)//!!!
    {
        $this->loadAllAttributes($object);

        if ($this->getUseDataSharing()) {
            $storeId = $object->getStoreId();
        } else {
            $storeId = $this->getStoreId();
        }

        $allStores = Mage::getConfig()->getStoresConfigByPath('system/store/id', array(), 'code');
        $data = array();

        foreach ($this->getAttributesByTable() as $table=>$attributes) {
            $entityIdField = current($attributes)->getBackend()->getEntityIdField();

            $select = $this->_read->select()
                ->from($table)
                ->where($this->getEntityIdField()."=?", $object->getId());

            $where = $this->_read->quoteInto("store_id=?", $storeId);

            $globalAttributeIds = array();
            foreach ($attributes as $attrCode=>$attr) {
                if ($attr->getIsGlobal()) {
                    $globalAttributeIds[] = $attr->getId();
                }
            }
            if (!empty($globalAttributeIds)) {
                $where .= ' or '.$this->_read->quoteInto('attribute_id in (?)', $globalAttributeIds);
            }
            $select->where($where);

            $values = $this->_read->fetchAll($select);

            if (empty($values)) {
                continue;
            }
            foreach ($values as $row) {
                $data[$this->getAttribute($row['attribute_id'])->getName()][$row['store_id']] = $row;
            }
            foreach ($attributes as $attrCode=>$attr) {

            }
        }

        return $data;
    }


    public function getStoresByGoogleCode($object, $latitude, $longitude, $radius, $stores_number){

    	return $object->getCollection()
			->addAttributeToSelect('name')
			->addAttributeToSelect('name_internal')
			->addAttributeToSelect('address')
			->addAttributeToSelect('zipcode')
			->addAttributeToSelect('city')
			->addAttributeToSelect('lat')
			->addAttributeToSelect('lng')
			->addAttributeToSelect('phone')
			->addAttributeToSelect('email')
			->addAttributeToSelect('manager_name')
			->addAttributeToSelect('store_image')
			->addAttributeToSelect('manager_image')
			->addAttributeToSelect('custom_url')
			->addExpressionAttributeToSelect(
				'distance',
				'( 3959 * acos( cos( radians('.$latitude.') ) * cos( radians( {{lat}} ) ) * cos( radians( {{lng}} ) - radians('.$longitude.') ) + sin( radians('.$latitude.') ) * sin( radians( {{lat}} ) ) ) )',
				array('lat','lng','lat'))
			->addFieldToFilter('is_active', '1')
			->addFieldToFilter('distance', array('lt'=> $radius))
			->addAttributeToSort('distance', 'asc')
			->setPageSize($stores_number);
			//!!!->limit
			;


//		$readresult=$write->query("SELECT  store.*,
//		( 3959 * acos( cos( radians(".$latitude.") ) * cos( radians( store.lat ) ) * cos( radians( store.lng ) - radians(".$longitude.") ) + sin( radians(".$latitude.") ) * sin( radians( store.lat ) ) ) ) as distance
//			FROM    store
//			WHERE active='1'
//			HAVING distance < ".$radius."
//			ORDER BY distance ASC
//			LIMIT ".$stores_number
//		);
    }



    public function loadOpeningData(Kega_Store_Model_Store $object)
    {
        $storeId   = $object->getId();
        $storeIds = array();
        if ($storeId) {
            $select = $this->_getReadAdapter()->select()
                ->from($this->getTable('store/store_opening'),
                		'mondayopen1','mondayclose1','mondayopen2','mondayclose2',
                		'tuesdayopen1','tuesdayclose1','tuesdayopen2','tuesdayclose2',
                		'wednesdayopen1','wednesdayclose1','wednesdayopen2','wednesdayclose2',
                		'thursdayopen1','thursdayclose1','thursdayopen2','thursdayclose2',
                		'fridayopen1','fridayclose1','fridayopen2','fridayclose2',
                		'saturdayopen1','saturdayclose1','saturdayopen2','saturdayclose2',
                		'sundayopen1','sundayclose1','sundayopen2','sundayclose2')
                ->where('store_id = ?', $storeId);
            $openingData = $this->_getReadAdapter()->fetchOne($select);//!!!fetchCol($select);
        }
        $object->setOpeningData($openingData);
    }


	public function _afterSave(Varien_Object $object)
	{

        // ------------- save store routes
        $this->saveStoreRoutes($object);

        /** extraopenings */
		$deleteWhere = $this->_getWriteAdapter()->quoteInto('store_id = ?', $object->getId());

		$this->_getWriteAdapter()->delete($this->getTable('extraopening/extraopening_store'), $deleteWhere);

        if (count($object->getStoreExtraopeningIds())) {
    		foreach ($object->getStoreExtraopeningIds() as $extraopeningId)
    		{
    
    			$extraopeningStoreData = array(
    				'extraopening_id'	=> $extraopeningId,
    				'store_id'			=> $object->getId()
    			);
    
    			$this->_getWriteAdapter()->insert($this->getTable('extraopening/extraopening_store'), $extraopeningStoreData);
    		}
		}

		/** opening datatime */

		$storeOpeningData = $object->getStoreOpeningData();

        $select = $this->_getWriteAdapter()->select()
            ->from($this->getTable('store/store_opening'), 'store_opening_id')
            ->where('store_id=?', $object->getId());

        /**
         * When opening datetime for store exist
         */
        if ($storeOpeningId = $this->_getWriteAdapter()->fetchOne($select)) {

            $this->_getWriteAdapter()->update(
            	$this->getTable('store/store_opening'),
                array(
                	'mondayopen1'		=> $storeOpeningData['mondayopen1'],
					'mondayclose1'		=> $storeOpeningData['mondayclose1'],
					'mondayopen2'		=> $storeOpeningData['mondayopen2'],
					'mondayclose2'		=> $storeOpeningData['mondayclose2'],
					'tuesdayopen1'		=> $storeOpeningData['tuesdayopen1'],
					'tuesdayclose1'		=> $storeOpeningData['tuesdayclose1'],
					'tuesdayopen2'		=> $storeOpeningData['tuesdayopen2'],
					'tuesdayclose2'		=> $storeOpeningData['tuesdayclose2'],
					'wednesdayopen1'	=> $storeOpeningData['wednesdayopen1'],
					'wednesdayclose1'	=> $storeOpeningData['wednesdayclose1'],
					'wednesdayopen2'	=> $storeOpeningData['wednesdayopen2'],
					'wednesdayclose2'	=> $storeOpeningData['wednesdayclose2'],
					'thursdayopen1'		=> $storeOpeningData['thursdayopen1'],
					'thursdayclose1'	=> $storeOpeningData['thursdayclose1'],
					'thursdayopen2'		=> $storeOpeningData['thursdayopen2'],
					'thursdayclose2'	=> $storeOpeningData['thursdayclose2'],
					'fridayopen1'		=> $storeOpeningData['fridayopen1'],
					'fridayclose1'		=> $storeOpeningData['fridayclose1'],
					'fridayopen2'		=> $storeOpeningData['fridayopen2'],
					'fridayclose2'		=> $storeOpeningData['fridayclose2'],
					'saturdayopen1'		=> $storeOpeningData['saturdayopen1'],
					'saturdayclose1'	=> $storeOpeningData['saturdayclose1'],
					'saturdayopen2'		=> $storeOpeningData['saturdayopen2'],
					'saturdayclose2'	=> $storeOpeningData['saturdayclose2'],
					'sundayopen1'		=> $storeOpeningData['sundayopen1'],
					'sundayclose1'		=> $storeOpeningData['sundayclose1'],
					'sundayopen2'		=> $storeOpeningData['sundayopen2'],
					'sundayclose2'		=> $storeOpeningData['sundayclose2']
                ),
				'store_opening_id='.$storeOpeningId
            );
        }
        else {

			$this->_getWriteAdapter()->insert(
				$this->getTable('store/store_opening'),
				array(
					'store_id'			=> $object->getId(),
					'mondayopen1'		=> $storeOpeningData['mondayopen1'],
					'mondayclose1'		=> $storeOpeningData['mondayclose1'],
					'mondayopen2'		=> $storeOpeningData['mondayopen2'],
					'mondayclose2'		=> $storeOpeningData['mondayclose2'],
					'tuesdayopen1'		=> $storeOpeningData['tuesdayopen1'],
					'tuesdayclose1'		=> $storeOpeningData['tuesdayclose1'],
					'tuesdayopen2'		=> $storeOpeningData['tuesdayopen2'],
					'tuesdayclose2'		=> $storeOpeningData['tuesdayclose2'],
					'wednesdayopen1'	=> $storeOpeningData['wednesdayopen1'],
					'wednesdayclose1'	=> $storeOpeningData['wednesdayclose1'],
					'wednesdayopen2'	=> $storeOpeningData['wednesdayopen2'],
					'wednesdayclose2'	=> $storeOpeningData['wednesdayclose2'],
					'thursdayopen1'		=> $storeOpeningData['thursdayopen1'],
					'thursdayclose1'	=> $storeOpeningData['thursdayclose1'],
					'thursdayopen2'		=> $storeOpeningData['thursdayopen2'],
					'thursdayclose2'	=> $storeOpeningData['thursdayclose2'],
					'fridayopen1'		=> $storeOpeningData['fridayopen1'],
					'fridayclose1'		=> $storeOpeningData['fridayclose1'],
					'fridayopen2'		=> $storeOpeningData['fridayopen2'],
					'fridayclose2'		=> $storeOpeningData['fridayclose2'],
					'saturdayopen1'		=> $storeOpeningData['saturdayopen1'],
					'saturdayclose1'	=> $storeOpeningData['saturdayclose1'],
					'saturdayopen2'		=> $storeOpeningData['saturdayopen2'],
					'saturdayclose2'	=> $storeOpeningData['saturdayclose2'],
					'sundayopen1'		=> $storeOpeningData['sundayopen1'],
					'sundayclose1'		=> $storeOpeningData['sundayclose1'],
					'sundayopen2'		=> $storeOpeningData['sundayopen2'],
					'sundayclose2'		=> $storeOpeningData['sundayclose2']
				)
			);
        }

        //save store views
        $deleteWhere = $this->_getWriteAdapter()->quoteInto('store_id = ?', $object->getId());

        $this->_getWriteAdapter()->delete($this->getTable('store/storeview'), $deleteWhere);

        $storeviewIds = is_array($object->getStoreviewIds()) ? $object->getStoreviewIds() : array((int)$object->getStoreviewIds());

        foreach ($storeviewIds as $storeviewId)
        {
            $storeStoreviewData = array(
	            'store_id'   => $object->getId(),
	            'storeview_id'  => $storeviewId
            );

            $this->_getWriteAdapter()->insert($this->getTable('store/storeview'), $storeStoreviewData);
        }


		//parent::_afterSave($object);
		return $this;
	}


    /**
     * Save store routes in the store routes table
     * @param Kega_Store_Model_Store
     */
    private function saveStoreRoutes($object)
    {

		$storeRoutes  = $object->getStoreRoutesData();

        if (empty($storeRoutes)) return;

        $select = $this->_getWriteAdapter()->select()
            ->from($this->getTable('store_routes'))
            ->where('store_id=?', $object->getId());

        $record =  $this->_getWriteAdapter()->fetchRow($select);

        if ($record) {
            $this->_getWriteAdapter()->update(
            	$this->getTable('store_routes'),
                array (
                    'mondayroute' => $storeRoutes['mondayroute'],
                    'tuesdayroute' => $storeRoutes['tuesdayroute'],
                    'wednesdayroute' =>  $storeRoutes['wednesdayroute'],
                    'thursdayroute' =>  $storeRoutes['thursdayroute'],
                    'fridayroute' =>  $storeRoutes['fridayroute'],
                    'saturdayroute' =>  $storeRoutes['saturdayroute'],
                    'sundayroute' =>  $storeRoutes['sundayroute'],
                ),
                $this->_getWriteAdapter()->quoteInto('store_route_id = ?', $record['store_route_id'])
                );
        } else {
            $this->_getWriteAdapter()->insert(
            	$this->getTable('store_routes'),
                array (
                    'store_id' => $object->getId(),
                    'mondayroute' => $storeRoutes['mondayroute'],
                    'tuesdayroute' => $storeRoutes['tuesdayroute'],
                    'wednesdayroute' =>  $storeRoutes['wednesdayroute'],
                    'thursdayroute' =>  $storeRoutes['thursdayroute'],
                    'fridayroute' =>  $storeRoutes['fridayroute'],
                    'saturdayroute' =>  $storeRoutes['saturdayroute'],
                    'sundayroute' =>  $storeRoutes['sundayroute'],
                )
                );
        }
    }

    /**
     * adds the store route data to the store object
     * @param Kega_Store_Model_Store
     */
    public function loadStoreRouteData($object)
    {
        $select = $this->_getWriteAdapter()->select()
            ->from($this->getTable('store_routes'))
            ->where('store_id=?', $object->getId());

        $record =  $this->_getWriteAdapter()->fetchRow($select);

        if (!$record) return;

        $object->setStoreRoutesData($record);
    }



    public function loadStoreviewIds(Kega_Store_Model_Store $object)
    {
        $storeId   = $object->getId();
        $storeviewIds = array();
        if ($storeId) {
            $select = $this->_getReadAdapter()->select()
                ->from($this->getTable('store/storeview'), 'storeview_id')
                ->where('store_id = ?', $storeId);
            $storeviewIds = $this->_getReadAdapter()->fetchCol($select);
        }
        $object->setStoreviewIds($storeviewIds);
    }

    /**
     *
     * @param Varien_Object $object
     */
    protected function _afterLoad(Varien_Object $object)
    {
        // set routes data
        $this->loadStoreRouteData($object);

        $select = $this->_getReadAdapter()->select()
            ->from($this->getTable('store/storeview'))
            ->where('store_id = ?', $object->getId());

        if ($data = $this->_getReadAdapter()->fetchAll($select)) {
            $storeviewArray = array();
            foreach ($data as $row) {
                $storeviewArray[] = $row['storeview_id'];
            }
            $object->setData('storeview_ids', $storeviewArray);
        }
        return parent::_afterLoad($object);
    }

}
?>