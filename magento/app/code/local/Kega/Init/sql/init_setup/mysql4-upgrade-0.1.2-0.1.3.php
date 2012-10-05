<?php
$installer = $this;

$installer->startSetup();

$installer->run("

-- DROP TABLE IF EXISTS `{$installer->getTable('core/file_storage')}`;
CREATE TABLE IF NOT EXISTS `{$installer->getTable('core/file_storage')}` (
    `file_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `content` LONGBLOB NOT NULL,
    `upload_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `filename` VARCHAR(255) NOT NULL DEFAULT '',
    `directory_id` INT(10) UNSIGNED NULL DEFAULT NULL,
    `directory` VARCHAR(255) NULL DEFAULT NULL,
    PRIMARY KEY (`file_id`),
    UNIQUE INDEX `IDX_FILENAME` (`filename`, `directory`),
    INDEX `directory_id` (`directory_id`),
    CONSTRAINT `FK_FILE_DIRECTORY` FOREIGN KEY (`directory_id`)
	REFERENCES `core_directory_storage` (`directory_id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='File storage';

");
$installer->endSetup();