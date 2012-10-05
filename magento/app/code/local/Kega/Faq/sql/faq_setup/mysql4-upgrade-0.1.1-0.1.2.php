<?php
$installer = $this;

$installer->startSetup();

$installer->run("
	ALTER TABLE `{$this->getTable('faq/category')}` ADD `overview_image` VARCHAR( 255 ) NOT NULL AFTER `permalink` ;
	ALTER TABLE `{$this->getTable('faq/category')}` ADD `category_image` VARCHAR( 255 ) NOT NULL AFTER `overview_image` ;
");
$installer->endSetup();