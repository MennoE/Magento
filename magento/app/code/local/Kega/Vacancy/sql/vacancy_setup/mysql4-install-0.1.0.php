<?php

$installer = $this;

$installer->startSetup();

$installer->run("
-- DROP TABLE IF EXISTS {$this->getTable('vacancy')};
CREATE TABLE {$this->getTable('vacancy')} (
  `vacancy_id` int(11) unsigned NOT NULL auto_increment,
  `title` varchar(255) NOT NULL default '',
  `store_id` int(11) unsigned NOT NULL,
  `vacancytype_id` int(11) unsigned NOT NULL,
  `vacancyregion_id` int(11) unsigned NOT NULL,
  `status` smallint(6) NOT NULL default '0',
  `created_time` datetime default NULL,
  `update_time` datetime default NULL,
  PRIMARY KEY (`vacancy_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->run("
-- DROP TABLE IF EXISTS {$this->getTable('vacancyregion')};
CREATE TABLE {$this->getTable('vacancyregion')} (
  `vacancyregion_id` int(11) unsigned NOT NULL auto_increment,
  `title` varchar(255) NOT NULL,
  `status` int(1) NOT NULL,
  `sequence` int(11) NOT NULL,
  `created_time` datetime NOT NULL,
  `update_time` datetime NOT NULL,
  PRIMARY KEY(`vacancyregion_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8
");

$installer->getConnection()->addConstraint('FK_VACANCY_VACANCYREGION',
    $installer->getTable('vacancy/vacancy'), 'vacancyregion_id',
    $installer->getTable('vacancy/vacancyregion'), 'vacancyregion_id',
    'CASCADE', 'CASCADE', true);

$installer->endSetup();