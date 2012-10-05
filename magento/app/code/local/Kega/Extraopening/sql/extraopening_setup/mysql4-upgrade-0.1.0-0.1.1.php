<?php

$installer = $this;

$installer->startSetup();

$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('extraopening')};
ALTER TABLE {$this->getTable('extraopening')} CHANGE `datetime` datetime_from datetime default NULL;
ALTER TABLE {$this->getTable('extraopening')} ADD `datetime_to` datetime default NULL AFTER datetime_from;
ALTER TABLE {$this->getTable('extraopening')} ADD `status` varchar(6)default NULL AFTER datetime_to;
    ");

$installer->endSetup();