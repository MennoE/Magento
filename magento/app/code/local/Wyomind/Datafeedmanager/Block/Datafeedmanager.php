<?php
class Wyomind_Datafeedmanager_Block_Datafeedmanager extends Mage_Core_Block_Template
{
	public function _prepareLayout()
	{
		return parent::_prepareLayout();
	}

	public function getDatafeedmanager()
	{
		if (!$this->hasData('datafeedmanager')) {
			$this->setData('datafeedmanager', Mage::registry('datafeedmanager'));
		}
		return $this->getData('datafeedmanager');

	}
}