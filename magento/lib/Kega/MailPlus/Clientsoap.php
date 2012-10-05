<?php
/**
 *  SOAP client for MailPlus
 *  @author Anda Bardeanu
 */
class Kega_MailPlus_Clientsoap
{
    private $_apiId;
    private $_apiPassword;
    private $_apiUrl = "http://api.mailplus.nl/ApiService/soap/Contacts_v2?wsdl";
    private $_soapClient;


    /**
     * Setup api data
     *
     * @param string $apiId
     * @param string $apiPassword
     * @param boolean $debug make request/response headers available
     * @param string $apiUrl
     */
    public function __construct ($apiId, $apiPassword, $debug = false, $apiUrl = '')
    {
        if (!$apiId || !$apiPassword) {
            throw new Kega_MailPlus_Client_Exception(null, -2);
        }

        $this->_apiId = $apiId;
        $this->_apiPassword = $apiPassword;
        if ($apiUrl) {
            $this->_apiUrl = $apiUrl;
        }

        $this->_soapClient = new SoapClient("http://api.mailplus.nl/ApiService/soap/Contacts_v2?wsdl", array('trace' => $debug));
    }

    /**
     * for debuggin purposes
     * xml string with the soap request, only if $debug is true, otherwise returns empty string
     *
     * @return string
     */
    public function getLastRequest()
    {
        return $this->_soapClient->__getLastRequest();
    }


    /**
     * for debuggin purposes
     * xml string with the soap response, only if $debug is true, otherwise returns empty string
     *
     * @return string
     */
    public function getLastResponse()
    {
        return $this->_soapClient->__getLastResponse();
    }


    /**
     * Retrieve a list with fields that can be sent to MailPlus.
     * Example:
     * <code>
     *      $apiId				= '314100796';
     *      $apiPass			= 'NkcMByWtI5Mdt2pXG4WDmK';
     *
     *      $client = new Kega_MailPlus_Clientsoap($apiId, $apiPass);
     *      $availableProperties = $client->getAvailableProperties();
     * </code>
     *
     * @return array|boolean array(array('property_name'=>array('name' => '', 'description'=> '', 'type' => '')))
     */
    public function getAvailableProperties()
    {
        $params = new stdClass;
        $params->id = new SOAPVar($this->_apiId, XSD_STRING);
        $params->password = new SOAPVar($this->_apiPassword, XSD_STRING);

        $callResult = $this->_soapClient->getAvailableProperties($params);

        if (isset($callResult->return)) {
             $properties = array();
             foreach ($callResult->return as $property) {
                $properties[$property->name] = array(
                                                    'name' => $property->name,
                                                    'description' => $property->description,
                                                    'type' => $property->type
                                                    );
             }
             return $properties;
        }

        return false;
    }


    /**
     * Retrive contact data by property and value
     * Example:
     * <code>
     *      $apiId				= '314100796';
     *      $apiPass			= 'NkcMByWtI5Mdt2pXG4WDmK';
     *
     *      $client = new Kega_MailPlus_Clientsoap($apiId, $apiPass);
     *      $contacts = $client->findContactsByProperty($property = 'email', $value = 'sam.bruurs@itnova.nl');
     * </code>
     *
     * @param string $property
     * @param string $value
     */
    public function findContactsByProperty($property, $value)
    {

        $params = new stdClass;
        $params->id = new SOAPVar($this->_apiId, XSD_STRING);
        $params->password = new SOAPVar($this->_apiPassword, XSD_STRING);
        $params->property = new SOAPVar($property, XSD_STRING);
        $params->value = new SOAPVar($value, XSD_STRING);

        $callResult = $this->_soapClient->findContactsByProperty($params);
    }

    /**
     * Retrive contact data by email and postcode
     * Example:
     * <code>
     *      $apiId				= '314100796';
     *      $apiPass			= 'NkcMByWtI5Mdt2pXG4WDmK';
     *
     *      $client = new Kega_MailPlus_Clientsoap($apiId, $apiPass);
     *      $contact = $client->findContactbyEmailPostcode('sam.bruurs@itnova.nl', '2613SV');
     * </code>
     *
     * @param string $property
     * @param string $value
     * @return array | boolean
     */
    public function findContactbyEmailPostcode($email, $postcode) 
    {
        $params = new stdClass;
        $params->id = new SOAPVar($this->_apiId, XSD_STRING);
        $params->password = new SOAPVar($this->_apiPassword, XSD_STRING);
        $params->property = new SOAPVar('email', XSD_STRING);
        $params->value = new SOAPVar($email, XSD_STRING);

        $callResult = $this->_soapClient->findContactsByProperty($params);

        if (!isset($callResult->return)) {
            return false;
        }

        //make a list so we can use the same code for search
        if(!is_array($callResult->return)) {
            $result = $callResult->return;
            $callResult->return = array($result);
        }

        //we have a list
        $isFound = false;   
        $properties = array(); 
        foreach ($callResult->return as $result) {
            if ($isFound) break;      
                    
            foreach ($result->properties->entry as $property) {                
                $properties[$property->key] = isset($property->value)? $property->value : '';
               
                if ($property->key == 'postalCode' && $property->value == $postcode) {                    
                    $isFound = true;
                }
            }
        }

        if ($isFound) {
            return $properties;
        }

        return false;
    }

   
    /**
     * Retrive contact data by email and postcode
     * Example:
     * <code>
     *      $apiId				= '314100796';
     *      $apiPass			= 'NkcMByWtI5Mdt2pXG4WDmK';
     *
     *      $client = new Kega_MailPlus_Clientsoap($apiId, $apiPass);
     *      $contact = $client->findContactbyCardnumberPostcode('123456', '2613SV');
     * </code>
     *
     * @param string $property
     * @param string $value
     * @return array | boolean
     */
    public function findContactbyCardnumberPostcode($cardnumber, $postcode) 
    {
        $params = new stdClass;
        $params->id = new SOAPVar($this->_apiId, XSD_STRING);
        $params->password = new SOAPVar($this->_apiPassword, XSD_STRING);
        $params->property = new SOAPVar('profileField1', XSD_STRING);
        $params->value = new SOAPVar($cardnumber, XSD_STRING);

        $callResult = $this->_soapClient->findContactsByProperty($params);

        if (!isset($callResult->return)) {
            return false;
        }

        //make a list so we can use the same code for search
        if(!is_array($callResult->return)) {
            $result = $callResult->return;
            $callResult->return = array($result);
        }

        //we have a list
        $isFound = false;   
        $properties = array(); 
        foreach ($callResult->return as $result) {
            if ($isFound) break;      
                    
            foreach ($result->properties->entry as $property) {                
                $properties[$property->key] = isset($property->value)? $property->value : '';
               
                if ($property->key == 'postalCode' && $property->value == $postcode) {                    
                    $isFound = true;
                }
            }
        }

        if ($isFound) {
            return $properties;
        }

        return false;
    }
}
