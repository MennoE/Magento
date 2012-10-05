<?php
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$installer->run("
DROP TABLE IF EXISTS {$this->getTable('kega_productattributedefault_attributes_dynamic')};
CREATE TABLE {$this->getTable('kega_productattributedefault_attributes_dynamic')} (
  `productattributedefault_attribute_dynamic_id` int(11) unsigned NOT NULL auto_increment,
  `productattributedefault_id` int(11) unsigned NOT NULL,
  `attribute_code` varchar(255) NOT NULL,
  `attribute_value` varchar(255),
  PRIMARY KEY (`productattributedefault_attribute_dynamic_id`),
  CONSTRAINT `FK_PRODUCT_ATTRIBUTE_DYNAMIC_DEFAULT` FOREIGN KEY (`productattributedefault_id`) REFERENCES {$this->getTable('kega_productattributedefault')} (`productattributedefault_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

$installer->endSetup();
