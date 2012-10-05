<?php
/**
 * This is a client for MailPlus
 *
 */
class Kega_MailPlus_Client
{
    protected $_httpClient = null;

    /**
     * Setup client connection with MailPlus
     *
     * @param String $username
     * @param String $password
     * @param String $hostname
     */
	public function __construct($username = null, $password = null, $hostname = 'https://api.mailplus.nl')
    {
        if (!$username || !$password) {
            throw new Kega_MailPlus_Client_Exception(null, -2);
        }

        $requestData = array('MailPlusAPIid' => $username,
        					 'MailPlusAPIpassword' => $password
        					 );

        $this->_httpClient = new Kega_Http_Client($hostname . '/api/v21/service', $requestData);
    }

    /**
     * Retrieve table structure from external MailPlus API
     *
     * @return Array
     */
    public function getTableStructure()
	{
        // Retrieve the table structure.
        $xmlData = $this->run('showFields');
        $code = $this->getCode($xmlData);
        if ($code < 0 || !$xmlData) {
            throw new Kega_MailPlus_Client_Exception(null, $code);
        }

        $xmlData = $xmlData->properties;

        $allowedFields = array();
        foreach ($xmlData->property as $property) {
            // Read the attributes from a property item.
            $propertyAttributes = $property->attributes();
            $propertyInfo = array();

            if (!empty($propertyAttributes['name'])) {
                // Use name as the id of the field.
                $propertyId = (string)$propertyAttributes['name'];
            } else {
                // Make sure we have a default id.
                $propertyId = count($allowedFields);
            }

            if (!empty($propertyAttributes['description'])) {
                // Use description as the name of the field.
                $propertyInfo['name'] = (string)$propertyAttributes['description'];
            }

            if (!empty($propertyAttributes['propertyType'])) {
                $propertyType = (string)$propertyAttributes['propertyType'];

                // If it is a 'set', get all the list items.
                if ($propertyType == 'set') {
                    $items = array();
                    foreach ($property->item as $item) {
                        $itemAttributes = $item->attributes();
                        $items[(string)$itemAttributes['bitValue']] = (string)$itemAttributes['name'];
                    }
                    $propertyInfo['items'] = $items;

                    // If the 'set' is a 'list', then change the type to list.
                    if (substr($propertyId, 0, 4) == 'list' && strlen($propertyId) > 4) {
                        $propertyType = 'list';
                    }
                }
            } else {
                // Make sure we have a default propertyType.
                $propertyType = 'string';
            }

            $propertyInfo['type'] = $propertyType;

            // Add this property item to the list of fields.
            $allowedFields[$propertyId] = $propertyInfo;
        }

        // We don't use this one, so remove it from results.
        unset($allowedFields['extContactId']);

        return $allowedFields;
	}

    /**
     * Retrieve a list with fields that can be sent to MailPlus.
     *
     * @return Array (field=>description,...)
     */
    public function getFieldList()
	{
        $allowedFields = array();

        // Loop through all the allowed fields
        foreach ($this->getTableStructure() as $key=>$data) {
			if ($key == 'testGroup') {
				continue;
			}
            if (isset($data['items'])) {
                // if there are items, then combine the fieldname with the id of the item.
                foreach ($data['items'] as $item=>$value) {
                    $allowedFields[$key . '_' . $item] = $data['name'] . ' - ' . $value . ' (' . $this->getTypeString($data['type']) . ')';
                }
            } else {

                $allowedFields[$key] = $data['name'] . ' (' . $this->getTypeString($data['type']) . ')';
            }
        }

        return $allowedFields;
    }

    /**
     * Check if the given fields are accepted by MailPlus (if they exist).
     *
     * @param Array $userData
     * @param boolean $exeption
     * @return Array
     */
    public function checkFields($userData, $exeption = false)
	{
        // Retrieve all available fields.
        $allowedFields = $this->getFieldList();

        $notAllowedFields = array();

        // Loop trough the userdata and check if all key's exist.
        foreach ($userData as $key=>$value)
        {
        	if (!isset($allowedFields[$key])) {
                // Key does not exist, so add to not allowed list.
                $notAllowedFields[$key] = $value;
            }
        }

        if ($exeption && !empty($notAllowedFields)) {
        	$notAllowedFields = array_flip($notAllowedFields);
            throw new Kega_MailPlus_Client_Exception("You try to update fields that are not in MailPlus (anymore?).\n- " . implode("\n- ", $notAllowedFields), -100);
        }

        return $notAllowedFields;
    }

    /**
     * Retrieve user data from MailPlus database
     *
     * @param String $email
     * @param boolean  $exeption
     * @return Array
     *
     * @todo Woensdag
     */
    public function getUserData($email, $exeption = true)
	{
        // Retrieve the user data.
		$xmlData = $this->run('getSubscription', array('email' => $email));
        $code = $this->getCode($xmlData);
        if ($code < 0) {
        	if ($exeption) {
            	throw new Kega_MailPlus_Client_Exception(null, $code);
        	} else {
        		return null;
        	}
        }

		$subscription = get_object_vars($xmlData->subscription);
	    if($subscription) {
        	foreach($subscription as &$value) {
        		// Make sure that the values are Strings (Not SimpleXMLElement Object).
        		$value = (String)$value;
        	}
        }

        return $subscription;
    }


    /**
     * Check if a user exists in the mailplus db
     *
     * @param String $email
     * @return boolean
     *
     */
    public function userIsSubscribed($email)
	{
		$xmlData = $this->run('getSubscription', array('email' => $email));

        if (!$xmlData || $this->getCode($xmlData) == -6) {
			return false;
		}

		$subscription = get_object_vars($xmlData->subscription);
	    if ($subscription) {
			foreach($subscription as &$value) {
        		// Make sure that the values are Strings (Not SimpleXMLElement Object).
        		$value = (String)$value;
        	}

			if(isset($subscription['list1']) && $subscription['list1'] == 1) {
				return true;
			}
        }
		return false;
    }

    /**
     * Save a user into the external MailPlus database.
     *
     * @param Array $userData
     * @param boolean $temporary
     * @param boolean $updateWhenExists
     * @return int $code (userId)
     */
    public function saveUserData($userData, $temporary = false, $updateWhenExists = true)
	{
        // Validate the fields.
        $this->checkFields($userData, true);

        $command = 'insert' . ($temporary ? 'Temporary' : '');

        // Save user data.
		$xmlData = $this->run($command, $userData);
        $code = $this->getCode($xmlData);

        // -7 = Already exists
        if ($code == -7 && $updateWhenExists) {
            // Update user data.
            $code = $this->updateUserData($userData['email'], $userData);
        } else if ($code < 0) {
            throw new Kega_MailPlus_Client_Exception(null, $code);
        }

        return $code;
    }

    /**
     * Update user data in the external MailPlus database.
     *
     * @param String $email
     * @param Array $userData
     * @return int $code (userId)
     */
    public function updateUserData($email, $userData)
	{
        if (empty($email)) {
            throw new Kega_MailPlus_Client_Exception(null, -4);
        }
        // Validate the fields.
        $this->checkFields($userData, true);

        if (empty($userData['email'])) {
            // Make sure that email is added, so that MailPlus uses it as indentiefier.
            $userData['email'] = $email;
        } else if ($email != $userData['email']) {
        	// Indentifier is not the same as the email address that we want to set.
        	// Conclusion: We want to change the email address.

        	// Read original user data.
	        $orgData = $this->getUserData($email);

	        // Add the encId, so that MailPlus uses it as indentifier.
	        $userData['encId'] = $orgData['encId'];

            // Check if there already is a user with this email address.
            $emailCheckData = $this->getUserData($userData['email'], false);
        	if (!empty($emailCheckData)) {
	        	// User already exists in the MailPlus database (with same email address).
        		throw new Kega_MailPlus_Client_Exception('User already exists in the MailPlus database (with same email address): ' . $userData['email'] . ' .', -107);
        	}
        }

        // Update user data.
        $xmlData = $this->run('modify', $userData);

        $code = $this->getCode($xmlData);
        if ($code < 0) {
            throw new Kega_MailPlus_Client_Exception(null, $code);
        }

        return $code;
    }

    /**
     * Remove user from external MailPlus database (only disable, user will still exist in database).
     *
     * @param String $email
     * @param boolean $exeption
     */
    public function deactivateUser($email, $exeption = true)
	{
        // Remove the user.
		$xmlData = $this->run('remove', array('email' => $email));
        $code = $this->getCode($xmlData);
        // Ignore -9, Customer record is already de-activated.
        if ($code < 0 && $code != -9) {
            if ($exeption) {
            	throw new Kega_MailPlus_Client_Exception(null, $code);
            } else {
            	return false;
            }
        }

        return true;
    }

    /**
     * Add a user to a campaign (trigger a campaign).
     *
     * @param String $email
     * @param String $triggerId
     * @param boolean $ignoreAlreadyActive
     * @return boolean
     */
    public function triggerCampaign($email, $triggerId, $ignoreAlreadyActive = true)
	{
        // Trigger the campaign.
		$xmlData = $this->run('trigger', array('email' => $email,
                                               'triggerEncId' => $triggerId)
                              );
        $code = $this->getCode($xmlData);

        // If ignoreAlreadyActive then ignore -11, Customer is already active in campaign.
        if ($code < 0 && (!$ignoreAlreadyActive || $code != -11 )) {
            throw new Kega_MailPlus_Client_Exception(null, $code);
        }

        return true;
    }

    /**
     * Send command to MailPlus server
     *
     * @param String $command
     * @param Array $requestData
     * @return Object (SimpleXMLElement)
     */
    private function run($command, $requestData = array())
	{
        $requestData = array_merge(array('command' => $command), $requestData);

        // Excecute remote script.
		$response = $this->_httpClient->doPost($requestData);
		//$response = $this->_httpClient->doGet($requestData);

        // Convert the response (string with XML) to XML object
        $xmlData = simplexml_load_string($response);

        return $xmlData;
    }

    /**
     * Get code from XML data.
     *
     * @param String $xmlData
     * @return int
     */
    private function getCode($xmlData)
	{
		if (isset($xmlData->code)) {
            $code = (int)$xmlData->code;
        } else {
        	// No code found, so return 0 (status = ok)
        	$code = 0;
        }

        return $code;
    }

    /**
     * Get the string representation of a type
     *
     * @param String $type
     * @return String
     */
    private function getTypeString($type) {

        $typeStrings = array('set' => 'Y/N',
                             'list' => 'Y/N',
                             'gender' => 'Man/Vrouw');

        if(empty($typeStrings[$type])) {
            return $type;
        } else {
            return $typeStrings[$type];
        }
    }
}