SET FOREIGN_KEY_CHECKS=0;

ALTER TABLE `#__redshopb_collection` ADD `alias` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_bin' NOT NULL DEFAULT '' AFTER `name`;

SET FOREIGN_KEY_CHECKS=1;
