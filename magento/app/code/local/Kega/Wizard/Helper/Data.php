<?php
/**
 * Kega_Wizard_Helper_Data
 * Wizard Helper
 */
class Kega_Wizard_Helper_Data extends Mage_Core_Helper_Abstract
{

    const XML_FREE_SHIPPING_NOTICE = 'wizardsettings/settings/free_shipping';

    /**
     * Kega_Wizard_Helper_Data::saveStoreCode()
     * Validate and save store code from url
     *
     * @return int
     */
	public function saveStoreCode()
	{
		if($storecode = Mage::app()->getRequest()->getParam('instorecode')) {

			if(Mage::app()->getStore()->getCode() != 'storeorders') {
				Mage::getSingleton('core/session')->addError('Storeview "' . Mage::app()->getStore()->getName() . '" ondersteunt geen winkelverkoop');
				return;
			}

			if(!Mage::app()->getStore()->getConfig('payment/instore/active')) {
				Mage::getSingleton('core/session')->addError('Winkelverkoop is uitgeschakeld.');
				return;
			}

			$store = Mage::getModel('store/store')->findByStoreCode($storecode);
			if(!$store->getIsActive()) {
				Mage::getSingleton('core/session')->addError('Ongeldige winkel gekozen');
				return;
			}

			Mage::getSingleton('core/session')->addSuccess($this->__('Winkelverkoop ingschakeld voor: %s', $store->getName()));
			return setcookie('instorecode', $storecode);
		}

		return;
	}

    /**
     * Kega_Wizard_Helper_Data::isInstoreorder()
     * Check if site is viewed from within a store
     *
     * @param void
     * @return Boolean
     */
    public function isInstoreorder()
    {
        $instoreEnabled = Mage::app()->getStore()->getConfig('payment/instore/active');
        if($instoreEnabled && isset($_COOKIE['instorecode']) && !empty($_COOKIE['instorecode'])) {
            return true;
        }
        return false;
    }

    /**
     * Kega_Wizard_Helper_Data::getInstorecode()
     * Get currently set instore code.
     *
     * @return String instorecode
     */
    public function getInstorecode()
    {
        if(isset($_COOKIE['instorecode'])) {
            return $_COOKIE['instorecode'];
        }
        return '';
    }

	public function getInstoreStore()
	{
		$store = Mage::getModel('store/store');
		if(!$this->isInstoreorder()) {
			return $store;
		}
		return $store->findByStorecode($this->getInstorecode());
	}
    /**
     * Kega_Wizard_Helper_Data::getInstoreBarcode()
     * Build barcode string from invoice. Format:
     * - Z: Fixed value
     * - A|C: Type of barcode. A for Accepting store orders, C for Collecting store pickups
     * - incrementId (10 chars) invoice increment id
     * - amount Amount which has to be paid when the barcode is scanned. (10 chars)
     *
     * @throws Exception on invalid $type
     * @param Mage_Sales_Model_Order_Invoice $invoice
     * @param Char $type A for store orders, C for store pickups
     * @return String Barcode
     */
    public function getInstoreBarcode($invoice, $type)
    {
        if(!in_array($type, array('A', 'C'))) {
            throw new Exception('Invalid $type. Expecting A or C');
        }

        $amount = 0;
        if($invoice->getOrder()->getGrandTotal() != $invoice->getOrder()->getTotalPaid()) {
            $amount = ($invoice->getGrandTotal()*100);
        }
        $barcode  = 'Z';
        $barcode .= $type;
        $barcode .= str_pad($invoice->getIncrementId(), 10, '0', STR_PAD_LEFT);
        $barcode .= str_pad($amount, 7, '0', STR_PAD_LEFT);
        return $barcode;
    }

    /**
     * Kega_Wizard_Helper_Data::getPrintInvoiceUrl()
     * Get url to print the instore invoice
     * @param unknown_type $order
     */
    public function getPrintInstoreInvoiceUrl($order)
    {
        $invoiceId = Mage::helper('wizard')->encrypt(
            $order->getInvoiceCollection()->getLastItem()->getId()
        );
        return Mage::getUrl('wizard/download/instoreinvoice', array('id' => $invoiceId));
    }

    public function formatTime($time) {

    	if(empty($time) || $time == '0000-00-00 00:00:00') {
    		return '--';
    	}
    	if(!is_numeric($time)) {
    		$time = strtotime($time);
    	}
    	return strftime('%e %b. %G %T', $time);
    }

    /**
     * Encrypt data (url safe)
     *
     * replace '/' in encrypted data to make result url safe
     *
     * @param String $data
     * @return String
     */
    public function encrypt($data)
    {
        $data = Mage::helper('core')->encrypt($data);
        return str_replace('/', '--', $data);
    }

    /**
     * Decrypt data which was encrypted with $this->encrypt()
     *
     * @param String $data
     * @return String
     */
    public function decrypt($data)
    {
        $data = str_replace('--', '/', $data);
        return Mage::helper('core')->decrypt($data);
    }

	/**
	 * The AJAX cart is being displayed on non-secure but tries to post to secure.
	 * Since that is cross-domain, its blocked. So we need to force these links to http://
	 * to be able to post.
	 *
	 * Since everything from the wizard and checkout modules are forced to https in the config.xml
	 * layer, we do not have any other choice then to make an exception this way.
	 *
	 * @param string $url
	 * @return string
	 */
	public function forceNonSecure($url)
	{
		$url = str_replace('https://', 'http://', $url);
		return $url;
	}

    /**
     * Free shipping notice in wizard
     */
    public function getFreeShippingNotice()
    {
        return Mage::getStoreConfig(self::XML_FREE_SHIPPING_NOTICE);
    }

    /**
     * Checking if the customer is in the Wizard and if it's logged in.
     *
     * @return true | false
     */
    public function isLoggedInWizard()
    {
        if(Mage::registry('isWizard') && Mage::getSingleton('customer/session')->isLoggedIn()) {
            return true;
        }
        return false;
    }
}