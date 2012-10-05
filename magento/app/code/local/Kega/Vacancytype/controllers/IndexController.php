<?php
class Kega_Vacancytype_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
    	
    	/*
    	 * Load an object by id 
    	 * Request looking like:
    	 * http://site.com/vacancytype?id=15 
    	 *  or
    	 * http://site.com/vacancytype/id/15 	
    	 */
    	/* 
		$vacancytype_id = $this->getRequest()->getParam('id');

  		if($vacancytype_id != null && $vacancytype_id != '')	{
			$vacancytype = Mage::getModel('vacancytype/vacancytype')->load($vacancytype_id)->getData();
		} else {
			$vacancytype = null;
		}	
		*/
		
		 /*
    	 * If no param we load a the last created item
    	 */ 
    	/*
    	if($vacancytype == null) {
			$resource = Mage::getSingleton('core/resource');
			$read= $resource->getConnection('core_read');
			$vacancytypeTable = $resource->getTableName('vacancytype');
			
			$select = $read->select()
			   ->from($vacancytypeTable,array('vacancytype_id','title','content','status'))
			   ->where('status',1)
			   ->order('created_time DESC') ;
			   
			$vacancytype = $read->fetchRow($select);
		}
		Mage::register('vacancytype', $vacancytype);
		*/

		$this->loadLayout();     
		$this->renderLayout();
    }
}