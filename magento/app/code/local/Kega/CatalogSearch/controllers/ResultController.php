<?php

/**
 * ResultController.php
 *
 * @category   Kega
 * @package    Kega_CatalogSearch
 */
require_once 'Mage/CatalogSearch/controllers/ResultController.php';
class Kega_CatalogSearch_ResultController extends Mage_CatalogSearch_ResultController
{
	/**
	 * Kega_Catalog_CategoryController->indexAction
	 * Choose which way we want to go for the search and display the results
	 * $type = site; searches the whole website using the google api
	 * $type = catalog; searches the catalog only using the default Magento search
	 *
	 * @param void
	 * @return void
     */
    const XML_USE_GOOGLE_CSE = 'catalog/search/use_google_cse';
    
    public function indexAction()
    {
		$type = $this->getRequest()->getParam('type', 'catalog');

		// Prevent Google CSE queries when inactive
		if (!Mage::getStoreConfig(self::XML_USE_GOOGLE_CSE)) {
			$type = 'catalog';
		}

		switch($type)
		{
			case 'catalog':
			default:
				$query = Mage::helper('catalogsearch')->getQuery();
				/* @var $query Mage_CatalogSearch_Model_Query */

				$query->setStoreId(Mage::app()->getStore()->getId());

				if ($query->getQueryText()) {
					if (Mage::helper('catalogsearch')->isMinQueryLength()) {
						$query->setId(0)
							->setIsActive(1)
							->setIsProcessed(1);
					}
					else {
						if ($query->getId()) {
							$query->setPopularity($query->getPopularity()+1);
						}
						else {
							$query->setPopularity(1);
						}

						if ($query->getRedirect()){
							$query->save();
							$this->getResponse()->setRedirect($query->getRedirect());
							return;
						}
						else {
							$query->prepare();
						}
					}
					
					$resultCount = Mage::getSingleton('catalogsearch/layer')->getProductCollection()->getSize(); 
                    			if ($resultCount == 0 
							&& Mage::helper('catalogsearch')->getQueryText() !== '' 
							&& Mage::getStoreConfig(Mage::getStoreConfig(self::XML_USE_GOOGLE_CSE))) { 
						$arguments = array('q' => $this->getRequest()->getParam('q'), 'type' => 'site'); 
						$this->_redirect('*/*/', $arguments); 
					} 

					Mage::helper('catalogSearch')->checkNotes();

					$this->loadLayout();
					$this->_initLayoutMessages('catalog/session');
					$this->_initLayoutMessages('checkout/session');
					$this->renderLayout();

					if (!Mage::helper('catalogSearch')->isMinQueryLength()) {
						$query->save();
					}
				}
				else {
					$this->_redirectReferer();
				}
			break;

			case 'site':
				$this->_getGoogleResults();
			break;
		}
    }

	/**
	 * Kega_Catalog_CategoryController->_getGoogleResults
	 * Retrieve the search results from google
	 *
	 * @param void
	 * @return void
     */
	private function _getGoogleResults()
	{
		$cse_key = Mage::app()->getStore()->getConfig('catalog/search/google_cse_key');
		if (empty($cse_key)) {
			die('Google CSE key could not be found!');
		}

		$offset = ($this->getRequest()->getParam('p', 1) - 1) * 10;
		$loc = sprintf('http://%s/cse?cx=%s&q=%s&output=%s%s',
			'www.google.com',
			$cse_key,
			$this->getRequest()->getParam('q'),
			'xml',
			$offset ? '&start=' . $offset : ''
		);

		$data = simplexml_load_file($loc);
		$data = $this->_parseGoogleXML($data);
		Mage::register('googleresult', $data);

		// normal way not working due to loadLayoutUpdates
		$head = $this->getLayout()->createBlock('Kega_Meta_Block_Html_Head');
		$head->overwriteTitle(
			sprintf(
				$this->__("Search results for: '%s'"),
				htmlentities($this->getRequest()->getParam('q'))
			)
		);

		$update = $this->getLayout()->getUpdate();

		// Use different layout handle for google result pages
		$update->addHandle('default');
		$update->addHandle('catalogsearch_google_result_index');

		$this->addActionLayoutHandles();
		$update->removeHandle('catalogsearch_result_index');

		$this->loadLayoutUpdates();

		$this->generateLayoutXml()->generateLayoutBlocks();
		$this->renderLayout();
	}

	/**
	 * Kega_Catalog_CategoryController->_parseGoogleXML
	 * Parse Google XML to result array
	 *
	 * @param $struct array
	 * @return void
     */
	private function _parseGoogleXML($struct)
	{
		if (!isset($struct->RES)) {
			return array();
		}

		$data = array();

		$data['p'] = $this->getRequest()->getParam('p', 1);
		$data['total'] = $struct->RES->M + (($data['p']-1) * 10);
		$data['pages'] = ceil($data['total'] / 10);

		$boundary = 5;
		$data['start'] = $data['p'] <= $boundary ? 1 : $data['p'] - $boundary;
		$data['end'] = $data['p'] >= $data['pages'] - $boundary ? $data['pages'] : $data['p'] + $boundary;

		foreach($struct->RES->R as $node => $result) {
			// Skip junk nodes in result struct
			if (!isset($result->T)) {
				continue;
			}

			$data['results'][] = array(
				'title' => $result->T,
				'desc' => $result->S,
				'link' => $result->U
			);
		}
		return $data;
	}
}