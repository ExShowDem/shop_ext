SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS `#__redshopb_stockroom_group` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `alias` VARCHAR(255) NOT NULL,
  `color` VARCHAR(7) NOT NULL DEFAULT '',
  `deadline_weekday_1` TIME NOT NULL DEFAULT '00:00:00',
  `deadline_weekday_2` TIME NOT NULL DEFAULT '00:00:00',
  `deadline_weekday_3` TIME NOT NULL DEFAULT '00:00:00',
  `deadline_weekday_4` TIME NOT NULL DEFAULT '00:00:00',
  `deadline_weekday_5` TIME NOT NULL DEFAULT '00:00:00',
  `description` VARCHAR(2048) NULL,
  `ordering` INT NULL COMMENT 'Decides the order selection of stockrooms of the company',
  `state` TINYINT(4) NOT NULL DEFAULT '1',
  `checked_out` INT(11) NULL,
  `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` INT(11) NULL,
  `created_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` INT(11) NULL,
  `modified_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  INDEX `idx_state` (`state` ASC),
  UNIQUE INDEX `idx_alias` (`alias` ASC))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;

-- -----------------------------------------------------
-- Table `#__redshopb_stockroom_group_stockroom_xref`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `#__redshopb_stockroom_group_stockroom_xref` (
  `id` INT(10) NOT NULL AUTO_INCREMENT,
  `stockroom_group_id` INT(10) UNSIGNED NOT NULL,
  `stockroom_id` INT(10) UNSIGNED NOT NULL,
  INDEX `#__rs_stockrm_grp_stock_fk1` (`stockroom_group_id` ASC),
  INDEX `#__rs_stockrm_grp_stock_fk2` (`stockroom_id` ASC),
  PRIMARY KEY (`id`),
  CONSTRAINT `#__rs_stockrm_grp_stock_fk2`
  FOREIGN KEY (`stockroom_id`)
  REFERENCES `#__redshopb_stockroom` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_stockrm_grp_stock_fk1`
  FOREIGN KEY (`stockroom_group_id`)
  REFERENCES `#__redshopb_stockroom_group` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
  ENGINE = InnoDB
  DEFAULT CHARACTER SET = utf8;

ALTER TABLE `#__redshopb_order_item`
ADD `stockroom_group_id` INT(5) UNSIGNED NOT NULL,
ADD `stockroom_group_name` VARCHAR(255) NOT NULL;

SET FOREIGN_KEY_CHECKS = 1;
