<?php
$installer = $this;

$installer->startSetup();

$installer->run("

CREATE TABLE IF NOT EXISTS `{$installer->getTable('vacancy/candidate')}` (
  `vacancycandidate_id` int(10) unsigned NOT NULL auto_increment,
  `vacancy_id` int(10) unsigned NOT NULL,
  `hash` varchar(255) NOT NULL,
  `initials` varchar(255) default NULL,
  `first-name` varchar(255) default NULL,
  `last-name` varchar(255) default NULL,
  `gender` varchar(255) default NULL,
  `email` varchar(255) default NULL,
  `postcode` varchar(255) default NULL,
  `number` varchar(255) default NULL,
  `number-addition` varchar(255) default NULL,
  `street` varchar(255) default NULL,
  `city` varchar(255) default NULL,
  `country` varchar(255) default NULL,
  `motivation` text,
  `cv` varchar(255) default NULL,
  `ctime` timestamp NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`vacancycandidate_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



-- apply-for-function  text
ALTER TABLE `{$installer->getTable('vacancy/candidate')}`
	ADD COLUMN `apply-for-function` varchar(255) default NULL;

-- preferred-store-1  int
ALTER TABLE `{$installer->getTable('vacancy/candidate')}`
	ADD COLUMN `preferred-store-1` int(10) unsigned NOT NULL;

-- preferred-store-2  int
ALTER TABLE `{$installer->getTable('vacancy/candidate')}`
	ADD COLUMN `preferred-store-2` int(10) unsigned NOT NULL;

-- preferred-store-3  int
ALTER TABLE `{$installer->getTable('vacancy/candidate')}`
	ADD COLUMN `preferred-store-3` int(10) unsigned NOT NULL;

-- available-from   text
ALTER TABLE `{$installer->getTable('vacancy/candidate')}`
	ADD COLUMN `available-from` varchar(25) default NULL;

-- available-days   text
ALTER TABLE `{$installer->getTable('vacancy/candidate')}`
	ADD COLUMN `available-days` varchar(25) default NULL;

-- birth-date    text
ALTER TABLE `{$installer->getTable('vacancy/candidate')}`
	ADD COLUMN `birth-date` varchar(25) default NULL;

-- nationality    text
ALTER TABLE `{$installer->getTable('vacancy/candidate')}`
	ADD COLUMN `nationality` varchar(25) default NULL;

-- phone     text
ALTER TABLE `{$installer->getTable('vacancy/candidate')}`
	ADD COLUMN `phone` varchar(50) default NULL;

-- phone-mobile   text
ALTER TABLE `{$installer->getTable('vacancy/candidate')}`
	ADD COLUMN `phone-mobile` varchar(50) default NULL;

-- training-1    text
ALTER TABLE `{$installer->getTable('vacancy/candidate')}`
	ADD COLUMN `training-1` varchar(25) default NULL;

-- training-1-start  text
ALTER TABLE `{$installer->getTable('vacancy/candidate')}`
	ADD COLUMN `training-1-start` varchar(25) default NULL;

-- training-1-end   text
ALTER TABLE `{$installer->getTable('vacancy/candidate')}`
	ADD COLUMN `training-1-end` varchar(25) default NULL;

-- training-1-completed text
ALTER TABLE `{$installer->getTable('vacancy/candidate')}`
	ADD COLUMN `training-1-completed` varchar(25) default NULL;

-- training-2    text
ALTER TABLE `{$installer->getTable('vacancy/candidate')}`
	ADD COLUMN `training-2` varchar(25) default NULL;

-- training-2-start  text
ALTER TABLE `{$installer->getTable('vacancy/candidate')}`
	ADD COLUMN `training-2-start` varchar(25) default NULL;

-- training-2-end   text
ALTER TABLE `{$installer->getTable('vacancy/candidate')}`
	ADD COLUMN `training-2-end` varchar(25) default NULL;

-- training-2-completed text
ALTER TABLE `{$installer->getTable('vacancy/candidate')}`
	ADD COLUMN `training-2-completed` varchar(25) default NULL;

-- experience-1-company text
ALTER TABLE `{$installer->getTable('vacancy/candidate')}`
	ADD COLUMN `experience-1-company` varchar(255) default NULL;

-- experience-1-start  text
ALTER TABLE `{$installer->getTable('vacancy/candidate')}`
	ADD COLUMN `experience-1-start` varchar(255) default NULL;

-- experience-1-end  text
ALTER TABLE `{$installer->getTable('vacancy/candidate')}`
	ADD COLUMN `experience-1-end` varchar(255) default NULL;

-- experience-1-function text
ALTER TABLE `{$installer->getTable('vacancy/candidate')}`
	ADD COLUMN `experience-1-function` varchar(255) default NULL;

-- experience-2-company text
ALTER TABLE `{$installer->getTable('vacancy/candidate')}`
	ADD COLUMN `experience-2-company` varchar(255) default NULL;

-- experience-2-start  text
ALTER TABLE `{$installer->getTable('vacancy/candidate')}`
	ADD COLUMN `experience-2-start` varchar(255) default NULL;

-- experience-2-end  text
ALTER TABLE `{$installer->getTable('vacancy/candidate')}`
	ADD COLUMN `experience-2-end` varchar(255) default NULL;

-- experience-2-function text
ALTER TABLE `{$installer->getTable('vacancy/candidate')}`
	ADD COLUMN `experience-2-function` varchar(255) default NULL;

-- properties-strong  text
ALTER TABLE `{$installer->getTable('vacancy/candidate')}`
	ADD COLUMN `properties-strong` varchar(255) default NULL;

-- properties-weak   text
ALTER TABLE `{$installer->getTable('vacancy/candidate')}`
	ADD COLUMN `properties-weak` varchar(255) default NULL;

");

$installer->endSetup();