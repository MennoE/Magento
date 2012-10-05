<?php
class Kega_Touch_Model_Api extends Mage_Api_Model_Resource_Abstract
{
	/**
	 * Mailplus API user and pass.
	 */
	protected $apiId = '314100796';
	protected $apiPass = 'NkcMByWtI5Mdt2pXG4WDmK';
	protected $campaign = 'VzpFEiIA8f';

    /**
	 * Hello world test.
	 * You can use this to simply test the SOAP service.
	 * Service name: touch.hello
	 */
    public function hello($value)
    {
        return array('text' => 'Hello world',
        			 'date' => date('Ymd'),
        			 'your_value' => $value);
    }

    /**
	 * Newsletter registration
	 * You can use this to add a user to the Mailplus mailing system.
	 *
	 * @param array $data	(email, firstname, lastname, interest_women, interest_men, interest_children)
	 *
	 * Service name: touch.newsletter
	 */
    public function newsletter($data)
    {
		// Soap V2 gives object, so convert to array.
		if (is_object($data)) {
			$data = get_object_vars($data);
		}

    	$client = new Kega_MailPlus_Client($this->apiId, $this->apiPass);

		// Convert M/F to mailplus gender key's.
		switch ($data['gender']) {
			case 'M':
				$gender = 'Man';
				break;
			case 'F':
				$gender = 'Vrouw';
				break;
			default:
				$gender = '';
		}

		$mailplus = array(
			'email' => addslashes($data['email']),
			'firstName' => addslashes($data['firstname']),
			'lastName' => addslashes($data['lastname']),
			'gender' => $gender
		);

		// Default, opt-in for newsletter.
		$mailplus['list1_1'] = 'Y';

		// Interest - Women
		if (isset($data['interest_women']) && $data['interest_women'] == 'Y') {
			$mailplus['list2_1'] = 'Y';
		}

		// Interest - Men
		if (isset($data['interest_men']) && $data['interest_men'] == 'Y') {
			$mailplus['list2_2'] = 'Y';
		}

	    // Interest - Children
		if (isset($data['interest_children']) && $data['interest_children'] == 'Y') {
			$mailplus['list2_4'] = 'Y';
		}

		// Save (update) user data.
		$saveResponse = $client->saveUserData($mailplus);

		// Trigger welcome campaign?
		if (isset($data['trigger_campaign']) && $data['trigger_campaign'] == 'Y') {
	    	try {
				$response = $client->triggerCampaign($data['email'], $this->campaign);
			} catch(Kega_MailPlus_Client_Exception $e) {
				// Campaign not active, or already triggered for this account.
			}
		}

		return $saveResponse;
    }
}