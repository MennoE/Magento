<?php
/**
 * @category   Kega
 * @package    Kega_Sysload
 */
class Kega_Sysload_Model_Sysload
{
	/**
	 * Module settings
	 * @var Array
	 */
	protected $_settings = array();

	/**
	 * Check system load and redirect to temp page if load is high
	 *
	 * @param $observer
	 * @return void
	 */
	public function check($observer)
	{
		$whitelist = array(
			'ogone_ogone_decline',
			'ogone_ogone_error',
			'ogone_ogone_failure',
			'ogone_ogone_process',
			'ogone_ogone_redirect',
			'ogone_ogone_success',
			'api_v2_soap_index',
		);
		$action = $observer->getControllerAction();
		if(!$action) {
			return;
		}
		if(in_array($action->getFullActionName(), $whitelist)) {
			return;
		}

		$load = Mage::helper('kega_sysload')->getLoad();
		$this->_settings = Mage::helper('kega_sysload')->getSettings();

		if($this->_checkRedirectNew($load) ||
			$this->_checkRedirectAll($load) ||
			$this->_checkRedirectNoncheckout($load) ) {

			$this->_redirect();
		}

		return;
	}

	/**
	 * Check if current visitor is new and if load is above redirect new
	 * treshold.
	 *
	 * @param double $load
	 * @return void
	 */
	protected function _checkRedirectNew($load)
	{
		$new = false;
		$visitorData = Mage::getSingleton('core/session')->getVisitorData();
		if(empty($visitorData['visitor_id'])) {
			$new = true;
		}

		if($new && $load >= $this->_settings['load_redirect_new']) {
			return true;
		}
		return false;
	}

	/**
	 * Check if current visitor is in checkout and load is above redirect
	 * noncheckout treshold
	 *
	 * @param double $load
	 * @return void
	 */
	protected function _checkRedirectNoncheckout($load)
	{
		$checkoutState = Mage::getSingleton('checkout/session')->getCheckoutState();
		$inCheckout = false;
		if(!empty($checkoutState)) {
			$inCheckout = true;
		}

		if(!$inCheckout && $load >= $this->_settings['load_redirect_noncheckout']) {
			return true;
		}
		return false;
	}

	/**
	 * Check if current load is above redirect all treshold.
	 *
	 * @param double $load
	 * @return void
	 */
	protected function _checkRedirectAll($load)
	{
		if($load >= $this->_settings['load_redirect_all']) {
			return true;
		}
		return false;
	}

	/**
	 * Redirect customer to temp page.
	 *
	 * @param void
	 * @return void
	 */
	protected function _redirect()
	{
		if(empty($this->_settings['redirect_url'])) {
			error_log('Kega_Sysload::check() Redirect failed! No redirect_url is set.');
			return;
		}
		header('location: ' . $this->_settings['redirect_url']);
		die();
	}
}