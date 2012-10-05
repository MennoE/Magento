<?php

class Kega_ReviewExport_Model_Export extends Mage_Core_Model_Abstract
{
	const EXPORT_DIRECTORY = 'reviewexport';

	/**
	 * Export reviews to file
	 * 
	 * @param void
	 * @return void
	 */
	public function export()
	{
		$startDate = date('Y-m-d H:i:s');

		$exportDirectory = self::EXPORT_DIRECTORY;

		$count = 0;
		foreach ($this->_getReviews() as $review)
		{
			$reviewId = $this->_exportReview($review);
			$count++;
		}

		echo 'start: '. $startDate . PHP_EOL;
		echo 'end: '. date('Y-m-d H:i:s') . PHP_EOL;
		echo 'exported reviews: ' . $count . PHP_EOL;
	}

	/**
	 * Export single review to file <id>.review
	 * 
	 * @param array $review
	 * @return string Review ID
	 */
	private function _exportReview($review)
	{
		$fullReviewData = array();
		$reviewData = $votes = array();

		$reviewDataRaw = $review->getData();
		$reviewData = $this->_reviewMapping($reviewDataRaw);

		$votesRaw = $review->getRatingVotes()->getData();
		$votes = $this->_voteMapping($votesRaw);

		$fullReviewData['review'] = $reviewData;
		$fullReviewData['votes'] = $votes;
		$fullReviewData['sku'] = $review->getSku();
		$fullReviewData['customer_email'] = $this->_getEmailByCustomerId($review->getCustomerId());

		$directory = Mage::getBaseDir('var') . DS . self::EXPORT_DIRECTORY;
		if (!file_exists($directory)) {
			mkdir($directory, 0777, true);
		}
		$filename = $directory . '/' . $reviewDataRaw['review_id'] . '.review';
		$file = fopen($filename, 'a+');

		fwrite($file, serialize($fullReviewData));

		return $reviewDataRaw['review_id'];
	}

	/**
	 * Create array of review fields that needs to be exported
	 * 
	 * @param array $data
	 * @return array $reviewData
	 */
	private function _reviewMapping($data)
	{
		$fields = array('created_at', 'status_id', 'detail_id', 'store_id', 'title', 'detail', 'nickname', 'option_id');
		$reviewData = array();
		foreach ($fields as $field) {
			$reviewData[$field] = isset($data[$field]) ? $data[$field] : '';
		}

		return $reviewData;
	}

	/**
	 * Create array of all votes with rating ID as key and option ID as value
	 * 
	 * @param array $data Array with data of all votes
	 * @return array $votes
	 */
	private function _voteMapping($data)
	{
		$votes = array();
		foreach ($data as $vote) {
			$votes[$vote['rating_id']] = $vote['option_id'];
		}
		return $votes;
	}

	/**
	 * Get review collection
	 * 
	 * @return Mage_Review_Model_Mysql4_Review_Collection
	 */
	private function _getReviews()
	{
		$collection = Mage::getModel('review/review')->getCollection();

		$collection->getSelect()
			->joinLeft(array('s' => 'kega_review_sizerating'),
				'main_table.review_id = s.review_id',
				array('option_id'));

		foreach ($collection as $c) {
			$id = $c->getEntityPkValue();
			$product = Mage::getModel('catalog/product')->load($id);
			$c->setSku($product->getSku());
		}
		$collection->addRateVotes();
		return $collection;
	}

	/**
	 * Get email by customer ID
	 * 
	 * @param int $id Customer ID
	 * @return string Customer email
	 */
	private function _getEmailByCustomerId($id)
	{
		if (!empty($id)) {
			$customer = Mage::getModel('customer/customer')->load($id);
			return $customer->getEmail();
		}
	}

}