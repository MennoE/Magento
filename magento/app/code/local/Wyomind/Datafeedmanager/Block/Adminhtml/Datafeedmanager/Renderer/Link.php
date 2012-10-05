<?php

class Wyomind_Datafeedmanager_Block_Adminhtml_Datafeedmanager_Renderer_Link
extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{

	 
	public function render(Varien_Object $row)
	{
		$types=array('none','xml','txt','csv');
		$ext=$types[$row->getFeed_type()];
		$fileName = preg_replace('/^\//', '', $row->getFeed_path() . $row->getFeed_name().'.'.$ext);
		$url = $this->htmlEscape(Mage::app()->getStore($row->getStoreId())->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB) . $fileName);

		if (file_exists(BP . DS . $fileName)) {
			return sprintf('<a href="%1$s" target="_blank">%1$s</a>', $url);
		}
		return $url;
	}

}
