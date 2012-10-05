<?php
$installer = $this;

$installer->startSetup();

//$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
//$setup->addAttribute('shipment', 'exported_at', array('backend' => 'eav/entity_attribute_backend_datetime', 'type' => 'datetime'));
$installer->run("
-- SET FOREIGN_KEY_CHECKS=0;
-- DROP TABLE IF EXISTS `{$this->getTable('kega_tntdirect/export')}`;
-- SET FOREIGN_KEY_CHECKS=1;

ALTER TABLE `{$this->getTable('sales/shipment')}` ADD `exported_at` DATETIME NULL;

CREATE TABLE IF NOT EXISTS `{$this->getTable('kega_tntdirect/export')}` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `created_at` datetime default NULL,
  `updated_at` datetime default NULL,
  `filename` varchar(255) default NULL,
  `uploaded_at` datetime default NULL,
  `extra_info` varchar(255) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

");

$installer->endSetup();