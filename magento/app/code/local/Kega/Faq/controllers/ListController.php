<?php
class Kega_Faq_ListController extends Mage_Core_Controller_Front_Action
{
	/**
	 * List all items from given category
	 */
	public function categoryAction()
	{
		$permalink = $this->getRequest()->getParam('permalink', null);
		$storeId = Mage::app()->getStore()->getStoreId();
		$category = Mage::getModel('faq/category')->loadByPermalinkAndStore($permalink, $storeId);

		if(!$permalink || !$category) {
			$this->norouteAction();
			return;
		}
		Mage::register('faq_permalink', $permalink);

		$this->loadLayout();
        $this->getLayout()->getBlock('head')->setTitle($category->getName() . ' - ' . $this->__('FAQ'));
        
        $breadcrumbs = $this->getLayout()->getBlock('breadcrumbs');
		$breadcrumbs->addCrumb($category->getName(), array('label' => $category->getName(), 'title' => $category->getName()));
        
        $this->renderLayout();

        return;
	}
}

