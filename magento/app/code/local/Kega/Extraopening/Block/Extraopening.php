<?php
class Kega_Extraopening_Block_Extraopening extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
     public function getExtraopening()     
     { 
        if (!$this->hasData('extraopening')) {
            $this->setData('extraopening', Mage::registry('extraopening'));
        }
        return $this->getData('extraopening');
        
    }
}