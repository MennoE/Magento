<?php

$installer = $this;

$installer->startSetup();

$installer->run("
	ALTER TABLE `{$this->getTable('smtppro_email_log')}`
		CHANGE COLUMN `to` `to_email` varchar(255) NOT NULL default '';
");

$installer->endSetup();