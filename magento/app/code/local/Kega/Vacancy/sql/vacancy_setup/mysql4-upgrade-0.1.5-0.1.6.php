<?php
$installer = $this;

$installer->startSetup();

$installer->run("

ALTER TABLE `{$installer->getTable('vacancy/candidate')}`
	CHANGE `available-from` `available-from` VARCHAR(255) DEFAULT NULL;

ALTER TABLE `{$installer->getTable('vacancy/candidate')}`
	CHANGE `available-days` `available-days` VARCHAR(255) DEFAULT NULL;

ALTER TABLE `{$installer->getTable('vacancy/candidate')}`
	CHANGE `birth-date` `birth-date` VARCHAR(255) DEFAULT NULL;

ALTER TABLE `{$installer->getTable('vacancy/candidate')}`
	CHANGE `nationality` `nationality` VARCHAR(255) DEFAULT NULL;

ALTER TABLE `{$installer->getTable('vacancy/candidate')}`
	CHANGE `training-1` `training-1` VARCHAR(255) DEFAULT NULL;

ALTER TABLE `{$installer->getTable('vacancy/candidate')}`
	CHANGE `training-1-start` `training-1-start` VARCHAR(255) DEFAULT NULL;

ALTER TABLE `{$installer->getTable('vacancy/candidate')}`
	CHANGE `training-1-end` `training-1-end` VARCHAR(255) DEFAULT NULL;

ALTER TABLE `{$installer->getTable('vacancy/candidate')}`
	CHANGE `training-1-completed` `training-1-completed` VARCHAR(255) DEFAULT NULL;

ALTER TABLE `{$installer->getTable('vacancy/candidate')}`
	CHANGE `training-2` `training-2` VARCHAR(255) DEFAULT NULL;

ALTER TABLE `{$installer->getTable('vacancy/candidate')}`
	CHANGE `training-2-start` `training-2-start` VARCHAR(255) DEFAULT NULL;

ALTER TABLE `{$installer->getTable('vacancy/candidate')}`
	CHANGE `training-2-end` `training-2-end` VARCHAR(255) DEFAULT NULL;

ALTER TABLE `{$installer->getTable('vacancy/candidate')}`
	CHANGE `training-2-completed` `training-2-completed` VARCHAR(255) DEFAULT NULL;

;
	");

$installer->endSetup();