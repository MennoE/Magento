<?php
class Kega_Recently_FlushController extends Mage_Core_Controller_Front_Action
{
    /**
     * removed recently viewed products for current visitor
     *
     * @return Mage_Reports_Model_Product_Index_Abstract
     */
    public function indexAction()
    {

        Mage::getModel('recently/recently')->removeViewedProductsForCurrentVisitor();
		
        $redirectUrl = Mage::getUrl('');
        
		if($this->_getRefererUrl()) {            
			$redirectUrl = $this->_getRefererUrl();
		}
		$this->_redirectUrl($redirectUrl);
        return;
    }
    
}