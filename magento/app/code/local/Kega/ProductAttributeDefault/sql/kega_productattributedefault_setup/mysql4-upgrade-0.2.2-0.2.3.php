<?php
/**
 *
 * @category Kega
 * @package  Kega_ProductAttributeDefault
 */
?>
<?php
$installer = new Mage_Eav_Model_Entity_Setup('core_setup');


$installer->startSetup();

$installer->getConnection()->addColumn($installer->getTable('catalog/eav_attribute'), "product_enricher", "VARCHAR(50) NULL DEFAULT 'static_and_dynamic'");

$installer->endSetup();
