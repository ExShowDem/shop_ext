SET FOREIGN_KEY_CHECKS = 0;

ALTER TABLE `#__redshopb_manufacturer` ADD COLUMN `description` TEXT NULL DEFAULT NULL AFTER `alias`;

SET FOREIGN_KEY_CHECKS = 1;