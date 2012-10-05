<?php

$installer = $this;
$installer->startSetup();
$installer->run("ALTER TABLE {$this->getTable('externalimage_gallery')} ADD COLUMN `is_main` tinyint (1)UNSIGNED  DEFAULT '0' NOT NULL;");
$installer->endSetup();