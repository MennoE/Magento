<?php
/**
 * Checkout observer model
 *
 */
class Kega_Wizard_Model_Checkout_Observer
{
	/**
	 * Observers: load_customer_quote_before
	 *
	 * When a customer logs in, we reset the "merged_from_quote_id" and "merged_with_quote_id" fields.
	 * In this way the cart items are not marked as "old/restored" anymore.
	 *
	 *
	 * If the customer has currently another quote in his session than this one,
	 * the dispatch event "sales_quote_merge_before" will be triggered.
	 *
	 * On that moment our Kega_Wizard_Model_Quote_Observer::mergeBefore observer will be triggered
	 * and will set the "merged_from_quote_id" and "merged_with_quote_id" fields again.
	 *
	 * @return Kega_Wizard_Model_Checkout_Observer
	 */
	public function loadCustomerQuoteBefore()
	{
		if (!Mage::getStoreConfigFlag('wizard_settings/smartcart/enabled')) {
			return $this;
		}

		$customerQuote = Mage::getModel('sales/quote')
			->setStoreId(Mage::app()->getStore()->getId())
			->loadByCustomer(Mage::getSingleton('customer/session')->getCustomerId());

		if ($customerQuote->getId()) {
			foreach ($customerQuote->getAllItems() as $quoteItem) {
				$quoteItem->setMergedFromQuoteId(null);
			}

			$customerQuote->setMergedWithQuoteId(null)
				->save();
		}

		return $this;
	}
}