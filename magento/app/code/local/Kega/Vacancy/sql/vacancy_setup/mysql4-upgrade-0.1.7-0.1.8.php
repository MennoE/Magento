<?php

$installer = $this;
/* @var $installer Mage_Catalog_Model_Resource_Eav_Mysql4_Setup */

$installer->startSetup();

// was replaced from vacancytype module
$installer->run("
ALTER TABLE {$this->getTable('vacancy')} ADD COLUMN number varchar(255)
");

$installer->endSetup();