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
ALTER TABLE {$this->getTable('kega_productattributedefault')} CHANGE COLUMN attribute_pattern attribute_pattern_value varchar(255) default 'contains' AFTER attribute_name;
ALTER TABLE {$this->getTable('kega_productattributedefault')} ADD COLUMN attribute_pattern_code varchar(255) default 'contains' AFTER attribute_pattern_value;
");

$installer->endSetup();
