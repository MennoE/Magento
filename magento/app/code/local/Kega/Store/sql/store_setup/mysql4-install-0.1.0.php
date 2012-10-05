<?php

$installer = $this;

$installer->startSetup();

$installer->run("
	
	-- DROP TABLE IF EXISTS {$this->getTable('store_opening')};
	CREATE TABLE {$this->getTable('store_opening')} (
	  `store_opening_id` int(11) unsigned NOT NULL auto_increment,
	  `store_id` int(11) unsigned NOT NULL,
	  `mondayopen1` varchar(25) default NULL,
	  `mondayclose1` varchar(25) default NULL,
	  `mondayopen2` varchar(25) default NULL,
	  `mondayclose2` varchar(25) default NULL,
	  `tuesdayopen1` varchar(25) default NULL,
	  `tuesdayclose1` varchar(25) default NULL,
	  `tuesdayopen2` varchar(25) default NULL,
	  `tuesdayclose2` varchar(25) default NULL,
	  `wednesdayopen1` varchar(25) default NULL,
	  `wednesdayclose1` varchar(25) default NULL,
	  `wednesdayopen2` varchar(25) default NULL,
	  `wednesdayclose2` varchar(25) default NULL,
	  `thursdayopen1` varchar(25) default NULL,
	  `thursdayclose1` varchar(25) default NULL,
	  `thursdayopen2` varchar(25) default NULL,
	  `thursdayclose2` varchar(25) default NULL,
	  `fridayopen1` varchar(25) default NULL,
	  `fridayclose1` varchar(25) default NULL,
	  `fridayopen2` varchar(25) default NULL,
	  `fridayclose2` varchar(25) default NULL,
	  `saturdayopen1` varchar(25) default NULL,
	  `saturdayclose1` varchar(25) default NULL,
	  `saturdayopen2` varchar(25) default NULL,
	  `saturdayclose2` varchar(25) default NULL,
	  `sundayopen1` varchar(25) default NULL,
	  `sundayclose1` varchar(25) default NULL,
	  `sundayopen2` varchar(25) default NULL,
	  `sundayclose2` varchar(25) default NULL,
	  PRIMARY KEY  (`store_opening_id`),
	  KEY `FK_STORE_OPENING_STORE_ID` (`store_id`),
	  CONSTRAINT `FK_STORE_OPENING_STORE_ENTITY` FOREIGN KEY (`store_id`) REFERENCES `{$installer->getTable('store_entity')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
	
	
	
	
	-- DROP TABLE IF EXISTS `{$installer->getTable('store_entity')}`;
	CREATE TABLE `{$installer->getTable('store_entity')}` (
		`entity_id` int(10) unsigned NOT NULL auto_increment,
		`entity_type_id` smallint(8) unsigned NOT NULL default '0',
		`attribute_set_id` smallint(5) unsigned NOT NULL default '0',
		`created_at` datetime NOT NULL default '0000-00-00 00:00:00',
		`updated_at` datetime NOT NULL default '0000-00-00 00:00:00',
		PRIMARY KEY  (`entity_id`),
		KEY `IDX_ENTITY_TYPE` (`entity_type_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='Store Entityies';

-- DROP TABLE IF EXISTS `{$installer->getTable('store_entity_datetime')}`;
CREATE TABLE `{$installer->getTable('store_entity_datetime')}` (
  `value_id` int(11) NOT NULL auto_increment,
  `entity_type_id` smallint(8) unsigned NOT NULL default '0',
  `attribute_id` smallint(5) unsigned NOT NULL default '0',
  `store_id` smallint(5) unsigned NOT NULL default '0',
  `entity_id` int(10) unsigned NOT NULL default '0',
  `value` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`value_id`),
  KEY `FK_STORE_DATETIME_ENTITY_TYPE` (`entity_type_id`),
  KEY `FK_STORE_DATETIME_ATTRIBUTE` (`attribute_id`),
  KEY `FK_STORE_DATETIME_ENTITY` (`entity_id`),
  CONSTRAINT `FK_STORE_DATETIME_ATTRIBUTE` FOREIGN KEY (`attribute_id`) REFERENCES `{$installer->getTable('eav_attribute')}` (`attribute_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_STORE_DATETIME_ENTITY` FOREIGN KEY (`entity_id`) REFERENCES `{$installer->getTable('store_entity')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_STORE_DATETIME_ENTITY_TYPE` FOREIGN KEY (`entity_type_id`) REFERENCES `{$installer->getTable('eav_entity_type')}` (`entity_type_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- DROP TABLE IF EXISTS `{$installer->getTable('store_entity_decimal')}`;
CREATE TABLE `{$installer->getTable('store_entity_decimal')}` (
  `value_id` int(11) NOT NULL auto_increment,
  `entity_type_id` smallint(8) unsigned NOT NULL default '0',
  `attribute_id` smallint(5) unsigned NOT NULL default '0',
  `store_id` smallint(5) unsigned NOT NULL default '0',
  `entity_id` int(10) unsigned NOT NULL default '0',
  `value` decimal(12,4) NOT NULL default '0.0000',
  PRIMARY KEY  (`value_id`),
  KEY `FK_STORE_DECIMAL_ENTITY_TYPE` (`entity_type_id`),
  KEY `FK_STORE_DECIMAL_ATTRIBUTE` (`attribute_id`),
  KEY `FK_STORE_DECIMAL_ENTITY` (`entity_id`),
  CONSTRAINT `FK_STORE_DECIMAL_ATTRIBUTE` FOREIGN KEY (`attribute_id`) REFERENCES `{$installer->getTable('eav_attribute')}` (`attribute_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_STORE_DECIMAL_ENTITY` FOREIGN KEY (`entity_id`) REFERENCES `{$installer->getTable('store_entity')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_STORE_DECIMAL_ENTITY_TYPE` FOREIGN KEY (`entity_type_id`) REFERENCES `{$installer->getTable('eav_entity_type')}` (`entity_type_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- DROP TABLE IF EXISTS `{$installer->getTable('store_entity_int')}`;
CREATE TABLE `{$installer->getTable('store_entity_int')}` (
  `value_id` int(11) NOT NULL auto_increment,
  `entity_type_id` smallint(8) unsigned NOT NULL default '0',
  `attribute_id` smallint(5) unsigned NOT NULL default '0',
  `store_id` smallint(5) unsigned NOT NULL default '0',
  `entity_id` int(10) unsigned NOT NULL default '0',
  `value` int(11) NOT NULL default '0',
  PRIMARY KEY  (`value_id`),
  KEY `FK_STORE_INT_ENTITY_TYPE` (`entity_type_id`),
  KEY `FK_STORE_INT_ATTRIBUTE` (`attribute_id`),
  KEY `FK_STORE_INT_ENTITY` (`entity_id`),
  CONSTRAINT `FK_STORE_INT_ATTRIBUTE` FOREIGN KEY (`attribute_id`) REFERENCES `{$installer->getTable('eav_attribute')}` (`attribute_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_STORE_INT_ENTITY` FOREIGN KEY (`entity_id`) REFERENCES `{$installer->getTable('store_entity')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_STORE_INT_ENTITY_TYPE` FOREIGN KEY (`entity_type_id`) REFERENCES `{$installer->getTable('eav_entity_type')}` (`entity_type_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- DROP TABLE IF EXISTS `{$installer->getTable('store_entity_text')}`;
CREATE TABLE `{$installer->getTable('store_entity_text')}` (
  `value_id` int(11) NOT NULL auto_increment,
  `entity_type_id` smallint(8) unsigned NOT NULL default '0',
  `attribute_id` smallint(5) unsigned NOT NULL default '0',
  `store_id` smallint(5) unsigned NOT NULL default '0',
  `entity_id` int(10) unsigned NOT NULL default '0',
  `value` text NOT NULL,
  PRIMARY KEY  (`value_id`),
  KEY `FK_STORE_TEXT_ENTITY_TYPE` (`entity_type_id`),
  KEY `FK_STORE_TEXT_ATTRIBUTE` (`attribute_id`),
  KEY `FK_STORE_TEXT_ENTITY` (`entity_id`),
  CONSTRAINT `FK_STORE_TEXT_ATTRIBUTE` FOREIGN KEY (`attribute_id`) REFERENCES `{$installer->getTable('eav_attribute')}` (`attribute_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_STORE_TEXT_ENTITY` FOREIGN KEY (`entity_id`) REFERENCES `{$installer->getTable('store_entity')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_STORE_TEXT_ENTITY_TYPE` FOREIGN KEY (`entity_type_id`) REFERENCES `{$installer->getTable('eav_entity_type')}` (`entity_type_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- DROP TABLE IF EXISTS `{$installer->getTable('store_entity_varchar')}`;
CREATE TABLE `{$installer->getTable('store_entity_varchar')}` (
  `value_id` int(11) NOT NULL auto_increment,
  `entity_type_id` smallint(8) unsigned NOT NULL default '0',
  `attribute_id` smallint(5) unsigned NOT NULL default '0',
  `store_id` smallint(5) unsigned NOT NULL default '0',
  `entity_id` int(10) unsigned NOT NULL default '0',
  `value` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`value_id`),
  KEY `FK_STORE_VARCHAR_ENTITY_TYPE` (`entity_type_id`),
  KEY `FK_STORE_VARCHAR_ATTRIBUTE` (`attribute_id`),
  KEY `FK_STORE_VARCHAR_ENTITY` (`entity_id`),
  CONSTRAINT `FK_STORE_VARCHAR_ATTRIBUTE` FOREIGN KEY (`attribute_id`) REFERENCES `{$installer->getTable('eav_attribute')}` (`attribute_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_STORE_VARCHAR_ENTITY` FOREIGN KEY (`entity_id`) REFERENCES `{$installer->getTable('store_entity')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_STORE_VARCHAR_ENTITY_TYPE` FOREIGN KEY (`entity_type_id`) REFERENCES `{$installer->getTable('eav_entity_type')}` (`entity_type_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
	
	
");
//!!! UNIQUE KEY
$installer->endSetup();

$installer->installEntities();