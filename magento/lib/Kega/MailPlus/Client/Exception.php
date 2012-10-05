<?php

class Kega_MailPlus_Client_Exception extends Kega_Exception
{
    public function __construct($message, $code, $priority = Zend_Log::WARN)
    {
    	if (is_null($message)) {
            // No message set, so find the error by code.
            switch ($code) {
                // Basic user errors
                case -1:
                    $message = 'Your server (IP: ' . $_SERVER['SERVER_ADDR'] . ') is not authorized, contact MailPlus.';
                    break;
                case -2:
                    $message = 'Your MailPlusAPIid and/or MailPlusAPIpassword are not correct, make sure that they match the ones in the MailPlus config.';
                    break;
                case -3:
                    $message = 'Unknown command, check MailPlus API for available commands.';
                    break;
                case -4:
                    $message = 'Provided email address is invalid.';
                    break;
                case -5:
                    $message = 'Provided mobileNumber is invalid.';
                    break;
                case -6:
                    $message = 'Can\'t find customer in MailPlus, please check if the "identifier" (email or mobileNumber) is correct?';
                    break;
                case -7:
                    $message = 'Customer is already in MailPlus database, please set updateWhenExists = true or use updateUserData.';
                    break;
                case -8:
                    $message = 'Parameter email not in request. Please add it.';
                    break;
                case -9:
                    $message = 'Customer record is already de-activated.';
                    break;

                // Campaign trigger errors
                case -10:
                    $message = 'Trigger is unknown (please check trigger id MailPlus).';
                    break;
                case -11:
                    $message = 'Customer is already active in campaign.';
                    break;
                case -12:
                    $message = 'Campaign is not active (this trigger is ignored).';
                    break;

                case -8888:
                    $message = 'MailPlus database is not available, please contact the MailPlus helpdesk.';
                    break;
                case -9999:
                    $message = 'Unknown error: -9999, please contact the MailPlus helpdesk.';
                	break;

                default:
                    $message = 'Unknown error: ' . $code;
            }
        }

        parent::__construct($message, $code, $priority);
    }
}