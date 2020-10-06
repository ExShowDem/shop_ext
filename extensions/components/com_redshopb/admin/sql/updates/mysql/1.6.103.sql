ALTER TABLE `#__redshopb_sync`
ADD `main_reference` TINYINT(1) NOT NULL DEFAULT '0' AFTER `execute_sync`,
ADD `deleted` TINYINT(1) NOT NULL DEFAULT '0' AFTER `main_reference`,
ADD `metadata` TEXT NULL,
ADD INDEX `idx_main_reference` (`main_reference` ASC),
ADD INDEX `idx_deleted` (`deleted` ASC);