<?php

$installer = $this;

$installer->startSetup();

$installer->run("

ALTER TABLE `{$this->getTable('faq/question_store_view')}`
	CHANGE COLUMN `question` `question` text NULL;
	
ALTER TABLE `{$this->getTable('faq/question_store_view')}`
	CHANGE COLUMN `answer` `answer` text NULL;

");

$installer->endSetup();