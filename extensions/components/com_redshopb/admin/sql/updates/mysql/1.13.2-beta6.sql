SET FOREIGN_KEY_CHECKS=0;

ALTER TABLE `#__redshopb_field`
  ADD COLUMN `importable` TINYINT(1) NOT NULL DEFAULT '0' AFTER `decimal_position`;

SET FOREIGN_KEY_CHECKS=1;
