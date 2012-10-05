<?php
$installer = $this;

$installer->startSetup();

$installer->run("
	
	ALTER TABLE `{$this->getTable('faq/category_store_view')}` ADD `overview_image` VARCHAR( 255 ) NULL AFTER `permalink` ;
	ALTER TABLE `{$this->getTable('faq/category_store_view')}` ADD `category_image` VARCHAR( 255 ) NULL AFTER `overview_image` ;
");
$installer->endSetup();