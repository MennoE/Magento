<?php

/**
 * Script for testing the Kega_MailPlus_Client
 *
 * @author Mike Weerdenburg
 *
 */

// Declare the site and path

define('SITE_DIR', realpath('./../../../'));

set_include_path(
'.'
. PATH_SEPARATOR . SITE_DIR .'/Zend/library'
. PATH_SEPARATOR . SITE_DIR .'/library/'
. PATH_SEPARATOR . get_include_path()
);


require_once 'Zend/Loader.php';
Zend_Loader::registerAutoload();

// library/Kega/MailPlus/examples.php


// HTTP-API ID			31002280
// HTTP-API wachtwoord	rHNkYwG_DVd7llwBF16Y
$client = new Kega_MailPlus_Client('31002280', 'rHNkYwG_DVd7llwBF16Y');

/**
 * Retrieve a list with fields that can be sent to MailPlus.
 */
echo '<br />== getFieldList() ==<br />';

$fields = $client->getFieldList();
echo '<table>';
foreach ($fields as $field=>$description) {
    echo '<tr>';
        echo '<td>' . $field . '</td>';
        echo '<td>' . $description . '</td>';

    echo '</tr>';
}
echo '</table>';

/**
 * Retrieve user data from MailPlus database
 */

echo '<br />== getUserData() ==<br />';

$response = $client->getUserData('rick.bakker@kega.nl');
echo '<pre>';
echo htmlentities(print_r($response, true));
echo '</pre>';

/**
 * Save a user into the external MailPlus database.
 */
echo '<br />== saveUserData() ==<br />';

$response = $client->saveUserData(array('email' => 'Mike.Weerdenburg@kega.nl',
                                        'firstName' => 'Mike',
                                        'list1_1' => 'Y'
                                        )
                                  );
echo '<pre>' . $response . '</pre>';



/**
 * Update user data in the external MailPlus database.
 */
echo '<br />== updateUserData() ==<br />';

$response = $client->updateUserData('mike.weerdenburg@kega.nl'
                                    , array('email' => 'mike.weerdenburg@kega.nl', // Not needed, but no problem to send.
                                            'firstName' => 'Mike'
                                            )
                                    );
echo '<pre>' . $response . '</pre>';

/**
 * Remove user from external MailPlus database (only disable, user will still exist in database).
 */
echo '<br />== deactivateUser() ==<br />';

$response = $client->deactivateUser('mike.weerdenburg@kega.nl');
echo '<pre>' . $response . '</pre>';


/**
 * Add a user to a campaign (trigger a campaign).
 */
echo '<br />== triggerCampaign() ==<br />';

$triggerId = '?';
$email = 'mike.weerdenburg@kega.nl';
$response = $client->triggerCampaign($email, $triggerId);
echo '<pre>' . $response . '</pre>';