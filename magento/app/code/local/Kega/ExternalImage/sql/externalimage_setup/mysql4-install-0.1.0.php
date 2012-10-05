<?php

$installer = $this;

$installer->startSetup();

$installer->run("

 -- DROP TABLE IF EXISTS {$this->getTable('externalimage_gallery')};
CREATE TABLE {$this->getTable('externalimage_gallery')} (
	`externalimage_id` int(10) unsigned NOT NULL auto_increment,
	`product_id` int(10) unsigned NOT NULL,
	`image_name` varchar(255) NOT NULL,
	PRIMARY KEY  (`externalimage_id`),
	UNIQUE KEY `image_name` (`image_name`),
	KEY `FK_EXTERNALIMAGE_GALLERY_CATALOG_PRODUCT_ENTITY` (`product_id`),
	CONSTRAINT `FK_EXTERNALIMAGE_GALLERY_CATALOG_PRODUCT_ENTITY` FOREIGN KEY (`product_id`)
		REFERENCES {$this->getTable('catalog_product_entity')} (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
)  ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->installEntities();

$installer->endSetup();