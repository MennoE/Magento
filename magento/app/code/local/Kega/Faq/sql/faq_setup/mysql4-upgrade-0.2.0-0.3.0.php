<?php

$installer = $this;

$installer->startSetup();

$installer->run("

 DROP TABLE IF EXISTS `{$this->getTable('faq/question_store_view')}`;
CREATE TABLE `{$this->getTable('faq/question_store_view')}` (
	`question_id` int(11) unsigned NOT NULL,
	`store_id` smallint(5) unsigned NOT NULL,
	`question` varchar(255) default NULL,
	`answer` varchar(255) default NULL,
	`permalink` varchar(255) default NULL,
	`order` smallint(6) default NULL,
	PRIMARY KEY  (`question_id`, `store_id`),
	CONSTRAINT `FK_FAQ_VIEW_QUESTION_ID` FOREIGN KEY (`question_id`) REFERENCES `{$installer->getTable('faq/question')}` (`question_id`) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT `FK_FAQ_QUESTION_VIEW_STORE_ID` FOREIGN KEY (`store_id`) REFERENCES `{$installer->getTable('core_store')}` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE
)  ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->endSetup();