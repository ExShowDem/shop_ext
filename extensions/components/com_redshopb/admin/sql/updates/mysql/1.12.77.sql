SET FOREIGN_KEY_CHECKS=0;

ALTER TABLE `#__redshopb_order` ADD COLUMN `customer_email` VARCHAR(255) NOT NULL DEFAULT '' AFTER `customer_name2`;
ALTER TABLE `#__redshopb_order` ADD COLUMN `customer_phone` VARCHAR(50) NOT NULL DEFAULT '' AFTER `customer_email`;

SET FOREIGN_KEY_CHECKS=1;
