SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `#__redshopb_newsletter_user_stats`;

-- -----------------------------------------------------
-- Table `#__redshopb_newsletter_user_stats`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `#__redshopb_newsletter_user_stats` (
  `newsletter_id` INT(10) UNSIGNED NOT NULL,
  `user_id` INT(10) UNSIGNED NOT NULL,
  `html` TINYINT(3) UNSIGNED NOT NULL DEFAULT '1',
  `sent` TINYINT(3) UNSIGNED NOT NULL DEFAULT '1',
  `send_date` INT(10) UNSIGNED NOT NULL,
  `open` TINYINT(3) UNSIGNED NOT NULL DEFAULT '1',
  `open_date` INT(10) UNSIGNED NOT NULL,
  `bounce` TINYINT(4) UNSIGNED NOT NULL DEFAULT '0',
  `fail` TINYINT(4) UNSIGNED NOT NULL DEFAULT '0',
  `ip` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`newsletter_id`, `user_id`),
  INDEX `idx_user_id` (`user_id` ASC),
  INDEX `idx_send_date` (`send_date` ASC),
  CONSTRAINT `#__rs_nl_userstats_fk1`
    FOREIGN KEY (`newsletter_id`)
    REFERENCES `#__redshopb_newsletter` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_nl_userstats_fk2`
    FOREIGN KEY (`user_id`)
    REFERENCES `#__redshopb_user` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `#__redshopb_offer`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `#__redshopb_offer` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `vendor_id` INT(10) UNSIGNED NOT NULL,
  `company_id` INT(10) UNSIGNED NULL,
  `department_id` INT(10) UNSIGNED NULL,
  `user_id` INT(10) UNSIGNED NULL,
  `status` ENUM('requested','sent','accepted','rejected','ordered') NOT NULL,
  `subtotal` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `discount` DECIMAL(12,2) NULL,
  `discount_perc` DECIMAL(12,2) NULL,
  `total` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `requested_date` DATETIME NULL,
  `sent_date` DATETIME NULL,
  `execution_date` DATETIME NULL COMMENT 'Date for both For accept and reject statuses',
  `order_date` DATETIME NULL,
  `expiration_date` DATETIME NULL,
  `checked_out` INT(11) NULL,
  `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` INT(11) NULL,
  `created_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` INT(11) NULL,
  `modified_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  INDEX `#__rs_offer_fk1` (`company_id` ASC),
  INDEX `#__rs_offer_fk2` (`department_id` ASC),
  INDEX `#__rs_offer_fk3` (`user_id` ASC),
  INDEX `#__rs_offer_fk4` (`checked_out` ASC),
  INDEX `#__rs_offer_fk5` (`created_by` ASC),
  INDEX `#__rs_offer_fk6` (`modified_by` ASC),
  INDEX `#__rs_offer_fk7` (`vendor_id` ASC),
  CONSTRAINT `#__rs_offer_fk1`
    FOREIGN KEY (`company_id`)
    REFERENCES `#__redshopb_company` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_offer_fk2`
    FOREIGN KEY (`department_id`)
    REFERENCES `#__redshopb_department` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_offer_fk3`
    FOREIGN KEY (`user_id`)
    REFERENCES `#__redshopb_user` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_offer_fk4`
    FOREIGN KEY (`checked_out`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_offer_fk5`
    FOREIGN KEY (`created_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_offer_fk6`
    FOREIGN KEY (`modified_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_offer_fk7`
    FOREIGN KEY (`vendor_id`)
    REFERENCES `#__redshopb_offer` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `#__redshopb_offer_item_xref`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `#__redshopb_offer_item_xref` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `offer_id` INT(10) UNSIGNED NOT NULL,
  `product_id` INT(10) UNSIGNED NOT NULL,
  `product_item_id` INT(10) UNSIGNED NULL,
  `quantity` INT(10) NOT NULL DEFAULT 1,
  `unit_price` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `subtotal` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `discount` DECIMAL(12,2) NULL,
  `discount_perc` DECIMAL(12,2) NULL,
  `total` DECIMAL(12,2) NOT NULL DEFAULT 0,
  INDEX `#__rs_offitem_fk1` (`offer_id` ASC),
  INDEX `#__rs_offitem_fk2` (`product_id` ASC),
  PRIMARY KEY (`id`),
  INDEX `#__rs_offitem_fk3` (`product_item_id` ASC),
  CONSTRAINT `#__rs_offitem_fk2`
    FOREIGN KEY (`product_id`)
    REFERENCES `#__redshopb_product` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_offitem_fk1`
    FOREIGN KEY (`offer_id`)
    REFERENCES `#__redshopb_offer` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_offitem_fk3`
    FOREIGN KEY (`product_item_id`)
    REFERENCES `#__redshopb_product_item` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;

SET FOREIGN_KEY_CHECKS = 1;
