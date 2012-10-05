<?php

$installer = $this;
/* @var $installer Mage_Catalog_Model_Resource_Eav_Mysql4_Setup */

$installer->startSetup();

$installer->getConnection()->addColumn(
    $installer->getTable('vacancy/vacancy'),
    'hours',
    'VARCHAR(255) DEFAULT NULL'
);

$installer->endSetup();