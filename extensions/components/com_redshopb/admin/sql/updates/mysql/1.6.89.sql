SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;

-- -----------------------------------------------------
-- Table `#__redshopb_tax`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_tax` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_tax` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `tax_rate` DECIMAL(10,4) NULL DEFAULT NULL,
  `price_debtor_group_id` INT(10) UNSIGNED NOT NULL,
  `state` TINYINT(1) NOT NULL DEFAULT 1,
  `checked_out` INT(11) NULL DEFAULT NULL,
  `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` INT(11) NULL DEFAULT NULL,
  `created_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` INT(11) NULL DEFAULT NULL,
  `modified_date` DATETIME NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  INDEX `idx_state` (`state` ASC),
  INDEX `#__rs_t_fk1_idx` (`price_debtor_group_id` ASC),
  INDEX `#__rs_t_fk2_idx` (`checked_out` ASC),
  INDEX `#__rs_t_fk3_idx` (`created_by` ASC),
  INDEX `#__rs_t_fk4_idx` (`modified_by` ASC),
  CONSTRAINT `#__rs_t_fk1`
    FOREIGN KEY (`price_debtor_group_id`)
    REFERENCES `#__redshopb_customer_price_group` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_t_fk2`
    FOREIGN KEY (`checked_out`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_t_fk3`
    FOREIGN KEY (`created_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_t_fk4`
    FOREIGN KEY (`modified_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE)
ENGINE = InnoDB;

SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
