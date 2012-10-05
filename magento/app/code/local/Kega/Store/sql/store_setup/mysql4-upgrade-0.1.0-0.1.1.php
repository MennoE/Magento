<?php
$installer = $this;

$installer->startSetup();

$installer->run("

-- DROP TABLE IF EXISTS `{$installer->getTable('store/storeview')}`;
 CREATE TABLE `{$installer->getTable('store/storeview')}` (
	`store_id` int(10) unsigned NOT NULL auto_increment,
	`storeview_id` smallint(5) unsigned NOT NULL,
	PRIMARY KEY  (`store_id`,`storeview_id`),
  	CONSTRAINT `FK_STORE_STORE` FOREIGN KEY (`store_id`) REFERENCES `{$installer->getTable('store_entity')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  	CONSTRAINT `FK_STORE_STOREVIEW` FOREIGN KEY (`storeview_id`) REFERENCES `{$installer->getTable('core_store')}` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


");
$installer->endSetup();

//add the default store view to every store
$data	= $installer->getConnection()->fetchAll("SELECT entity_id FROM `{$this->getTable('store_entity')}`");

foreach ($data as $row) {

    $installer->run("INSERT `{$this->getTable('store/storeview')}`
                SET `store_id` = {$row['entity_id']}, storeview_id = 0");
}