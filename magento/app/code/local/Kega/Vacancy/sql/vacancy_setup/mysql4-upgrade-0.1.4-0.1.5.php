<?php
$installer = $this;

$installer->startSetup();

$installer->run("



-- apply-for-function  text

ALTER TABLE `{$installer->getTable('vacancy/candidate')}` 
	CHANGE `cv` `cv-upload` VARCHAR(255) DEFAULT NULL  ;
ALTER TABLE `{$installer->getTable('vacancy/candidate')}`
	ADD COLUMN `motivation-upload` varchar(255) DEFAULT NULL;
ALTER TABLE `{$installer->getTable('vacancy/candidate')}`
	ADD COLUMN `photo-upload` varchar(255) DEFAULT NULL;

ALTER TABLE `vacancycandidate` CHANGE `preferred-store-1` `preferred-store-1` INT( 10 ) UNSIGNED NULL ,
CHANGE `preferred-store-2` `preferred-store-2` INT( 10 ) UNSIGNED NULL ,
CHANGE `preferred-store-3` `preferred-store-3` INT( 10 ) UNSIGNED NULL;
	");

$installer->endSetup();