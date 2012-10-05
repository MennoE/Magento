<?php

$installer = $this;

$installer->startSetup();

$installer->run("

ALTER TABLE `{$this->getTable('vacancy/vacancy')}`
    CHANGE COLUMN `store_id` `shop_id` int(11) default NULL;

 -- DROP TABLE IF EXISTS `{$this->getTable('vacancy/vacancy_store_view')}`;
CREATE TABLE `{$this->getTable('vacancy/vacancy_store_view')}` (
	`vacancy_id` int(11) unsigned NOT NULL,
	`store_id` smallint(5) unsigned NOT NULL,
	`title` varchar(255) default NULL,
	`vacancytype_id` int(11) default NULL,
	`shop_id` int(11) default NULL,
	`vacancyregion_id` int(11) default NULL,
	`status` smallint(6) default NULL,
	`number` varchar(255) default NULL,
	PRIMARY KEY  (`vacancy_id`, `store_id`),
	CONSTRAINT `FK_VACANCY_VIEW_VACANCY_ID` FOREIGN KEY (`vacancy_id`) REFERENCES `{$installer->getTable('vacancy/vacancy')}` (`vacancy_id`) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT `FK_VACANCY_VIEW_STORE_ID` FOREIGN KEY (`store_id`) REFERENCES `{$installer->getTable('core_store')}` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE
)  ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->endSetup();