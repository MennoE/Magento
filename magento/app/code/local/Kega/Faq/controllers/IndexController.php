<?
class Kega_Faq_IndexController extends Mage_Core_Controller_Front_Action
{
	/**
	 * FAQ index controller
	 *
	 * check if a search is done. If if a search if found put it in registry.
	 * Otherwise just render page.
	 */
	function indexAction()
	{
		if ($this->getRequest()->getParam('do') == 'search') {
			Mage::register('faq_search', $this->getRequest()->getParam('criteria'));
		}

        $this->loadLayout();
        $this->getLayout()->getBlock('head')->setTitle($this->__('FAQ'));
        $this->renderLayout();
	}
}