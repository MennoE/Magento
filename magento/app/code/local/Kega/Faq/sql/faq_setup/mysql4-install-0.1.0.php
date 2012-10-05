<?php

$installer = $this;

$installer->startSetup();

$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('faq/category')};
CREATE TABLE {$this->getTable('faq/category')} (
  `category_id` int(11) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `order` smallint(6) NOT NULL default '0',
  PRIMARY KEY (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- DROP TABLE IF EXISTS {$this->getTable('faq/question')};
CREATE TABLE {$this->getTable('faq/question')} (
  `question_id` int(11) unsigned NOT NULL auto_increment,
  `category_id` int(11) unsigned NOT NULL,
  `question` text NOT NULL default '',
  `answer` text NOT NULL default '',
  `order` smallint(6) NOT NULL default '0',
  `created_time` datetime NULL,
  `update_time` datetime NULL,
  PRIMARY KEY (`question_id`),
  CONSTRAINT `FK_FAQ_QUESTION_FAQ_CATEGORY` FOREIGN KEY (`category_id`)
		REFERENCES {$this->getTable('faq/category')} (`category_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");

$installer->endSetup();