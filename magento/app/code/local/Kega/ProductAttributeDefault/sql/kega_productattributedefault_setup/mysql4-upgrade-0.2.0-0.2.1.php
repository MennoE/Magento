<?php
/**
 *
 * @category Kega
 * @package  Kega_ProductAttributeDefault
 */
?>
<?php
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$installer->run("
ALTER TABLE {$this->getTable('kega_productattributedefault')} ADD COLUMN overwrite_product_manual_changes char(1);

DROP TABLE IF EXISTS {$this->getTable('kega_productattributedefault_manual_product_changes')};
CREATE TABLE {$this->getTable('kega_productattributedefault_manual_product_changes')} (
  `product_id` int(11) unsigned NOT NULL,
  `changed_attributes` text,
  `updated_at` datetime,
  PRIMARY KEY (`product_id`),
  CONSTRAINT `FK_PRODUCT_MANUAL_PRODUCT_CHANGES` FOREIGN KEY (`product_id`) REFERENCES {$this->getTable('catalog_product_entity')} (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

$installer->endSetup();

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

$setup->addAttribute(
'catalog_product', 'updated_by_product_attribute_default_at', 
	array('backend' => 'eav/entity_attribute_backend_datetime', 
	'type' => 'datetime', 
	'required' => false, 
	'label' => 'Updated By ProductAttributeDefault Module At', 
	'input' => 'hidden')
);