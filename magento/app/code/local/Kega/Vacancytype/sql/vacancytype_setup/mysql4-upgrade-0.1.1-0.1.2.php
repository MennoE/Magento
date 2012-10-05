<?php
$installer = $this;
$installer->startSetup();
$installer->run("ALTER TABLE `{$this->getTable('vacancytype')}` CHANGE `content` `text` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL");
$installer->endSetup();