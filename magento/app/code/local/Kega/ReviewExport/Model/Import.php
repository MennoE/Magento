<?php

class Kega_ReviewExport_Model_Import extends Mage_Core_Model_Abstract
{
	const EXPORT_DIRECTORY = Kega_ReviewExport_Model_Export::EXPORT_DIRECTORY;
	const WEBSITE_ID = 1;

	/**
	 * Import reviews from export directory
	 * 
	 * @param void
	 * @return void
	 */
	public function import()
	{
		$startdate = date('Y-m-d H:i:s');

		$importDirectory = Mage::getBaseDir('var') . DS . self::EXPORT_DIRECTORY;

		$files = scandir($importDirectory);
		unset($files[0]);
		unset($files[1]);

		if (count($files) <= 0) {
			die('no review files to be imported');
		}

		$reviewCount = 0;
		$errors = 0;
		foreach ($files as $file) {
			$fullReviewData = $this->_getFileContent($importDirectory . DS . $file);

			if ($this->_importReview($fullReviewData)) {
				unlink($importDirectory . DS . $file);
				$reviewCount++;
			}
			else {
				$errors++;
			}
		}

		echo 'start: ' . $startdate . PHP_EOL;
		echo '- end: ' . date('Y-m-d H:i:s') . PHP_EOL;
		echo '- reviews imported: '. $reviewCount . PHP_EOL;
		echo '- missed files: ' . $errors . PHP_EOL;
	}

	/**
	 * Import review
	 * 
	 * @param array $fullReviewData Unserialized array from .review file
	 * @return bool True on success
	 */
	private function _importReview($fullReviewData)
	{
		$reviewData = $votes = $sku = array();
		$reviewData = $fullReviewData['review'];

		$votes = $fullReviewData['votes'];
		$sku = $fullReviewData['sku'];
		$customerEmail = $fullReviewData['customer_email'];
		$productEntityId = $this->_getProductId($sku);

		$customerId = $this->_getCustomerIdByEmail($customerEmail, $reviewData['store_id']);

		$reviewData['entity_pk_value'] = $productEntityId;

	    // import review
	    try {
	    	// Save review
	    	$review = Mage::getModel('review/review')->setData($reviewData);

	    	$review->setEntityId($review->getEntityIdByCode(Mage_Review_Model_Review::ENTITY_PRODUCT_CODE))
                        ->setEntityPkValue($reviewData['entity_pk_value'])
                        ->setStatusId($reviewData['status_id'])
                        ->setCustomerId($customerId)
                        ->setStoreId($reviewData['store_id'])
                        ->setStores(array($reviewData['store_id']))
                        ->setCreatedAt($reviewData['created_at'])
                        ->save()
            ;

			$newReviewId = $review->getId();

			// Save the votes
			foreach ($votes as $ratingId => $optionId) {
				$vote = $this->_voteMapping($ratingId, $optionId);
				$ratingId = $vote['rating_id'];
				$optionId = $vote['option_id'];

				Mage::getModel('rating/rating')
					->setRatingId($ratingId)
					->setReviewId($newReviewId)
					->setCustomerId(NULL)
				;
				Mage::getModel('rating/rating_option')->setOptionId($optionId)
		            ->setRatingId($ratingId)
		            ->setReviewId($newReviewId)
		            ->setEntityPkValue($productEntityId)
		            ->addVote();
			}

			// Save the sizerating
            Mage::getModel('kega_review/sizerating')
                ->setReviewId($newReviewId)
                ->setOptionId($reviewData['option_id'])
                ->save();

            $review->aggregate();

			return true;
	    } catch (Exception $e) {
	    	if (Mage::getStoreConfig('kega_reviewexport/import/debug_mode')) {
	    		echo '<pre>'; print_r($reviewData) . PHP_EOL;
	    		echo 'Exception: ' . $e->getMessage() . PHP_EOL;
	    	}
	    	return false;
	    }
	}

	/**
	 * Get product ID by given SKU
	 * 
	 * @param string $sku SKU of product
	 * @return int Product ID
	 */
	private function _getProductId($sku)
	{
		$product = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);
		return $product->getEntityId();
	}

	/**
	 * Get content from file and unserialize it
	 * 
	 * @param string $file Full path of file
	 * @return array $fullReviewData Unserialized file content
	 */
	private function _getFileContent($filename)
	{
		$file = fopen($filename, 'r');
		$fileContent = fread($file, filesize($filename));

		$fullReviewData = unserialize($fileContent);

		return $fullReviewData;
	}

	/**
	 * Rating ids of exported shop can be different
	 * Set the rating ids to the new shops rating ids
	 * 
	 * @param int $ratingId Rating ID
	 * @param int $optionId Option ID
	 * @return array $vote
	 */
	private function _voteMapping($ratingId, $optionId)
	{
		$vote = array();
		$mapping = Mage::getStoreConfig('kega_reviewexport/import/rating_mapping');
		if (!empty($mapping)) {
			$mapping = explode(',', $mapping);
			$ratingIds = array();
			foreach ($mapping as $rating) {
				$rating = explode('=', $rating);
				$ratingIds[$rating[0]] = $rating[1];
			}

			if (isset($ratingIds[$ratingId])) {
				$newRatingId = $ratingIds[$ratingId];
				$difference = $newRatingId - $ratingId;
				$newOptionId = $optionId + ($difference * 5);

				$vote['rating_id'] = $newRatingId;
				$vote['option_id'] = $newOptionId;
			}
		}
		if (!isset($vote['rating_id'])) {
			$vote['rating_id'] = $ratingId;
			$vote['option_id'] = $optionId;
		}

		return $vote;
	}

	/**
	 * Get customer ID by email address
	 * 
	 * @param string $email
	 * @param int $storeId
	 * @return int Customer ID
	 */
	private function _getCustomerIdByEmail($email, $storeId)
	{
		if (!empty($email)) {
			$customer = Mage::getModel('customer/customer');
			$customer->setWebsiteId(self::WEBSITE_ID);
			$customer->loadByEmail($email);

			return $customer->getId();
		}
	}

}