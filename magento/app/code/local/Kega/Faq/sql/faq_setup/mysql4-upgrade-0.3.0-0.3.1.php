<?php

$installer = $this;

$installer->startSetup();

$installer->run("

ALTER TABLE `{$this->getTable('faq/category')}`
    CHANGE COLUMN `order` `display_order` smallint(6) default NULL;

ALTER TABLE `{$this->getTable('faq/question')}`
    CHANGE COLUMN `order` `display_order` smallint(6) default NULL;

ALTER TABLE `{$this->getTable('faq/category_store_view')}`
    CHANGE COLUMN `order` `display_order` smallint(6) default NULL;

ALTER TABLE `{$this->getTable('faq/question_store_view')}`
    CHANGE COLUMN `order` `display_order` smallint(6) default NULL;

ALTER TABLE `{$this->getTable('faq/question_store_view')}`
	ADD COLUMN `category_id` int(11) NULL;

");

$installer->endSetup();