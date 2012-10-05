<?php
/**
 * FAQ Category Model
 */
class Kega_Faq_Model_Category extends Mage_Core_Model_Abstract
{
    protected $isNew = true;

	/**
	 * Construct. Init Model & set DB connection
	 */
    public function _construct()
    {
        parent::_construct();
        $this->_init('faq/category');
        
        $this->storeId = Mage_Core_Model_App::ADMIN_STORE_ID;

        $this->_resource = Mage::getSingleton('core/resource');
        $this->_read = $this->_resource->getConnection('core_read');
    }
    
	public function getStoreId()
    {
    	return $this->storeId;
    }
    
	public function setStoreId($storeId)
    {
    	$this->storeId = $storeId;
    	
    	return $this;
    }

    /**
     * Get url for category
     *
     * @param void
     * @return String url
     */
    public function getUrl($permalink = false)
    {
    	if(!$permalink) {
    		$permalink = $this->getPermalink();
    	}
    	return Mage::getUrl('faq/list/category', array('permalink' => $permalink));
    }

    /**
     * Get current category by key
     * 
     * @param string Column name
     * @param string Value to search for
     * @return object
     */
	public function getCurrentCategory($key, $value)
	{
		$currentCategory = array();
		$storeId = Mage::app()->getStore()->getId();

		$categoryModel = Mage::getModel('faq/category')
			->getCollection()
			->addFieldToFilter($key, $value);

		foreach ($categoryModel as $category) {
			$currentCategory = $category;
		}
        
        if(!$currentCategory){
            return false;
        }
        
		return $currentCategory;
	}
	
	public function loadByPermalinkAndStore($permalink, $storeId)
	{
		return $this->_getResource()
            ->loadByPermalinkAndStore($this, $permalink, $storeId);
	}
	
	/**
	 * Retrieve all or a single category and the related questions
	 * The amount of questions can be limited w/ $limit param
	 * If there's only one category to display, it has to be a collection,
	 * so the template can do a foreach. This is managed by $singleAsCollection param
	 *
	 * @param Integer $categoryId
	 * @param Integer $limit
	 * @param bool $singleAsCollection
	 * @return array
	 */
	public function retrieveQuestionByCategory($categoryId = false, $limit = false, $singleAsCollection = true)
	{
		if ($categoryId) {
			$collection = array();
			$category = Mage::getModel('faq/category')->load($categoryId);
			$category->setQuestions($this->getQuestionsByCategoryId($categoryId));
			if ($singleAsCollection) {
				$collection[] = $category;
				return $collection;
			}
			else {
				return $category;
			}
		}
		else {
			$collection = $this->getCollection()->addStoreFilter($this->getStoreId())
												->addOrder('display_order','ASC');

			foreach ($collection as $item) {
				$questions = $this->getQuestionsByCategoryId($item->getId());
				$item->questions = $questions;

				$collection->item = $item;
			}
			return $collection;
		}
	}
	
	public function getQuestionsByCategoryId($id)
	{
		$questions = Mage::getModel('faq/question')->getCollection()
												   ->addFieldToFilter('category_id', $id)
                                                   ->addOrder('display_order','ASC');
		return $questions;
	}

	/**
	 * Retreive all questions matching the search key. Questions are returned
	 * grouped under it's corresponding category.
	 *
	 * @param String $where
	 * @return Array results
	 */
	public function searchFaqQuestions($where)
	{

		$storeId = $this->getStoreId();

		// Retrieve matching categories
		$categories = Mage::getModel('faq/category')->getCollection()
													->addFieldToFilter('name', array('like' => '%' . $where . '%'))
													->addStoreFilter($this->getStoreId());

		// Retrieve matching questions
		$questions = Mage::getModel('faq/question')->getCollection()
													->addFieldToFilter(
														array(
															'main_table.question','main_table.answer'
														),
														array(
															array('like'=>'%' . $where . '%'),
															array('like'=>'%' . $where . '%')
														)
													)
													->addStoreFilter($this->getStoreId());

		// Get all matching category IDs
		$categoryIds = array();
		foreach ($categories as $c) {
			if (!in_array($c->getCategoryId(), $categoryIds)) {
				$categoryIds[] = $c->getCategoryId();
			}
		}
		foreach ($questions as $q) {
			if (!in_array($q->getCategoryId(), $categoryIds)) {
				$categoryIds[] = $q->getCategoryId();
			}
		}

		// Loop through all category IDs and retrieve the categories with questions
		$collection = array();
		foreach ($categoryIds as $categoryId) {
			$collection[] = $this->retrieveQuestionByCategory($categoryId, false, false);
		}

		return $collection;
	}

	protected function _beforeSave()
	{   
		parent::_beforeSave();	
		$this->_getResource()->saveGlobalData($this);
		
		return $this;
	}
	
	protected function _afterSave()
	{   
		parent::_afterSave();	
		$this->_getResource()->saveStoreViewData($this);
		
		return $this;
	}
	
	/**
	 * Format result array from mysql query as a array.
	 *
	 * @param Array $data
	 * @return Array
	 */
	private function _formatItemArray($data)
	{
		$items = array();
		foreach($data as $q) {
			$items[$q['category_id']]['name'] = $q['category'];
			$items[$q['category_id']]['permalink'] = $q['permalink'];
			$items[$q['category_id']]['overview_image'] = $q['overview_image'];
			$items[$q['category_id']]['category_image'] = $q['category_image'];
			$items[$q['category_id']]['questions'][$q['question_id']]['question'] = $q['question'];
			$items[$q['category_id']]['questions'][$q['question_id']]['answer'] = $q['answer'];
        }
        return $items;
	}
	
	public static function getColumns()
    {
    	return array(
    		'name',
    		'permalink',
    		'display_order',
			'overview_image',
			'category_image',
    	);
    }
}