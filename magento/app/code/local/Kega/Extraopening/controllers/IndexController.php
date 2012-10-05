<?php
class Kega_Extraopening_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
    	
    	/*
    	 * Load an object by id 
    	 * Request looking like:
    	 * http://site.com/extraopening?id=15 
    	 *  or
    	 * http://site.com/extraopening/id/15 	
    	 */
    	/* 
		$extraopening_id = $this->getRequest()->getParam('id');

  		if($extraopening_id != null && $extraopening_id != '')	{
			$extraopening = Mage::getModel('extraopening/extraopening')->load($extraopening_id)->getData();
		} else {
			$extraopening = null;
		}	
		*/
		
		 /*
    	 * If no param we load a the last created item
    	 */ 
    	/*
    	if($extraopening == null) {
			$resource = Mage::getSingleton('core/resource');
			$read= $resource->getConnection('core_read');
			$extraopeningTable = $resource->getTableName('extraopening');
			
			$select = $read->select()
			   ->from($extraopeningTable,array('extraopening_id','title','content','status'))
			   ->where('status',1)
			   ->order('created_time DESC') ;
			   
			$extraopening = $read->fetchRow($select);
		}
		Mage::register('extraopening', $extraopening);
		*/

			
		$this->loadLayout();     
		$this->renderLayout();
    }
}