SET FOREIGN_KEY_CHECKS = 0;

-- -----------------------------------------------------
-- Table `#__redshopb_newsletter_list`
-- -----------------------------------------------------
ALTER TABLE `#__redshopb_newsletter_list`
  ADD COLUMN `company_id` INT(10) UNSIGNED NULL AFTER `alias`,
  ADD INDEX `#__rs_newslist_fk1` (`company_id` ASC),
  ADD CONSTRAINT `#__rs_newslist_fk1`
    FOREIGN KEY (`company_id`)
    REFERENCES `#__redshopb_company` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE;

-- -----------------------------------------------------
-- Table `#__redshopb_newsletter`
-- -----------------------------------------------------
ALTER TABLE `#__redshopb_newsletter`
  ADD COLUMN `company_id` INT(10) UNSIGNED NULL AFTER `alias`,
  ADD INDEX `#__rs_newsletter_fk6` (`company_id` ASC),
  ADD CONSTRAINT `#__rs_newsletter_fk6`
    FOREIGN KEY (`company_id`)
    REFERENCES `#__redshopb_company` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE;

-- -----------------------------------------------------
-- Table `#__redshopb_favoritelist`
-- -----------------------------------------------------
CALL `#__redshopb_favoritelist_1_6_21`();

DROP PROCEDURE IF EXISTS `#__redshopb_favoritelist_1_6_21`;

ALTER TABLE `#__redshopb_favoritelist`
  ADD INDEX `#__rs_favlist_fk1` (`company_id` ASC),
  ADD INDEX `#__rs_favlist_fk2` (`department_id` ASC),
  ADD INDEX `#__rs_favlist_fk3` (`user_id` ASC),
  ADD CONSTRAINT `#__rs_favlist_fk1`
    FOREIGN KEY (`company_id`)
    REFERENCES `#__redshopb_company` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_favlist_fk2`
    FOREIGN KEY (`department_id`)
    REFERENCES `#__redshopb_department` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_favlist_fk3`
    FOREIGN KEY (`user_id`)
    REFERENCES `#__redshopb_user` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE;

-- -----------------------------------------------------
-- Table `#__redshopb_cart`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `#__redshopb_cart` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `state` TINYINT(4) NOT NULL DEFAULT '1',
  `company_id` INT(10) UNSIGNED NULL,
  `department_id` INT(10) UNSIGNED NULL,
  `user_id` INT(10) UNSIGNED NULL,
  `visible_others` TINYINT(4) NOT NULL DEFAULT '0',
  `checked_out` INT(11) NULL,
  `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` INT(11) NULL,
  `created_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` INT(11) NULL,
  `modified_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  INDEX `#__rs_cart_fk1` (`company_id` ASC),
  INDEX `#__rs_cart_fk2` (`department_id` ASC),
  INDEX `#__rs_cart_fk3` (`user_id` ASC),
  INDEX `#__rs_cart_fk4` (`checked_out` ASC),
  INDEX `#__rs_cart_fk5` (`created_by` ASC),
  INDEX `#__rs_cart_fk6` (`modified_by` ASC),
  CONSTRAINT `#__rs_cart_fk1`
    FOREIGN KEY (`company_id`)
    REFERENCES `#__redshopb_company` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_cart_fk2`
    FOREIGN KEY (`department_id`)
    REFERENCES `#__redshopb_department` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_cart_fk3`
    FOREIGN KEY (`user_id`)
    REFERENCES `#__redshopb_user` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_cart_fk4`
    FOREIGN KEY (`checked_out`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_cart_fk5`
    FOREIGN KEY (`created_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_cart_fk6`
    FOREIGN KEY (`modified_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `#__redshopb_cart_item`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `#__redshopb_cart_item` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `cart_id` INT(11) NOT NULL,
  `product_id` INT(10) UNSIGNED NOT NULL,
  `product_item_id` INT(10) UNSIGNED NULL,
  `parent_cart_item_id` INT(11) NULL COMMENT 'When it’s an accessory item, it points to the parent item',
  `collection_id` INT(10) UNSIGNED NULL,
  `quantity` INT(11) NOT NULL,
  `state` TINYINT(4) NOT NULL DEFAULT '1',
  `created_by` INT(11) NULL,
  `created_date` DATETIME NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` INT(11) NULL,
  `modified_date` DATETIME NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  INDEX `#__rs_cartitem_fk1` (`cart_id` ASC),
  INDEX `#__rs_cartitem_fk2` (`created_by` ASC),
  INDEX `#__rs_cartitem_fk3` (`modified_by` ASC),
  INDEX `idx_product` (`product_id` ASC),
  INDEX `idx_product_item` (`product_item_id` ASC),
  INDEX `#__rs_cartitem_fk4` (`parent_cart_item_id` ASC),
  INDEX `idx_collection` (`collection_id` ASC),
  CONSTRAINT `#__rs_cartitem_fk1`
    FOREIGN KEY (`cart_id`)
    REFERENCES `#__redshopb_cart` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_cartitem_fk2`
    FOREIGN KEY (`created_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_cartitem_fk3`
    FOREIGN KEY (`modified_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_cartitem_fk4`
    FOREIGN KEY (`parent_cart_item_id`)
    REFERENCES `#__redshopb_cart_item` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;

SET FOREIGN_KEY_CHECKS = 1;