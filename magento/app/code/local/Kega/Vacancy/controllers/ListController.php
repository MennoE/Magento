<?php
class Kega_Vacancy_ListController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
    	
//		echo("frontend Index Liist Action");

		 /*
    	 * If no param we load a the last created item
    	 */ 
    	
/*
		$resource = Mage::getSingleton('core/resource');
		$read= $resource->getConnection('core_read');
		$vacancyTable = $resource->getTableName('vacancy');
		
		$select = $read->select()
		   ->from($vacancyTable,array('vacancy_id','title','content','status'))
		   ->where('status',1)
		   ->order('created_time DESC') ;
		   
		$vacancy = $read->fetchRow($select);
*/

		$collection = Mage::getModel('vacancy/vacancy')->getCollection();

		Mage::register('vacancies', $collection);


			
		$this->loadLayout();     
		$this->renderLayout();
    }

}

?>