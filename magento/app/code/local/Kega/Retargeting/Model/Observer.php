<?php

class Kega_Retargeting_Model_Observer
{
	/**
	 * Loads available retargeting in register
	 * Checks for each pixel if they are enable in storeconfig
	 *
	 * @see doc/readme.txt
	 * @param void
	 * @return void
	 */
	public function loadRetargeting()
	{
	    $retargeting = array();
		$pixels = array();

		// check for each pixel if it's enabled in config (store view)
	/*
		if (Mage::getStoreConfig('retargeting/retargeting/example'))
		{
			array_push($pixels, 'example');
		}
	*/	

		foreach ($pixels as $pixel) {
			$pixel = Mage::getModel('kega_retargeting/' . $pixel->getPixel());
			$retargeting[] = $pixel;
		}

		Mage::register('retargeting', $retargeting, true);
	}
}