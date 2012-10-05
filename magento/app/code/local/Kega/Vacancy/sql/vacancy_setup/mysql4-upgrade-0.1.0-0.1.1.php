<?php

$installer = $this;
/* @var $installer Mage_Catalog_Model_Resource_Eav_Mysql4_Setup */

$installer->startSetup();

// was replaced from vacancytype module
$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('vacancytype')};
CREATE TABLE {$this->getTable('vacancytype')} (
  `vacancytype_id` int(11) unsigned NOT NULL auto_increment,
  `title` varchar(255) NOT NULL default '',
  `content` text NOT NULL default '',
  `status` smallint(6) NOT NULL default '0',
  `created_time` datetime NULL,
  `update_time` datetime NULL,
  PRIMARY KEY (`vacancytype_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");

// add foreign key to vacancy table
$installer->getConnection()->addConstraint('FK_VACANCY_VACANCYTYPE',
    $installer->getTable('vacancy/vacancy'), 'vacancytype_id',
    $installer->getTable('vacancy/vacancytype'), 'vacancytype_id',
    'CASCADE', 'CASCADE', true);
$installer->endSetup();