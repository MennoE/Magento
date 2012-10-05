<?php

error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', 1);
setlocale(LC_TIME, 'nl_NL');
umask(0);

$compilerConfig = 'includes/config.php';
if (file_exists($compilerConfig)) {
    include($compilerConfig);
}

$mageFilename = dirname(__FILE__) . '/../app/Mage.php';

require_once $mageFilename;


Mage::setIsDeveloperMode(true);

Mage::app();
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
try {
	// check the log file /var/urapidflow/log/run-... .log
	Mage::getModel('kega_urapidflow/stock_observer')->runImport(null);
}
catch (Exception $e)
{
	echo $e;
}