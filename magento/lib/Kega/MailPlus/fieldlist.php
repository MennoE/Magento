<?php

/**
 * Script for testing the Kega_MailPlus_Client
 * 
 * @author Mike Weerdenburg
 *
 */

// ----------------------------------------------------------------------------------------------------
// To make this example work:
// Add the  path to the Kega Framework and Zend Framework to the include path.
// Run the example to see what kind of fields MailPlus accepts.
// Copy the example code and buildt it into the Newsletter subscribe pages.
// ----------------------------------------------------------------------------------------------------

// Declare the site and path
define('SITE_DIR', realpath('./../../../'));

set_include_path(
'.'
. PATH_SEPARATOR . SITE_DIR .'/library/'
. PATH_SEPARATOR . SITE_DIR .'/ZendFramework/1.0.0/'
. PATH_SEPARATOR . get_include_path()
);


require_once 'Zend/Loader.php';
Zend_Loader::registerAutoload();

// library/Kega/MailPlus/examples.php

// HTTP-API ID & wachtwoord
$apiId				= '31002086';
$apiPass			= 'uHNoL8xWrcxWhLqHTD_LC4';

/**
 * MailPlus connection.
 */
$client = new Kega_MailPlus_Client($apiId, $apiPass);

/**
 * Retrieve a list with fields that can be sent to MailPlus.
 */
echo '<br />== getFieldList() ==<br />';

// Show all fields with description.
$fields = $client->getFieldList();
echo '<table>';
foreach ($fields as $field=>$description) {
    echo '<tr>';
        echo '<td>' . $field . '</td>';
        echo '<td>' . $description . '</td>';
    echo '</tr>';
}
echo '</table>';

// Generate example source code for developer.
$array = 'array(';
foreach ($fields as $field=>$description) {
	$array .= "\n\t// {$description}";
	$array .= "\n\t'{$field}' => '',\n";
}
$array = substr($array, 0, -1);
$array .= "\n);\n\n";

?>
<pre>
// HTTP-API ID & wachtwoord
$apiId				= '<?=$apiId;?>';
$apiPass			= '<?=$apiPass;?>';

/**
 * MailPlus connection.
 */
$client = new Kega_MailPlus_Client($apiId, $apiPass);

/**
 * Save user into the external MailPlus database.
 */
$data = <?=$array;?>
$response = $client->saveUserData($data);

echo '&lt;pre&gt;' . $response . '&lt;/pre&gt;';
</pre>