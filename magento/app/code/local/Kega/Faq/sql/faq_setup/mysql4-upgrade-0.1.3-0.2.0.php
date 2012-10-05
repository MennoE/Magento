<?php

$installer = $this;

$installer->startSetup();

$installer->run("

 DROP TABLE IF EXISTS `{$this->getTable('faq/category_store_view')}`;
CREATE TABLE `{$this->getTable('faq/category_store_view')}` (
	`category_id` int(11) unsigned NOT NULL,
	`store_id` smallint(5) unsigned NOT NULL,
	`permalink` varchar(255) default NULL,
	`name` varchar(255) default NULL,
	`order` smallint(6) default NULL,
	PRIMARY KEY  (`category_id`, `store_id`),
	CONSTRAINT `FK_FAQ_VIEW_CATEGORY_ID` FOREIGN KEY (`category_id`) REFERENCES `{$installer->getTable('faq/category')}` (`category_id`) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT `FK_FAQ_VIEW_STORE_ID` FOREIGN KEY (`store_id`) REFERENCES `{$installer->getTable('core_store')}` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE
)  ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->endSetup();