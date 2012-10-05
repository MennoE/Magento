<?php
class Kega_Vacancy_List2Controller extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
    	
//		echo("frontend Index List2 Action");

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

		$typesCollection = Mage::getModel('vacancytype/vacancytype')->getCollection();

		$vacCollection = Mage::getModel('vacancy/vacancy')->getCollection();

		$export = array();

		foreach($typesCollection as $vacancyType){
			$export[$vacancyType->getId()]["vacancyType"] = $vacancyType;
		}

		foreach($vacCollection as $vacancy){
			$export[$vacancy->getVacancytypeId()]["vacancies"][] = $vacancy;
		}


//		Mage::register('vacancies', $collection);
		Mage::register('vacancies_data', $export);

			
		$this->loadLayout();     
		$this->renderLayout();
    }

}

?>