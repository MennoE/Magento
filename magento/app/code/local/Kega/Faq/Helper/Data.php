<?php

class Kega_Faq_Helper_Data extends Mage_Core_Helper_Abstract
{
	public function getStoreViewNames($categoryId) {
		$storeIds = Mage::getResourceModel('faq/category')->getStoresIdArray($categoryId);
		$storeViewNames = '';
		if ($storeIds) {
			$i = 0;
			foreach ($storeIds as $storeId) {
				if ($storeId != 0) {
					if ($i == 0) { 
						$storeViewNames .= Mage::app()->getStore($storeId)->getName();
						$i++;
					} else {
						$storeViewNames .= ', '.Mage::app()->getStore($storeId)->getName();
					}
				} else {
					if ($i == 0) { 
						$storeViewNames .= $this->__('All Store Views');
						$i++;
					} else {
						$storeViewNames .= ', '. $this->__('All Store Views');
					}
				}
			}
		}
		return $storeViewNames;
	}
}