SET FOREIGN_KEY_CHECKS=0;

ALTER TABLE `#__redshopb_category`
  ADD COLUMN `hide` TINYINT(4) NOT NULL DEFAULT '0' COMMENT 'Hide or show in menus & shop' AFTER `state`;

SET FOREIGN_KEY_CHECKS=1;
