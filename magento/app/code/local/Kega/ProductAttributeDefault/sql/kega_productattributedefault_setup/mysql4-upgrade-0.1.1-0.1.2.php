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
ALTER TABLE {$this->getTable('kega_productattributedefault_attributes')} DROP COLUMN attribute_id;
ALTER TABLE {$this->getTable('kega_productattributedefault_attributes')} ADD COLUMN attribute_code varchar(255) AFTER productattributedefault_id;
");

$installer->endSetup();
