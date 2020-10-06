SET FOREIGN_KEY_CHECKS = 0;

-- -----------------------------------------------------
-- Table `#__redshopb_shipping_route`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `#__redshopb_shipping_route` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `company_id` INT(10) UNSIGNED NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `weekday_1` TINYINT(4) NOT NULL DEFAULT '0',
  `weekday_2` TINYINT(4) NOT NULL DEFAULT '0',
  `weekday_3` TINYINT(4) NOT NULL DEFAULT '0',
  `weekday_4` TINYINT(4) NOT NULL DEFAULT '0',
  `weekday_5` TINYINT(4) NOT NULL DEFAULT '0',
  `weekday_6` TINYINT(4) NOT NULL DEFAULT '0',
  `weekday_7` TINYINT(4) NOT NULL DEFAULT '0',
  `max_delivery_time` TIME NOT NULL DEFAULT '00:00:00',
  `state` TINYINT(4) NOT NULL DEFAULT '1',
  `checked_out` INT(11) NULL,
  `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` INT(11) NULL,
  `created_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` INT(11) NULL,
  `modified_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  INDEX `idx_state` (`state` ASC),
  INDEX `#__rs_ship_route_fk1` (`company_id` ASC),
  CONSTRAINT `#__rs_ship_route_fk1`
    FOREIGN KEY (`company_id`)
    REFERENCES `#__redshopb_company` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;

-- -----------------------------------------------------
-- Table `#__redshopb_shipping_route_address_xref`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `#__redshopb_shipping_route_address_xref` (
  `id` INT(10) NOT NULL AUTO_INCREMENT,
  `shipping_route_id` INT(10) UNSIGNED NOT NULL,
  `address_id` INT(10) UNSIGNED NOT NULL,
  INDEX `#__rs_shipping_route_fk1` (`shipping_route_id` ASC),
  INDEX `#__rs_shipping_route_fk2` (`address_id` ASC),
  PRIMARY KEY (`id`),
  CONSTRAINT `#__rs_shipping_route_fk1`
  FOREIGN KEY (`shipping_route_id`)
  REFERENCES `#__redshopb_shipping_route` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_shipping_route_fk2`
  FOREIGN KEY (`address_id`)
  REFERENCES `#__redshopb_address` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
  ENGINE = InnoDB
  DEFAULT CHARACTER SET = utf8;

SET FOREIGN_KEY_CHECKS = 1;
