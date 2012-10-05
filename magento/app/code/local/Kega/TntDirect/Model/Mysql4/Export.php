<?php

class Kega_TntDirect_Model_Mysql4_Export extends Mage_Core_Model_Mysql4_Abstract
{
	public function _construct()
	{
		$this->_init('kega_tntdirect/export', 'id');
	}
}