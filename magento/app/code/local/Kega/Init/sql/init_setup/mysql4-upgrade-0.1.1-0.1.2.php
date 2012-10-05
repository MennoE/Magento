<?php
$installer = $this;

$installer->startSetup();

$installer->run("

-- DROP TABLE IF EXISTS `{$installer->getTable('core/directory_storage')}`;
CREATE TABLE IF NOT EXISTS `{$installer->getTable('core/directory_storage')}` (
  `directory_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `path` varchar(255) NOT NULL DEFAULT '',
  `upload_time` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `parent_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`directory_id`),
  UNIQUE KEY `IDX_DIRECTORY_PATH` (`name`, `path`),
  KEY `parent_id` (`parent_id`),
  CONSTRAINT `FK_DIRECTORY_PARENT_ID` FOREIGN KEY (`parent_id`)
  REFERENCES `{$installer->getTable('core/directory_storage')}` (`directory_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Directory storage';


");
$installer->endSetup();