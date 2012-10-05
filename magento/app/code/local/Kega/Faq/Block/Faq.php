<?
/**
 * @category   Kega
 * @package    Kega_Faq
 */
class Kega_Faq_Block_Faq extends Mage_Core_Block_Template
{
	public function __construct()
    {
        parent::__construct();
	}
	
	/**
	 * Retreive items.
	 *
	 * What items are returned depends on which key is set in registry:
	 * - faq_search: do a search and return items
	 * - faq_category: find and return all questions for given category
	 * - no key set: return all questions
	 *
	 * Questions are returned in an array where they are grouped by category.
	 */
	public function getItems()
	{
		if($search = Mage::registry('faq_search')) {
			$items = Mage::getModel('faq/category')->setStoreId(Mage::app()->getStore()->getId())
													->searchFaqQuestions($search);
		} else if($permalink = Mage::registry('faq_permalink')) {
		    $items = array();
			$model = Mage::getModel('faq/category');
			$storeId = Mage::app()->getStore()->getStoreId();
			$category = $model->loadByPermalinkAndStore($permalink, $storeId);
		
            if($category) {
			     $items = $model->setStoreId(Mage::app()->getStore()->getId())
											 ->retrieveQuestionByCategory($category->getId());
            }
		} else {
			$items = Mage::getModel('faq/category')->setStoreId(Mage::app()->getStore()->getId())
											 ->retrieveQuestionByCategory(false, 50);
		}
		return $items;
	}

	/**
	 * get current search question. Return false not available
	 *
	 * @param void
	 * @return String
	 */
	public function getSearchQuery()
	{
		return Mage::registry('faq_search');
	}

	/**
	 * Kega_Faq_Block_Faq->retrieveQuestionByCategory()
	 * Returns an array containing all categories with related questions in a subarray
	 *
	 * @param void
	 * @return array
	 */
	public function retrieveQuestionByCategory()
	{
		return Mage::getModel('faq/category')->setStoreId(Mage::app()->getStore()->getId())
											 ->retrieveQuestionByCategory();
	}	
}