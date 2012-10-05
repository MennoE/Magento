<?php
class Mage_Ogone_Block_Adminhtml_Sales_Order_Creditmemo_View extends Mage_Adminhtml_Block_Sales_Order_Creditmemo_View
{
	/**
	 * Extended: We want to set the refund url to the Ogone creditmemo export
	 * It looks like there is no default getRefundUrl available (results in empty url).
	 */

	/**
	 * Retreive refund url to Ogone creditmemo export.
	 */
    function getRefundUrl() {
    	return $this->getUrl('*/sales_creditmemo/ogoneexportcreditmemos', array(
			'creditmemo_ids' => $this->getCreditmemo()->getId()
			));
    }
}