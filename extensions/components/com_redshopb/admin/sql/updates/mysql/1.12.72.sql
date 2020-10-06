SET FOREIGN_KEY_CHECKS=0;

-- -----------------------------------------------------
-- Table `#__redshopb_user_multi_company`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_user_multi_company`;

CREATE TABLE IF NOT EXISTS `#__redshopb_user_multi_company` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT(10) UNSIGNED NOT NULL,
  `company_id` INT(10) UNSIGNED NOT NULL,
  `role_id` INT(10) UNSIGNED NOT NULL,
  `main` TINYINT(4) NOT NULL DEFAULT '0',
  `state` TINYINT(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE INDEX `#__idx_user_company` (`user_id` ASC, `company_id` ASC),
  INDEX `#__rs_use_mul_com_fk1` (`user_id` ASC),
  INDEX `#__rs_use_mul_com_fk2` (`company_id` ASC),
  INDEX `#__rs_use_mul_com_fk3` (`role_id` ASC),
  INDEX `idx_state` (`state` ASC, `main` ASC),
  CONSTRAINT `#__rs_use_mul_com_fk1`
    FOREIGN KEY (`user_id`)
    REFERENCES `#__redshopb_user` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_use_mul_com_fk2`
    FOREIGN KEY (`company_id`)
    REFERENCES `#__redshopb_company` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_use_mul_com_fk3`
    FOREIGN KEY (`role_id`)
    REFERENCES `#__redshopb_role_type` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;

ALTER TABLE `#__redshopb_order`
  ADD COLUMN `user_company_id` INT(10) UNSIGNED NULL DEFAULT NULL AFTER `offer_id`,
  ADD CONSTRAINT `#__rs_order_fk10`
  FOREIGN KEY (`user_company_id`) REFERENCES `#__redshopb_company` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  ADD INDEX `#__rs_order_fk10` (`user_company_id` ASC);

SET FOREIGN_KEY_CHECKS=1;
