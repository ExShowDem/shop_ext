SET FOREIGN_KEY_CHECKS=0;

ALTER TABLE `#__redshopb_product` ADD COLUMN `params` VARCHAR(2048) NULL COMMENT 'JSON formatted';
ALTER TABLE `#__redshopb_category` ADD COLUMN `params` VARCHAR(2048) NULL COMMENT 'JSON formatted';
ALTER TABLE `#__redshopb_manufacturer` ADD COLUMN `params` VARCHAR(2048) NULL COMMENT 'JSON formatted';

SET FOREIGN_KEY_CHECKS=1;
