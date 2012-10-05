<?php
class Mage_Ogone_Model_Observer
{
	/**
	 * Observers: adminhtml_grid_prepare_massaction_block_after_sales_creditmemo_grid
	 * Adds mass export to Ogone option to the creditmemo grid.
	 *
	 * @param Varien_Event_Observer $observer
	 */
	public function addMassExportToOgoneAction($observer)
	{
		$block = $observer->getEvent()->getBlock();
        $block->setMassactionIdField('entity_id');
        $block->getMassactionBlock()->setFormFieldName('creditmemo_ids');

        $block->getMassactionBlock()->addItem('ogoneexportcreditmemos_order', array(
             'label'=> Mage::helper('sales')->__('Ogone Export Credit Memos'),
             'url'  => $block->getUrl('*/*/ogoneexportcreditmemos'),
        ));
	}

	/**
	 * Observers: sales_order_creditmemo_refund
	 * When refund is requested, and grand total > 0
	 * set creditmemo state to refund requested.
	 *
	 * @param Varien_Event_Observer $observer
	 */
	public function setStateRefundRequested($observer)
	{
		$creditmemo = $observer->getEvent()->getCreditmemo();
		if ($creditmemo->getGrandTotal() > 0) {
			$creditmemo->setState(Mage_Ogone_Model_Order_Creditmemo::STATE_REFUND_REQUESTED);
		}
	}
}