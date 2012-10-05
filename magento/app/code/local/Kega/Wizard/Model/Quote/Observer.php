<?php
/**
 * Quote observer model
 *
 */
class Kega_Wizard_Model_Quote_Observer
{
	/**
	 * Observers: sales_quote_merge_before
	 *
	 * When a customer logs in, the old quote is merged with the new quote.
	 *
	 * We merge the quotes in a different way, than Magento does by default.
	 * The way we merge is as follows:
	 *  - Merge all items from the new quote into the old quote.
	 *  - If quote item already exists, we set qty to the qty of new quote item (instead of adding extra qty).
	 *
	 * We also set the "merged_from_quote_id" and "merged_with_quote_id" fields.
	 * In the checkout page we can use these fields to check if the items are "old/restored".
	 *
	 * <code>
	 * <?php
	 * $_restoredItem = ($_item->getQuote()->getMergedWithQuoteId() != $_item->getMergedFromQuoteId());
	 * ?>
	 * </code>
	 *
	 * @param $observer
	 *
	 * @return Kega_Wizard_Model_Quote_Observer
	 */
	public function mergeBefore($observer)
	{
		if (!Mage::getStoreConfigFlag('wizard_settings/smartcart/enabled')) {
				return $this;
		}

		// Original (old stored) quote.
		$quote = $observer->getQuote();

		// The new quote, just created by the user (before logging in).
		$newQuote = $observer->getSource();

		foreach ($newQuote->getAllVisibleItems() as $item) {
			$found = false;
			foreach ($quote->getAllItems() as $quoteItem) {
				// If the old quote has the same item as the new quote, set the qty to the same value.
				if ($quoteItem->compare($item)) {
					$quoteItem->setQty($item->getQty());
					$quoteItem->setMergedFromQuoteId($newQuote->getId());
					$found = true;
					break;
				}
			}

			if (!$found) {
				$newItem = clone $item;
				$newItem->setMergedFromQuoteId($newQuote->getId());
				$quote->addItem($newItem);
				if ($item->getHasChildren()) {
					foreach ($item->getChildren() as $child) {
						$newChild = clone $child;
						$newChild->setParentItem($newItem);
						$quote->addItem($newChild);
					}
				}
			}
		}

		/**
		 * During the quote merge, we have marked all updated (or created) items.
		 * As a last step we will check if there are items, that are not marked as updated.
		 * If so... we show a message about the fact that we restored (a part of) the items of the last visit.
		 */
		foreach ($quote->getAllVisibleItems() as $quoteItem) {
			if (!$quoteItem->getMergedFromQuoteId() && !$quoteItem->getParentItem()) {
				Mage::getSingleton('checkout/session')->addSuccess(
					Mage::helper('wizard')->__('For your convenience we automatically restored the items from your last visit (they are marked).')
				);

				// Because we have restored items, go back to the cart.
				Mage::getSingleton('customer/session')->setBeforeAuthUrl(Mage::getUrl('checkout/cart/index/'));
				break;
			}
		}

		$quote->setMergedWithQuoteId($newQuote->getId());

		/**
		 * Remove all items from the new quote (we already merged them into the old quote).
		 * Mage_Sales_Model_Quote::merge will therefore skip the merging items functionality.
		 * The rest of the merging, like coupon code, shipping, billing address etc. wil still be executed as usual.
		 */
		$newQuote->removeAllItems();

		return $this;
	}
}