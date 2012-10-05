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
ALTER TABLE {$this->getTable('kega_productattributedefault')} DROP COLUMN sku_pattern;
ALTER TABLE {$this->getTable('kega_productattributedefault')} ADD COLUMN attribute_name varchar(255) AFTER productattributedefault_id;
ALTER TABLE {$this->getTable('kega_productattributedefault')} ADD COLUMN attribute_pattern varchar(255) AFTER attribute_name;

DROP TABLE IF EXISTS {$this->getTable('kega_productattributedefault_categories')};
CREATE TABLE {$this->getTable('kega_productattributedefault_categories')} (
  `productattributedefault_category_id` int(11) unsigned NOT NULL auto_increment,
  `productattributedefault_id` int(11) unsigned NOT NULL,
  `category_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`productattributedefault_category_id`),
  CONSTRAINT `FK_PRODUCT_ATTRIBUTE_DEFAULT_CATEGOTY` FOREIGN KEY (`productattributedefault_id`) REFERENCES {$this->getTable('kega_productattributedefault')} (`productattributedefault_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

$installer->endSetup();
