<?php
class Wyomind_Datafeedmanager_Block_Adminhtml_Datafeedmanager_Renderer_Type
extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	public function render(Varien_Object $row)
	{
		$types=array('none','xml','txt','csv');
		return $types[$row->getFeed_type()];
	}

}