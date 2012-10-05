<?php
class Kega_Vacancytype_Block_Vacancytype extends Mage_Core_Block_Template
{
	public function __construct()
    {
        parent::__construct();
	}

	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
     public function getVacancytype()     
     { 
        if (!$this->hasData('vacancytype')) {
            $this->setData('vacancytype', Mage::registry('vacancytype'));
        }
        return $this->getData('vacancytype');
        
    }
}