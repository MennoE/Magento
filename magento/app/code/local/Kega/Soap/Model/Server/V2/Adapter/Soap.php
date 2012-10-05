<?php
/**
 * Some modifications for the default Magento soap adapter.
 * 
 * Todo: Maybe extend the way of building up the wsdl file (only export the allowed methods).
 * @see: Mage_Api_Model_Wsdl_Config::init()
 *       The line with code: Mage::getConfig()->loadModulesConfiguration('wsdl.xml', $this, $mergeWsdl);
 *
 * @author     Mike Weerdenburg <mike.weerdenburg@kega.nl>
 */
class Kega_Soap_Model_Server_V2_Adapter_Soap extends Mage_Api_Model_Server_V2_Adapter_Soap
{
	/**
     * Try to instantiate Zend_Soap_Server
     * If schema import error is caught, it will retry in 1 second.
     *
     * @throws Zend_Soap_Server_Exception
     */
    protected function _instantiateServer()
    {
    	// Check if caching of the WSDL file is disabled.
    	if (!Mage::getStoreConfig('api/config/cache')) {
    		// Because disabling of internal caching of the WSDL file is bad for performance, log it.
			Mage::log('Kega_Soap_Model_Server_V2_Adapter_Soap: WSDL cache is DISABLED, please enable caching in Config -> Services -> Mageto Core API');
			// And disable caching.
        	ini_set('soap.wsdl_cache_enabled', '0');
    	}
    	$apiConfigCharset = Mage::getStoreConfig('api/config/charset');
        $tries = 0;
        do {
            $retry = false;
            try {
                $this->_soap = new Zend_Soap_Server($this->getWsdlUrl(array("wsdl" => 1)), array('encoding' => $apiConfigCharset));
            } catch (SoapFault $e) {
                if (false !== strpos($e->getMessage(), "can't import schema from 'http://schemas.xmlsoap.org/soap/encoding/'")) {
                    $retry = true;
                    sleep(1);
                } else {
                    throw $e;
                }
                $tries++;
	    }
        } while ($retry && $tries < 5);
        use_soap_error_handler(false);
        $this->_soap
            ->setReturnResponse(true)
            ->setClass($this->getHandler());
    }
}
