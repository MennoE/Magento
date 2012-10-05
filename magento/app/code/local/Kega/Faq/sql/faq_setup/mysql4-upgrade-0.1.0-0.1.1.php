<?php
$installer = $this;

$installer->startSetup();

$installer->run("
	ALTER TABLE `{$this->getTable('faq/category')}` ADD `permalink` VARCHAR( 255 ) NOT NULL AFTER `name` ;
	ALTER TABLE `{$this->getTable('faq/question')}` ADD `permalink` VARCHAR( 255 ) NOT NULL AFTER `category_id` ;
");
$installer->endSetup();