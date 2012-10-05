<?php
/**
 * Webservices server handler v2
 *
 * Extended because we want to be able to track what kind of error there was thrown (instead of a 'Unknown error' message).
 * The 'real' solution will be "Adding the faultName's to the resource it's config file."
 * But with this "path" we are able to see the errors instead of 'Unknown error' for already made api's.
 *
 */
class Kega_Soap_Model_Server_V2_Handler extends Mage_Api_Model_Server_V2_Handler
{
	/**
	 * Dispatch webservice fault
	 * Extended because we want to be able to track what kind of error there was thrown (instead of a 'Unknown error' message).
	 *
	 * @param string $faultName
	 * @param string $resourceName
	 * @param string $customMessage
	 * @param string $notFoundFaultName
	 */
	protected function _fault($faultName, $resourceName=null, $customMessage='', $notFoundFaultName='')
	{
		$faults = $this->_getConfig()->getFaults($resourceName);
		if (!isset($faults[$faultName]) && !is_null($resourceName)) {
			$this->_fault($faultName, null, $customMessage, $faultName);
			return;
		} elseif (!isset($faults[$faultName])) {
			$this->_fault('unknown', null, trim($customMessage . PHP_EOL .
				($notFoundFaultName ? '(Original faultName was not found in api config: ' . $notFoundFaultName . ')' : ''))
			);
			return;
		}
		$this->_getServer()->getAdapter()->fault(
			$faults[$faultName]['code'],
			(empty($customMessage) ? $faults[$faultName]['message'] : $customMessage)
		);
	}
}