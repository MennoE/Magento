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
ALTER TABLE {$this->getTable('kega_productattributedefault')} ADD COLUMN dry_run char(1) default '0';
ALTER TABLE {$this->getTable('kega_productattributedefault')} ADD COLUMN log_file varchar(255);
");

$installer->endSetup();
