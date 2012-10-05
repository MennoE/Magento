<?php

class Wyomind_Datafeedmanager_Model_Observer
{	
	
	/** 
	* Generate all active feeds
	*/
	public function generateDatafeeds()
    {
		$collection = Mage::getModel('datafeedmanager/datafeedmanager')->getCollection();
		foreach ($collection as $feed) {
			if ($feed->getFeedStatus() == 1) {
				$this->generateFeed($feed->getFeedId());
			}
		}
    	return $this;
    }

	/** 
	* 
	* Abstract method used for generating feeds
	* @param $id
	* 
	*/ 
	public function generateFeed($id)
	{
		$datafeedmanager = Mage::getModel('datafeedmanager/datafeedmanager');
		$datafeedmanager->setId($id);	
		$limit = null;
		$datafeedmanager->_limit=$limit;
		if ($datafeedmanager->load($id)) {
			$datafeedmanager->generateFile();
			$ext=array(1=>'xml',2=>'txt',3=>'csv');
		}
	}
	
}