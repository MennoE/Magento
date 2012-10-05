<?php

/**
 * @category   Kega
 * @package    Kega_Contact
 */
class Kega_Contact_Block_Contact extends Mage_Core_Block_Template
{
    private $_required = array(
		'name',
		'e-mail',
		'address',
		'zipcode',
		'city',
	);

	private $_fieldValidators = array(
		'e-mail' => 'Zend_Validate_EmailAddress'
	);

    private $_validateErrors = array();


    public function __construct()
    {
        parent::__construct();

		if($this->getRequest()->isPost()) {
			$this->concatenateCustomerName();
		}
    }

    public function getRequiredFields()
    {
        return $this->_required;
    }

	/**
	 * Kega_Contact_Block_Contact->getValue()
	 * Returns a marked up required * for required fields
	 *
	 * @param $fieldname string
	 * @return string
	 */
    public function checkRequired($fieldname)
    {
        return (in_array($fieldname, $this->_required) ? '<span class="required">*</span>':'');
    }

	/**
	 * Kega_Contact_Block_Contact->getValue()
	 * Retrieving the $fielname value from the request data.
	 *
	 * @param $fieldname string
	 * @return string
	 */
    public function getValue($fieldname)
    {
        if($this->getRequest()->getParam($fieldname)) {
            return '';
        }
        return $this->getRequest()->getParam($fieldname);
    }

	/**
	 * Concatenate the customer name by binding the firstname, (middlename = optional) and lastname
	 * into the new 'customer-name' parameter in the postdata array.
	 */
	public function concatenateCustomerName()
	{
		if($this->getRequest()->getParam('name')) {
			$customerName = $this->getRequest()->getParam('name');
		} else {
			$customerName = $this->getRequest()->getParam('firstname');
			if($this->getRequest()->getParam('middlename')) {
				$customerName .= ' ' . $this->getRequest()->getParam('middlename');
			}
			$customerName .= ' ' . $this->getRequest()->getParam('lastname');
		}

		$this->getRequest()->setParam('customer-name', $customerName);
	}

	/**
	 * Kega_Contact_Block_Contact->getChecked()
	 * Returns whether a field needs to be checked by default
	 *
	 * @param $fieldname string
	 * @param $value string
	 * @return mixed (string, boolean)
	 */
    public function getChecked($fieldname, $value)
    {
        if($data = $this->getValue($fieldname)) {
            if(!is_array($data)) {
                return ($data == $value ? 'checked="checked"' : '');
            } else {
                return (in_array($value, $data) ? 'checked="checked"' : '' );
            }
        }
        return false;
    }

	/**
	 * Kega_Contact_Block_Contact->validateInput()
	 * Validates all inputs on required and value matching (email etc.)
	 *
	 * @param $formData array
	 * @return void
	 */
    public function validateInput($formData)
    {
        $this->_validateErrors = array('required' =>  array(), 'invalid' => array());

        foreach($this->getRequiredFields() as $_required) {
            if(empty($formData[$_required])) {
                $this->_validateErrors['required'][] = $_required;
            }
        }

        // check all given validates
        foreach($this->_fieldValidators as $field => $class) {
            $validator = new $class();
            if(!$validator->isValid($formData[$field]))
            $this->_validateErrors['invalid'][] = $field;

        }



        return (empty($this->_validateErrors['required']) && empty($this->_validateErrors['invalid']));
    }

	/**
	 * Kega_Contact_Block_Contact->getValidateErrors()
	 *
	 * @param void
	 * @return void
	 */
    public function getValidateErrors()
    {
        return $this->_validateErrors;
    }

	/**
	 * Kega_Contact_Block_Contact->sendMail()
	 * Sends the contactform to the site owner
	 *
	 * @param void
	 * @return void
	 */
	public function sendMail()
	{
		$variables = array();

		$toName = Mage::getStoreConfig('trans_email/ident_general/name');
		$toEmail = Mage::getStoreConfig('trans_email/ident_general/email');

		if($this->getRequest()->getParam('question-type') && $this->getRequest()->getParam('question-type') == 'webshop') {
			$storeName = Mage::getStoreConfig('trans_email/ident_sales/name');
			$storeEmail = Mage::getStoreConfig('trans_email/ident_sales/email');
		}

		// skip fields that doesn't need to be in the mail
		$skipFields = array('data', 'form_key');
		foreach($this->getRequest()->getParams() as $key => $value) {

			if (in_array($key, $skipFields)) {
				continue;
			}

			$variables[$key] = htmlspecialchars($value);
		}

		// display store title in email as store referral
		$variables['storename'] = Mage::getStoreConfig('design/head/default_title');

		// Get Store ID
		$store = Mage::app()->getStore()->getId();

		// Transactional Email Template's ID
		$templateId = Mage::getStoreConfig('customer/contact_mail/internal', $store);

		// Set sender information
		$recepientName = htmlspecialchars($this->getRequest()->getParam('customer-name'));
		$recepientEmail = htmlspecialchars($this->getRequest()->getParam('e-mail'));
		$sender = array('name' => $recepientName,
						'email' => $recepientEmail);

		$translate  = Mage::getSingleton('core/translate');
		$translate->setTranslateInline(true);

		try {

			// Send Transactional Email
			Mage::getModel('core/email_template')
				->setDesignConfig(array(
									   'area'  => 'frontend',
									   'store' => $store))
				->sendTransactional($templateId, $sender, $toEmail, $toName, $variables, $store);

		} catch (Exception $e) {
			Mage::log('Customer Contact Mail cannot be sent, please notice: ' . $e->getMessage());
		}

	}

    /**
     * Kega_Contact_Block_Contact->sendCustomerMail()
     *
     * Sending the contact recieved notification to the
     * customer.
     *
     * @param void
     * @return void
     */
    public function sendCustomerMail()
    {
        // Get Store ID
        $store = Mage::app()->getStore()->getId();

        // Transactional Email Template's ID
        $templateId = Mage::getStoreConfig('customer/contact_mail/email_template', $store);

        // Set sender information
        $senderName = Mage::getStoreConfig('trans_email/ident_general/name', $store);
        $senderEmail = Mage::getStoreConfig('trans_email/ident_general/email', $store);
        $sender = array('name' => $senderName,
                    'email' => $senderEmail);

        // Set recepient information
        $recepientName = htmlspecialchars($this->getRequest()->getParam('customer-name'));
        $recepientEmail = htmlspecialchars($this->getRequest()->getParam('e-mail'));

        $vars['customername'] = $recepientName;
        $vars['question'] = htmlspecialchars($this->getRequest()->getParam('comment'));

        $translate  = Mage::getSingleton('core/translate');
        $translate->setTranslateInline(true);

        try {

          // Send Transactional Email
            Mage::getModel('core/email_template')
            ->setDesignConfig(array(
              'area'  => 'frontend',
              'store' => $store))
            ->sendTransactional($templateId, $sender, $recepientEmail, $recepientName, $vars, $store);

        } catch (Exception $e) {
                error_log('Customer Contact Mail cannot be sent, please notice: ' . $e->getMessage());
        }
    }
}
