<?php

/**
* @category Unirgy
* @package Unirgy_RapidFlow
*/
abstract class RapidFlow_Model_Preprocess_ProductAbstract extends Mage_Core_Model_Abstract
{
   /**
	* Trigger abstract construct
	*
	* @param void
	* @return void
	*/
	public function __construct()
	{
		parent::__construct();
	}

   /**
	* Trigger abstract construct
	*
	* @param void
	* @return void
	*/
	public function processProductLines()
	{
		$handle = fopen($filename, "r");
		$count = 0;
		while (($data = fgetcsv($handle, 4000, ";")) !== false)
		{
	}
}