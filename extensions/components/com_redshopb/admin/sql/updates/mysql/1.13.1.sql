SET FOREIGN_KEY_CHECKS=0;

-- -----------------------------------------------------
-- Table `#__redshopb_shipping_rates`
-- -----------------------------------------------------

ALTER TABLE `#__redshopb_shipping_rates`
   ADD COLUMN `on_product_discount_group` TEXT NOT NULL AFTER `on_product`;

-- -----------------------------------------------------
-- Table `#__redshopb_product`
-- -----------------------------------------------------

ALTER TABLE `#__redshopb_product`
   ADD COLUMN `weight` FLOAT NOT NULL COMMENT 'kg' AFTER `campaign`,
   ADD COLUMN `volume` FLOAT NOT NULL COMMENT 'm3' AFTER `weight`;

-- -----------------------------------------------------
-- Table `#__redshopb_free_shipping_threshold_purchases`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_free_shipping_threshold_purchases` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_free_shipping_threshold_purchases` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_discount_group_id` INT(10) UNSIGNED NOT NULL,
  `threshold_expenditure` FLOAT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `idx_alias` (`product_discount_group_id` ASC),
  CONSTRAINT `#__rs_freeshipthrespur_fk1`
    FOREIGN KEY (`product_discount_group_id`)
    REFERENCES `#__redshopb_product_discount_group`(`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;

-- -----------------------------------------------------
-- Table `#__redshopb_field`
-- -----------------------------------------------------

ALTER TABLE `#__redshopb_field`
  ADD COLUMN `prefix` VARCHAR(255) NULL AFTER `params`,
  ADD COLUMN `suffix` VARCHAR(255) NULL AFTER `prefix`
;

-- -----------------------------------------------------
-- Table `#__redshopb_role`
-- -----------------------------------------------------

ALTER TABLE `#__redshopb_role` CHANGE `joomla_group_id` `joomla_group_id` INT(10) UNSIGNED NULL COMMENT 'fk to a joomla group id';

ALTER TABLE `#__redshopb_role`
	DROP FOREIGN KEY `#__rs_role_fk3`;

ALTER TABLE `#__redshopb_role`
 	ADD CONSTRAINT `#__rs_role_fk3`
    FOREIGN KEY (`joomla_group_id`)
    REFERENCES `#__usergroups` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE;

SET FOREIGN_KEY_CHECKS=1;
