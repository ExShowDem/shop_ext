SET FOREIGN_KEY_CHECKS=0;

ALTER TABLE `#__redshopb_field` ADD COLUMN `required` TINYINT(4) NOT NULL DEFAULT '0' AFTER `state`;

SET FOREIGN_KEY_CHECKS=1;
