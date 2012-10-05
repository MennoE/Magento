<?php
$installer = $this;

$installer->startSetup();

$installer->run("

ALTER TABLE `{$installer->getTable('vacancy/vacancytype')}`
	ADD COLUMN `meta_keywords` varchar(255) default '';

ALTER TABLE `{$installer->getTable('vacancy/vacancytype')}`
	ADD COLUMN `meta_description` varchar(255) default '';

");

$installer->endSetup();