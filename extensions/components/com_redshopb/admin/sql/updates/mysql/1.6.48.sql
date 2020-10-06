SET FOREIGN_KEY_CHECKS = 0;

-- -----------------------------------------------------
-- Table `#__redshopb_return_orders`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_return_orders` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_return_orders` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` INT(10) UNSIGNED NOT NULL,
  `order_item_id` INT(10) UNSIGNED NOT NULL,
  `quantity` INT(10) NOT NULL DEFAULT 1,
  `comment` TINYTEXT NULL,
  `created_by` INT(11) NULL,
  `created_date` DATETIME NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` INT(11) NULL,
  `modified_date` DATETIME NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  INDEX `#__rs_retord_fk1` (`order_id` ASC),
  INDEX `#__rs_retord_fk2` (`order_item_id` ASC),
  INDEX `#__rs_retord_fk3` (`created_by` ASC),
  INDEX `#__rs_retord_fk4` (`modified_by` ASC),
  CONSTRAINT `#__rs_retord_fk1`
  FOREIGN KEY (`order_id`)
  REFERENCES `#__redshopb_order` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_retord_fk2`
  FOREIGN KEY (`order_item_id`)
  REFERENCES `#__redshopb_order_item` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_retord_fk3`
  FOREIGN KEY (`created_by`)
  REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_retord_fk4`
  FOREIGN KEY (`modified_by`)
  REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE)
  ENGINE = InnoDB
  DEFAULT CHARACTER SET = utf8;

SET FOREIGN_KEY_CHECKS = 1;