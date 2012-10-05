<?php
/**
 *
 * @category Kega
 * @package  Kega_LayeredNavSeo
 */
?>
<?php
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$installer->run("
DROP TABLE IF EXISTS {$this->getTable('kega_productattributedefault')};
CREATE TABLE {$this->getTable('kega_productattributedefault')} (
  `productattributedefault_id` int(11) unsigned NOT NULL auto_increment,
  `sku_pattern` varchar(255),
  `created_on` datetime,
  `updated_on` datetime,
  PRIMARY KEY (`productattributedefault_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS {$this->getTable('kega_productattributedefault_attributes')};
CREATE TABLE {$this->getTable('kega_productattributedefault_attributes')} (
  `productattributedefault_attribute_id` int(11) unsigned NOT NULL auto_increment,
  `productattributedefault_id` int(11) unsigned NOT NULL,
  `attribute_id` int(11) unsigned NOT NULL,
  `attribute_value` varchar(255),
  PRIMARY KEY (`productattributedefault_attribute_id`),
  CONSTRAINT `FK_PRODUCT_ATTRIBUTE_DEFAULT` FOREIGN KEY (`productattributedefault_id`) REFERENCES {$this->getTable('kega_productattributedefault')} (`productattributedefault_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

$installer->endSetup();
