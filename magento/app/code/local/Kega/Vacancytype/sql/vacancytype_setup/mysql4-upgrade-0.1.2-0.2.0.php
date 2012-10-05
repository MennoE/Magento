<?php

$installer = $this;

$installer->startSetup();

$installer->run("

 -- DROP TABLE IF EXISTS `{$this->getTable('vacancytype/vacancytype_store_view')}`;
CREATE TABLE `{$this->getTable('vacancytype/vacancytype_store_view')}` (
	`vacancytype_id` int(11) unsigned NOT NULL,
	`store_id` smallint(5) unsigned NOT NULL,
	`title` varchar(255) default NULL,
	`text` text default NULL,
	`status` smallint(6) default 0,
	`vacancy_form_type` varchar(255) default NULL,
	`meta_keywords` varchar(255),
	`meta_description` varchar(255),
	PRIMARY KEY  (`vacancytype_id`, `store_id`),
	CONSTRAINT `FK_VACANCYTYPE_VIEW_VACANCYTYPE_ID` FOREIGN KEY (`vacancytype_id`) REFERENCES `{$installer->getTable('vacancytype/vacancytype')}` (`vacancytype_id`) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT `FK_VACANCYTYPE_VIEW_STORE_ID` FOREIGN KEY (`store_id`) REFERENCES `{$installer->getTable('core_store')}` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE
)  ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->endSetup();