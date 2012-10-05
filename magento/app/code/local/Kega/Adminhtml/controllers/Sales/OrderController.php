<?php
require_once('app/code/core/Mage/Adminhtml/controllers/Sales/OrderController.php');
class Kega_Adminhtml_Sales_OrderController extends Mage_Adminhtml_Sales_OrderController
{
    /**
     * Edit order address form
     *
     * KEGA:
     * This looks a bit like a bug.
     * This method is extended to load single address instead of
     * a complete collection.
     * Loading the complete collection is a problem when there are
     * a lot of addresses in the database, this gave a memory size
     * exhausted error.
     */
    public function addressAction()
    {
        $addressId = $this->getRequest()->getParam('address_id');

        // Load single item instead of collection
		$address = Mage::getModel('sales/order_address')->load($addressId);

        if ($address) {
            Mage::register('order_address', $address);
            $this->loadLayout();
            $this->renderLayout();
        } else {
            $this->_redirect('*/*/');
        }
    }

}
