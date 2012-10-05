<?php
$installer = $this;

$installer->startSetup();

$installer->run("

ALTER TABLE `{$installer->getTable('vacancy/vacancytype')}`
	ADD COLUMN `vacancy_form_type` varchar(255) default NULL;

");

$installer->endSetup();