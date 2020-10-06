SET FOREIGN_KEY_CHECKS=0;

-- -----------------------------------------------------
-- Table `#__redshopb_product`
-- -----------------------------------------------------

ALTER TABLE `#__redshopb_product`
   ADD COLUMN `publish_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `calc_type`,
   ADD COLUMN `unpublish_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `publish_date`;

SET FOREIGN_KEY_CHECKS=1;
