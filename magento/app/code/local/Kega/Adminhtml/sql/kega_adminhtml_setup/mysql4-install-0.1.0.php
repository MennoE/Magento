<?php

$installer = $this;
/* @var $installer Enterprise_GiftCardAccount_Model_Mysql4_Setup */
$installer->startSetup();

$installer->getConnection()->addColumn(
	$installer->getTable('core/website'),
    'host',
	"text not null"
);
$installer->endSetup();