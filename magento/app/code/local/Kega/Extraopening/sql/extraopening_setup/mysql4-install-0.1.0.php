<?php

$installer = $this;

$installer->startSetup();

$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('extraopening')};
CREATE TABLE {$this->getTable('extraopening')} (
  `extraopening_id` int(11) unsigned NOT NULL auto_increment,
  `store_id` int(10) unsigned NOT NULL,
  `title` varchar(255) NOT NULL default '',
  `datetime` datetime default NULL,
  PRIMARY KEY (`extraopening_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;




-- DROP TABLE IF EXISTS {$this->getTable('extraopening_store')};
CREATE TABLE {$this->getTable('extraopening_store')} (
  `extraopening_id` int(11) unsigned NOT NULL ,
  `store_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`extraopening_id`,`store_id`),
  CONSTRAINT `FK_EXTRAOPENNING_STORE_EXTRAOPENNING` FOREIGN KEY (`extraopening_id`) 
  		REFERENCES {$this->getTable('extraopening')} (`extraopening_id`) 
  		ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_EXTRAOPENING_STORE_STORE_ENTITY` FOREIGN KEY (`store_id`) REFERENCES `{$installer->getTable('store_entity')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE/*,
  CONSTRAINT `FK_EXTRAOPENNING_STORE_STORE` FOREIGN KEY (`store_id`) 
  		REFERENCES {$this->getTable('core_store')} (`store_id`) 
  		ON DELETE CASCADE ON UPDATE CASCADE*/
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


    ");

$installer->endSetup(); 