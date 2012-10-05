<?php
$installer = $this;

$installer->run("

	-- DROP TABLE IF EXISTS {$this->getTable('store_routes')};
	CREATE TABLE {$this->getTable('store_routes')} (
	  `store_route_id` int(11) unsigned NOT NULL auto_increment,
	  `store_id` int(10) unsigned NOT NULL,
	  `mondayroute` varchar(25) default NULL,
	  `tuesdayroute` varchar(25) default NULL,
	  `wednesdayroute` varchar(25) default NULL,
	  `thursdayroute` varchar(25) default NULL,
	  `fridayroute` varchar(25) default NULL,
	  `saturdayroute` varchar(25) default NULL,
	  `sundayroute` varchar(25) default NULL,
	  PRIMARY KEY  (`store_route_id`),
	  KEY `FK_STORE_OPENING_STORE_ID` (`store_id`),
	  CONSTRAINT `FK_STORE_ROUTE_STORE_ENTITY` FOREIGN KEY (`store_id`) REFERENCES `{$installer->getTable('store_entity')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
  ");