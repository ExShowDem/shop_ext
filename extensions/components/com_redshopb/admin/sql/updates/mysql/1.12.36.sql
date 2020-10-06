-- -----------------------------------------------------
-- Table `#__redshopb_holiday`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_holiday` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_holiday` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `day` INT NOT NULL,
  `month` INT NOT NULL,
  `fixed_date` DATE NULL,
  `country_id` SMALLINT(5) UNSIGNED NOT NULL,
  `checked_out` INT(11) NULL,
  `checked_out_time` DATETIME NULL,
  `created_by` INT(11) NULL DEFAULT NULL,
  `created_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` INT(11) NULL DEFAULT NULL,
  `modified_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  INDEX `#__rs_holiday_fk1` (`country_id` ASC),
  INDEX `#__rs_holiday_fk2` (`checked_out` ASC),
  INDEX `#__rs_holiday_fk3` (`created_by` ASC),
  INDEX `#__rs_holiday_fk4` (`modified_by` ASC),
  CONSTRAINT `#__rs_holiday_fk1`
    FOREIGN KEY (`country_id`)
    REFERENCES `#__redshopb_country` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_holiday_fk2`
    FOREIGN KEY (`checked_out`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_holiday_fk3`
    FOREIGN KEY (`created_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_holiday_fk4`
    FOREIGN KEY (`modified_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE)
ENGINE = InnoDB;
