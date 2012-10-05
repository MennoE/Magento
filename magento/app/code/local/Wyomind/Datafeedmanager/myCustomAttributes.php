<?php

class MyCustomAttributes extends Wyomind_Datafeedmanager_Model_Datafeedmanager{
    
	public function _eval($product,$exp,$value)
	{
		try {
	        switch ($exp['pattern']) 
	        {
				case "{externalimage_large}":
					return $this->getKegaImage($product, 'large_image');
				break;
		
				case "{externalimage_small}":
					return $this->getKegaImage($product, 'small_image');
				break;
				
				case "{externalimage_medium}":
					return $this->getKegaImage($product, 'medium_image');
				break;
				
				case "{externalimage_large2}":
						$imagecollection = $product->getMediaGalleryImages();
						if (count($imagecollection) >= '2') {
							$i = 0;
							foreach ($imagecollection as $image) {
								if ($i === 1){
									$image = $image['url'];
									$imageUrl = str_replace ('medium_image' , 'large_image' , $image);
									return $imageUrl;
								}
								$i++;
							}
						}
						return '';
				break;		
				
				case "{kega_gender}":
					if($product['zie_doelgroep'] != null){
						switch ($product['zie_doelgroep']) {
						    case 'Jongens':
						        return 'male';
						        break;
						     case 'Meisjes':
						        return 'female';
						        break;
						     case 'Dames':
						        return 'female';
						        break;
						     case 'Heren':
						        return 'male';
						        break;
						     case 'Kinderen':
						        return '';
						        break;
						     case 'Unisex':
						        return '';
						        break;
						} 
					}
				break;
	
				case "{kega_shipping_cost}":
					$freeshipping = trim(Mage::getStoreConfig('datafeedmanager/feed_freeshipping/freeshipping'));
					$shippingfee = trim(Mage::getStoreConfig('datafeedmanager/feed_freeshipping/shippingcost'));
					
					if ($freeshipping != null && $shippingfee != null | $freeshipping != '' && $shippingfee != ''){
						$price = $product->getPrice();
						$specialPrice = $product->getSpecialPrice();
						
						//first check specialprice
						if($specialPrice >= $freeshipping && $specialPrice > 0){
							return '0.00';
						} elseif ($specialPrice > 0) {
							return $shippingfee;
						}
						//then do price
						if ($price >= $freeshipping) {
							return '0.00';
						} else {
							return $shippingfee;
						}
					}
					return '';
				break;
		
				case "{created_at}":
					if($product->getCreatedAt()) {
						return 	$product->getCreatedAt();
					}
					return '';
				break;
				
				case "{kega_category_url}":
					$allowedCategories = $this->getAllowedCategories();
					$categoryIdCollection = $this->getCategoryIdCollection($product);
					
					if ($categoryIdCollection == null){
						return '';
					}
					
		        	foreach($categoryIdCollection as $categoryId){
			        	if (in_array($categoryId ,$allowedCategories)) {
			        		$categoryUrl = $this->getCategoryUrl($categoryId);
			        		
			        		return $categoryUrl;
			        	} else {
							continue;
			        	}
		        	}
					return '';
				break;
				
				case "{kega_category}":
					$allowedCategories = $this->getAllowedCategories();
					$categoryIdCollection = $this->getCategoryIdCollection($product);
					
					if ($categoryIdCollection == null){
						return '';
					}
								
		        	foreach($categoryIdCollection as $categoryId){
			        	if (in_array($categoryId ,$allowedCategories)) {
			        		$categoryName = $this->getCategoryName($categoryId);
			        		return $categoryName;
			        	} else {
							continue;
			        	}
		        	}
					return '';
				break;
				
				case "{kega_rootcategory}":
					$rootCategories = $this->getRootCategories();
					$categoryIdCollection = $this->getCategoryIdCollection($product);
					
					if ($categoryIdCollection == null){
						return '';
					}
								
		        	foreach($categoryIdCollection as $categoryId){
			        	if (in_array($categoryId ,$rootCategories)) {
			        		$categoryName = $this->getCategoryName($categoryId);
			        		return $categoryName;
			        	} else {
							continue;
			        	}
		        	}
					return '';
				break;
				
				case "{kega_subcategory}":
					$rootCategories = $this->getRootCategories();
					$categoryIdCollection = $this->getCategoryIdCollection($product);
					
					if ($categoryIdCollection == null){
						return '';
					}
								
		        	foreach($categoryIdCollection as $categoryId){
			        	if (in_array($categoryId ,$rootCategories)) {
			        		$subCategory = $this->getSubcategoryName($categoryId, $categoryIdCollection);
			        		
			        		return $subCategory;
			        	} else {
							continue;
			        	}
		        	}
					return '';
				break;
				
				case "{kega_category_path}":
					$allowedCategories = $this->getAllowedCategories();
					$categoryIdCollection = $this->getCategoryIdCollection($product);
					
					if ($categoryIdCollection == null){
						return '';
					}
								
		        	foreach($categoryIdCollection as $categoryId){
			        	if (in_array($categoryId ,$allowedCategories)) {
			        		$categoryPath = $this->getCategoryPath($categoryId);
			        		return $categoryPath;
			        	} else {
							continue;
			        	}
		        	}
					return '';
				break;
	
	        	case "{kega_price}":
	        		$specialPrice = $product->getSpecialPrice();
					$price = $product->getPrice();
					
	        		if($specialPrice != null && $specialPrice > 0) {
						return number_format($specialPrice, 2);
					} else {
						return number_format($price, 2);
					}
	
				break;
	
				case "{kega_oldprice}":
	                $specialPrice = $product->getSpecialPrice();
					$price = $product->getPrice();
					
	        		if($specialPrice != null && $specialPrice > 0) {
						return number_format($price, 2);
					} else {
						return '';
					}
				break;
				
				case "{deeplink}":
					$url = $product->getProductUrl();
					$url = str_replace ( 'lightspeed.php/' , '' , $url );
					return $url;
				break;
	                
				case "{specialdate}":
					$currentTime = date("Y-m-d H:i:s", Mage::getModel('core/date')->timestamp(time()));
					$specialToDate = $product->getResource()->getAttribute('special_to_date')->getFrontend()->getValue($product);
					$specialFromDate = $product->getResource()->getAttribute('special_from_date')->getFrontend()->getValue($product);
					if (!$specialFromDate || !$specialToDate){
						return null;
					}
					if ($specialFromDate>$currentTime) {
						return null;
					} elseif ($specialToDate<$currentTime){
						return null;
					}
					else {
						return true;
					}
				break;
				
				case "{configurable_size}":
					if($product->type_id=='configurable'){
						$size = '';
						$i = 0;
						
						$childProducts = $product->getTypeInstance()->getUsedProductCollection(); 
						$childProducts->addAttributeToSelect('size');
						$childProducts->addAttributeToSelect('is_in_stock');
	
						foreach ($childProducts as $child){
							if($child->getStockItem()->getIsInStock() >= '1'){
								if ($i==0){
									$size = $child->getResource()->getAttribute('size')->getFrontend()->getValue($child);
								}
								else {
									$size .= '/ '.$child->getResource()->getAttribute('size')->getFrontend()->getValue($child);
								}
								$i++;
							}
						}
						return $size;
					}
					else return  false;
				break;
				
				case "{configurable_size_apart}":
					if($product->type_id=='configurable'){
						$size = '';
						$i = 0;
						
						$childProducts = $product->getTypeInstance()->getUsedProductCollection(); 
						$childProducts->addAttributeToSelect('size');
						$childProducts->addAttributeToSelect('is_in_stock');
	
						foreach ($childProducts as $child){
							if($child->getStockItem()->getIsInStock() >= '1'){
								if ($i==0){
									$size = '<Maat>' . $child->getResource()->getAttribute('size')->getFrontend()->getValue($child) . '</Maat>';
								}
								else {
									$size .= '<Maat>' . $child->getResource()->getAttribute('size')->getFrontend()->getValue($child) . '</Maat>';
								}
								$i++;
							}
						}
						return $size;
					}
					else return  false;
				break;
	
				default :
					return $value;
				break;
			}
		} catch (Exception $e) {
			 echo 'Exception caught: ',  $e->getMessage(), "\n";
		}
	}
	
    /**
     * getRootCategories()
     * 
	 * Retrieves allowed top-level category ids (not THE actual root category) from storeconfig.
	 * Ignores id's that aren't actual root categories
	 * 
	 * Saves array in datafeedmanager/category singleton a
	 *
	 * @return array $passedRootCategories
	 */
    public function getRootCategories()
    {
    	if (Mage::getSingleton('datafeedmanager/category')->getData('passedRootCategories') != null) {
    		$passedRootCategories = Mage::getSingleton('datafeedmanager/category')->getData('passedRootCategories');
    		return $passedRootCategories;
    		
    	} else {
	    	if (Mage::getStoreConfig('datafeedmanager/feed_categories/root_category')) {
	    		$siteRootCategory = Mage::getModel('catalog/category')->load(Mage::getStoreConfig('datafeedmanager/feed_categories/root_category'));
	    	} else {
	    		$siteRootCategory = Mage::getModel('catalog/category')->load(Mage::app()->getStore('default')->getRootCategoryId());
	    	}
	    	$allRootCategories = explode(',', $siteRootCategory->getChildren());
	    	
			$allowedRootCategories = explode(";", Mage::getStoreConfig('datafeedmanager/feed_categories/toplevel_categories'));
			$allowedRootCategories = array_map('trim', $allowedRootCategories);
			
			$passedRootCategories= array();
		
			foreach($allowedRootCategories as $allowedRootCategory)
			{	
				if(in_array($allowedRootCategory, $allRootCategories)) {
					$passedRootCategories[] = $allowedRootCategory;
				}
			}
	
			Mage::getSingleton('datafeedmanager/category')->setData('passedRootCategories', $passedRootCategories);
			return $passedRootCategories;
    	}
    }
    
    /**
     * getAllowedCategories()
     * 
     * Recursively retrieves all active categories under $rootCategories.
     * 
     * Tries to get $allowCategories from singleton model datafeedmanager/category first.
     * If not available in this singleton model, they are generated and set.
     * 
     * @return array $allowedCategories
     *  
	 */    
    public function getAllowedCategories()
    {
		if (Mage::getSingleton('datafeedmanager/category')->getData('allowedCategories') != null){
			$allowedCategories = Mage::getSingleton('datafeedmanager/category')->getData('allowedCategories');
			
			return $allowedCategories;
		} else {
			$rootCategories = $this->getRootCategories();
			$allowedCategories = array();
			
			foreach ($rootCategories as $rootCategory) {
				$category = Mage::getModel('catalog/category')->load($rootCategory);
				$childCategories = $category->getAllChildren($category);
				unset($childCategories[0]);
				$allowedCategories = array_merge($allowedCategories, $childCategories);
				
				// recursively loop through categories until we got it all baby!
				$allowedCategories = $this->addChildCategoryIds($childCategories, $allowedCategories);
			}
			
			// append (merge) root categories to $allowedCategories
			$allowedCategories = array_merge($rootCategories, $allowedCategories);
			
			Mage::getSingleton('datafeedmanager/category')->setData('allowedCategories', $allowedCategories);
			return $allowedCategories;
		}
    }

    /** 
     * addChildCategoryIds()
     * 
     * Recursively loop through all childs of $categoriesCollection until everything 
     * is added to $allowedCategories
     * 
	 * @param array $categoriesCollection - collection of category ids
	 * @param array $allowedCategories - already accumulated collection of allowed category ids
	 * 
	 * @return array $allowedCategories
	 * 
	 */
	public function addChildCategoryIds($categoriesCollection, $allowedCategories) 
	{
		if(count($categoriesCollection = 1)){
			return $allowedCategories;
		} else {
			foreach ($categoriesCollection as $childCategory) {
				$category = Mage::getModel('catalog/category')->load($childCategory);
				$categoriesCollection = $category->getResource()->getAllChildren($category);
				unset($categoriesCollection[0]);
				$allowedCategories = array_merge($allowedCategories, $this->addChildCategoryIds($categoriesCollection, $allowedCategories)); 
			}
		}
	}
    
    /**
     * getCategoryUrl()
     * 
	 * Retrieves category url value from datafeedmanager/category singleton model if available
	 * Loads category model and gets url if not set in singleton model, and sets in singleton
	 *
	 * @param string $categoryId
	 * @return array $categoryUrl
	 */
    public function getCategoryUrl($categoryId)
    {
		if (Mage::getSingleton('datafeedmanager/category')->getData('cat-'.$categoryId) != null){
			$categoryUrl = Mage::getSingleton('datafeedmanager/category')->getData('cat-'.$categoryId);
			
			return $categoryUrl;
		} else {
			$categoryUrl = Mage::getModel('catalog/category')->load($categoryId)->getUrl();
			$categoryUrl = str_replace('/index.php', '', $categoryUrl);

			Mage::getSingleton('datafeedmanager/category')->setData('cat-' . $categoryId, $categoryUrl);
			
			return $categoryUrl;
		}
    }
    
    /**
     * getCategoryName()
     * 
	 * Retrieves category name value from datafeedmanager/category singleton model if available
	 * Loads category model and gets name if not set in singleton model, and sets it in singleton
	 *
	 * @param string $categoryId
	 * @return array $categoryUrl
	 */
    public function getCategoryName($categoryId)
    {
		if (Mage::getSingleton('datafeedmanager/category')->getData('cat_name-'.$categoryId) != null){
			$categoryName = Mage::getSingleton('datafeedmanager/category')->getData('cat_name-'.$categoryId);

			return $categoryName;
		} else {
			$categoryName = Mage::getModel('catalog/category')->load($categoryId)->getName();

			$categoryName = $this->getCategoryRename($categoryName);

			Mage::getSingleton('datafeedmanager/category')->setData('cat_name-' . $categoryId, $categoryName);
			return $categoryName;
		}
    }
    
    /**
     * getCategoryRename()
     * 
     * Returns value from $categoryRenameArray for key $categoryName if exists in array
	 *
	 * @param string $categoryName
	 * @return string $categoryName
	 */
    public function getCategoryRename($categoryName)
    {
		$categoryRenameArray = $this->getCategoryRenameData();
		
		if (array_key_exists($categoryName,$categoryRenameArray)){
			$categoryName = $categoryRenameArray[$categoryName];
		}
		return $categoryName;
	}
	
    /**
     * getSubcategoryName()
     * 
     * Returns name of subcategory for this rootCategory
     * 
	 * @param string $rootCategoryId
	 * @param array $productCategoryIdCollection
	 * 
	 * @return string $subCategoryName
	 */
    public function getSubcategoryName($rootCategoryId, $productCategoryIdCollection)
    {
    	$rootCategory = Mage::getModel('catalog/category')->load($rootCategoryId);
    	$subCategories = $rootCategory->getChildren();
    	$subCategories = explode(',', $subCategories);
    	unset($subCategories[0]);
    	
    	arsort($productCategoryIdCollection);
    	
    	foreach ($productCategoryIdCollection as $productCategoryId) {
    		if(in_array($productCategoryId, $subCategories)) {
    			$subCategoryName = $this->getCategoryName($productCategoryId);
    			break;
    		}
    	}
    	
    	if (isset($subCategoryName)) {
    		return $subCategoryName;
    	}
    	else {
    		 return '';
    	}
	}

	/**
	 * getCategoryRenameData()
	 * 
	 * Gets data from storeConfig
	 * Outputs array with old category name as key and new category name as value
	 * 
	 * @return array $categoryRenameArray
	 */
	public function getCategoryRenameData()
	{
		if (Mage::getSingleton('datafeedmanager/category')->getData('categoryRenameArray') != null) {
			$categoryRenameArray = Mage::getSingleton('datafeedmanager/category')->getData('categoryRenameArray');
		} else {
			$categoryRenameData = Mage::getStoreConfig('datafeedmanager/feed_rename_categories/rename_categories');
			$categoryRenameArray = array();
	
			if($categoryRenameData !=null) {
				$categoryRenameData = explode("\n", $categoryRenameData);

				foreach ($categoryRenameData as $categoryRenameLine) {
					$categoryRenameLine = explode(',', $categoryRenameLine);

					$categoryRenameArray[trim($categoryRenameLine[0])] = trim($categoryRenameLine[1]);
				}
				Mage::getSingleton('datafeedmanager/category')->setData('categoryRenameArray', $categoryRenameArray);
		    }
    	}
    	return $categoryRenameArray;
    }
    
    /**
     * getCategoryPath()
     * 
	 * Retrieves category path value from datafeedmanager/category singleton model if available
	 * Loads category model and gets path if not set in singleton model, and sets it in singleton
	 *
	 * @param string $categoryId
	 * @return array $categoryUrl
	 */
    public function getCategoryPath($categoryId)
    {
		if (Mage::getSingleton('datafeedmanager/category')->getData('cat_path-'.$categoryId) != null){
			$categoryPath = Mage::getSingleton('datafeedmanager/category')->getData('cat_path-'.$categoryId);
			
			return $categoryPath;
		} else {
			$rootCategories = $this->getRootCategories();

			$category = Mage::getModel('catalog/category')->load($categoryId);
			$categoryPath = $category->getName();
			
			$categoryPath = $this->addParentCategoryName($category, $rootCategories) . $categoryPath;
			
			Mage::getSingleton('datafeedmanager/category')->setData('cat_path-' . $categoryId, $categoryPath);
			return $categoryPath;
		}
    }

    /**
     * addParentCategoryName()
	 * 
	 * Manually loop upwards through categories and generate full category path
	 * I did this with a recursive function, but this was causing the feed generation to break
	 * This wasn't a memory problem but I failed to find out was caused this.
	 *
	 * @param array $category
	 * @param array $rootCategories
	 * @param string | null - category path string
	 * 
	 * @return string $categoryPath
	 */
	public function addParentCategoryName($category, $rootCategories, $categoryPath = null) 
	{
		if(in_array($category->getParentCategory()->getId(), $rootCategories)){
			$category = $category->getParentCategory();
			$categoryPath = $this->getCategoryName($category->getId()).' > '.$categoryPath;
			
			return $categoryPath;
		} else {
			if ($category->getLevel() < '4') { 
				return $categoryPath;
			}
			$category = $category->getParentCategory();
			$categoryPath = $this->getCategoryName($category->getId()).' > '.$categoryPath;

			if(in_array($category->getParentCategory()->getId(), $rootCategories)){
				$category = $category->getParentCategory();
				$categoryPath = $this->getCategoryName($category->getId()).' > '.$categoryPath;
				
				return $categoryPath;
			} else {
				if ($category->getLevel() < '4') { 
					return $categoryPath;
				}
				$category = $category->getParentCategory();
				$categoryPath = $this->getCategoryName($category->getId()).' > '.$categoryPath;
				
				if(in_array($category->getParentCategory()->getId(), $rootCategories)){
					$category = $category->getParentCategory();
					$categoryPath = $this->getCategoryName($category->getId()).' > '.$categoryPath;
					
					return $categoryPath;
				} else {
					if ($category->getLevel() < '4') { 
						return $categoryPath;
					}
					$category = $category->getParentCategory();
					$categoryPath = $this->getCategoryName($category->getId()).' > '.$categoryPath;
					
					if(in_array($category->getParentCategory()->getId(), $rootCategories)){
						$category = $category->getParentCategory();
						$categoryPath = $this->getCategoryName($category->getId()).' > '.$categoryPath;
						
						return $categoryPath;
					} else {
						if ($category->getLevel() < '4') { 
							return $categoryPath;
						}
						$category = $category->getParentCategory();
						$categoryPath = $this->getCategoryName($category->getId()).' > '.$categoryPath;
						return $categoryPath;
					}
				} 
			} 
		}
	}
    
    /**
     * sortCategoryIdCollection()
	 *
	 * Sorts category collection
	 *
	 * @param array $categoryIdCollection
	 * @return array $categoryIdCollection
	 */
	public function sortCategoryIdCollection($categoryIdCollection)
	{
		//sorting by array value from high to low (descending)
		arsort($categoryIdCollection);
		
		return $categoryIdCollection;
	}

    /**
     * getCategoryIdCollection()
	 *
	 * gets all category id's linked to product and calls sortCategoryIdCollection()
	 *
	 * @param array $product
	 * @return array $categoryIdCollection
	 */
	public function getCategoryIdCollection($product)
	{
		//get our category ids from product
		$categoryIdCollection = array();
		$categoryIdCollection = $product->getCategoryIds();
		
		// remove blacklisted category ids from our category id collection
		$blacklistedCategories = explode(";", Mage::getStoreConfig('datafeedmanager/feed_categories/ignore_categories'));
		if ($blacklistedCategories != null) {
			foreach ($blacklistedCategories as $category) {
				$key = array_search($category, $categoryIdCollection);
				if ($key !== null) {
					unset($categoryIdCollection[$key]);
				}
			}
		}
		//reassign array keys
		$categoryIdCollection = array_values($categoryIdCollection);
		
		// extend our category id collection with parent categories of our categories etc until we reach root categories
		if(Mage::getStoreConfig('datafeedmanager/feed_categories/force_anchor') == 1) {
			$categoryIdCollection = $this->createAnchorCategoryCollection($categoryIdCollection);
		}
		
		//sort our category id collection the way we want it before returning it
		$categoryIdCollection = $this->sortCategoryIdCollection($categoryIdCollection);
		
		return $categoryIdCollection;
	}
	
	public function createAnchorCategoryCollection($categoryIdCollection)
	{
		$rootCategories = $this->getRootCategories();
		
		foreach ($categoryIdCollection as $categoryId) {
			if (in_array($categoryId, $rootCategories)) {
				continue;
			}
			else {
				$parentIds = array();
				if (Mage::getSingleton('datafeedmanager/category')->getData('cat_parent-'.$categoryId) != null){
					$parentIds = Mage::getSingleton('datafeedmanager/category')->getData('cat_parent-'.$categoryId);
					$categoryIdCollection =  array_merge_recursive($categoryIdCollection, $parentIds);
				} else {
					$parentIds = $this->getParentCategoryIds($categoryId, $parentIds);

					Mage::getSingleton('datafeedmanager/category')->setData('cat_parent-' . $categoryId, $parentIds);

					$categoryIdCollection = array_merge_recursive($categoryIdCollection, $parentIds);
				}
			}
		}
		return $categoryIdCollection;
	}

	public function getParentCategoryIds($categoryId, $parentIds)
	{
		$category = Mage::getModel('catalog/category')->load($categoryId);
		if ($category->getLevel() > '3') {
			$parentId = $category->getParentId();
			$parentIds[] = strval($parentId);
			return $this->getParentCategoryIds($parentId, $parentIds);
		} else {
			return $parentIds;
		}
	}
	
    /**
     * getKegaImage()
     * 
	 * Retrieves image url
	 *
	 * @param string $imageType - e.g. 'small_image'
	 * @param array $product
	 * 
	 * @return string $imageUrl
	 */
	public function getKegaImage($product, $imageType)
	{
		$imageUrl = Mage::helper('catalog/image')->getExternalUrl($product, $imageType);
		if($imageUrl) { 
			return $imageUrl;
		}
		return '';
	}
}