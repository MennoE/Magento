<?php
##########################################################################################################
/* Legacy code:
   We now use: <Mage_Ogone before="Mage_Adminhtml">Mage_Ogone</Mage_Ogone>
   maybe it is better to create our own Ogone admin route?
*/
##########################################################################################################

require_once 'Mage/Adminhtml/controllers/Sales/CreditmemoController.php';
class Mage_Ogone_Sales_CreditmemoController extends Mage_Adminhtml_Sales_CreditmemoController
{
	protected $_test;
	protected $_ogoneAFUUrl;
	protected $_log_file;

	protected function _construct()
	{
		parent::_construct();

		$this->_test = Mage::getStoreConfig('payment/ogone/test');
		$this->_ogoneAFUUrl = 'https://secure.ogone.com/ncol/' . ($this->_test ? 'test' : 'prod') . '/AFU_agree.asp';
		$this->_log_file = 'ogoneafu.log';
		parent::_construct();
	}

	/**
	 * Export the selected creditmemo's to Ogone for refund.
	 */
	public function ogoneexportcreditmemosAction(){

		try {
		 	$creditmemosIds = $this->getRequest()->getParam('creditmemo_ids');
		 	if (!empty($creditmemosIds)) {
		 		if (!is_array($creditmemosIds)) {
		 			// If single creditmemo id.
		 			$creditmemosIds = array($creditmemosIds);
		 		}
				$creditmemos = Mage::getResourceModel('sales/order_creditmemo_collection')
					->addAttributeToSelect('*')
					->addAttributeToFilter('entity_id', array('in' => $creditmemosIds))
					->addAttributeToFilter('state', Mage_Ogone_Model_Order_Creditmemo::STATE_REFUND_REQUESTED)
					->load();

				if ($creditmemos->getSize() == 0){
					$this->_getSession()->addError(Mage::helper('sales')->__('No creditmemos selected for export to Ogone'));
					$this->_redirect('*/*/');
					return;
				}

				// build array with credits per store
				$creditsPerStore = array();
				foreach($creditmemos as $creditmemo) {

					$storeId = $creditmemo->getStoreId();
					if(!isset($creditsPerStore[$storeId])) {
						$creditsPerStore[$storeId] = array();
					}
					$creditsPerStore[$storeId][] = $creditmemo;
				}
				$exportCount = array();
				$totalCount = 0;
				foreach($creditsPerStore as $store => $storeCreditmemos) {
					$exportCount[$store] = $this->_exportCreditmemosPerStore($store, $storeCreditmemos);
					$totalCount += $exportCount[$store];
				}
		 	}

			$storeDetails = '<br />';
			foreach($exportCount as $store => $qty) {
				$storeDetails .= '- ' . Mage::getModel('core/store')->load($store)->getName() . ' => ' . $qty . ' Credits.<br />';
			}

			$this->_getSession()->addSuccess(Mage::helper('sales')->__(
				'Sent %s creditmemos to Ogone:%s',
				count($creditmemos),
				$storeDetails
				));
			$this->_redirect('*/*/');
	 	}
		catch (Mage_Core_Exception $e) {
			$this->_getSession()->addError($e->getMessage());
		}
		catch (Exception $e) {
			$this->_getSession()->addError($this->__('Can\'t send creditmemos to Ogone'));
		}
		$this->_redirect('*/*/');
	}

	/**
	 * Send creditmemo's to ogone. (per store)
	 *
	 * Since we use a different Ogone account per store, we cannont send all
	 * credit's at once.
	 * This methods sends an array of $creditmemos for a given $storeId.
	 *
	 * @param unknown_type $storeId
	 * @param unknown_type $creditmemos
	 */
	protected function _exportCreditmemosPerStore($storeId, $creditmemos)
	{
		if(!is_numeric($storeId) || !is_array($creditmemos) || !count($creditmemos)) {
			return false;
		}

		$credentials = $this->_getOgoneCredentials($storeId);

		$filename = "Refund" . time();
		$content = "OHL;" . $credentials['PSPID'] . ";" . $credentials['PSWD'] . ";;" . $credentials['USERID'] . ";\r\n";
		$content .= "OHF;" . $filename . ";MTR;RFD;" . count($creditmemos) . ";\r\n";
		foreach ($creditmemos as $creditmemo){
			$payment = $creditmemo->getOrder()->getPayment();
			$content .= round($creditmemo->getGrandTotal() * 100) . ";" .
				$creditmemo->getOrderCurrencyCode() .
				";;;;;;;" . $payment->getLastTransId() . ";RFD;\r\n";
		}
		$content .= "OTF;";
		$client = new Zend_Http_Client($this->_ogoneAFUUrl);
		$client->setMethod(Zend_Http_Client::POST);
		$client->setParameterPost(array(
			'FILE'  => $content,
			'REPLY_TYPE' => 'XML',
			'MODE' => 'SYNC',
			'PROCESS_MODE' => 'SEND'
			));

		$response = $client->request();
		$this->_log($content);
		$this->_log($response->getBody());

		$fileId = (string) $this->_retreiveFileId($response);

		$client->resetParameters();
		$client->setParameterPost(array(
			'PSPID' => $this->_pspid,
			'USERID' => $this->_userid,
			'PSWD' => $this->_pswd,
			'REPLY_TYPE' => 'XML',
			'MODE' => 'SYNC',
			'PFID' => $fileId,
			'PROCESS_MODE' => 'CHECK',
			));

		$response = $client->request();
		$this->_log($client->getLastRequest());
		$this->_log($client->getLastResponse());

		$client->resetParameters();
		$client->setParameterPost(array(
			'PSPID' => $this->_pspid,
			'USERID' => $this->_userid,
			'PSWD' => $this->_pswd,
			'REPLY_TYPE' => 'XML',
			'MODE' => 'SYNC',
			'PFID' => $fileId,
			'PROCESS_MODE' => 'PROCESS',
			));

		$response = $client->request();
		$this->_log($client->getLastRequest());
		$this->_log($client->getLastResponse());

		foreach ($creditmemos as $creditmemo){
			$creditmemo->setState(Mage_Sales_Model_Order_Creditmemo::STATE_REFUNDED);
			$creditmemo->save();
		}

		return count($creditmemos);
	}

	/**
	 * Get Ogone credentials for specified store from config
	 *
	 * Since different Ogone accounts are used per store, different Ogone
	 * credentials need to be used per creditmemo refund.
	 * @param mixed $store
	 * @return array
	 */
	protected function _getOgoneCredentials($store)
	{
		$keys = array('PSPID', 'PSWD', 'USERID', 'test');
		$credentials = array();
		foreach($keys as $key) {
			$credentials[$key] = Mage::getStoreConfig('payment/ogone/' . $key, $store);
		}
		return $credentials;
	}

	protected function _retreiveFileId($response)
	{
		$xml = @simplexml_load_string($response->getBody());
		if (!$xml) {
			Mage::throwException($this->__('Invalid response from ogone AFU server'));
		}

		if (isset($xml->PARAMS_ERROR)) {
			Mage::throwException($this->__('File is not accepted by ogone AFU server') .
				' (' . $xml->NCERROR . ': ' . $xml->PARAMS_ERROR . ')');
		}

		if (!isset($xml->SEND_FILE->FILEID)) {
			Mage::throwException($this->__('File is not accepted by ogone AFU server'));
		}

		return $xml->SEND_FILE->FILEID;
	}

	/**
	 * Log method
	 *
	 * @param string $message
	 * @param string $level
	 */
	protected function _log($message, $level = Zend_Log::INFO)
	{
		Mage::log($message, $level, $this->_log_file);
	}
}