SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;

-- -----------------------------------------------------
-- Table `#__redshopb_manufacturer`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `#__redshopb_manufacturer` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `parent_id` INT(10) UNSIGNED NULL,
  `lft` INT(11) NOT NULL DEFAULT 0,
  `rgt` INT(11) NOT NULL DEFAULT 0,
  `level` INT(10) UNSIGNED NOT NULL DEFAULT 0,
  `path` VARCHAR(255) NOT NULL DEFAULT '',
  `alias` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_bin' NOT NULL DEFAULT '',
  `image` VARCHAR(255) NULL,
  `state` TINYINT(4) NOT NULL DEFAULT '1',
  `checked_out` INT(11) NULL,
  `checked_out_time` DATETIME NULL,
  `created_by` INT(11) NULL,
  `created_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` INT(11) NULL,
  `modified_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  INDEX `idx_name` (`name` ASC),
  INDEX `idx_state` (`state` ASC),
  INDEX `idx_path` (`path` ASC),
  INDEX `idx_left_right` (`lft` ASC, `rgt` ASC),
  UNIQUE INDEX `idx_alias` (`parent_id` ASC, `alias` ASC),
  INDEX `#__rs_manufacturer_fk1` (`parent_id` ASC),
  INDEX `#__rs_manufacturer_fk2` (`created_by` ASC),
  INDEX `#__rs_manufacturer_fk3` (`modified_by` ASC),
  INDEX `#__rs_manufacturer_fk4` (`checked_out` ASC),
  CONSTRAINT `#__rs_manufacturer_fk1`
    FOREIGN KEY (`parent_id`)
    REFERENCES `#__redshopb_manufacturer` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_manufacturer_fk2`
    FOREIGN KEY (`created_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_manufacturer_fk3`
    FOREIGN KEY (`modified_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_manufacturer_fk4`
    FOREIGN KEY (`checked_out`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;

INSERT INTO `#__redshopb_manufacturer` (`parent_id`, `lft`, `rgt`, `level`, `path`, `name`, `alias`)
  VALUES (NULL, 0, 1, 0, '', 'ROOT', 'root');

-- -----------------------------------------------------
-- Table `#__redshopb_product`
-- -----------------------------------------------------
ALTER TABLE `#__redshopb_product`
  ADD COLUMN `manufacturer_id` INT(10) UNSIGNED NULL DEFAULT NULL AFTER `filter_fieldset_id`,
  ADD INDEX `#__rs_prod_fk9` (`manufacturer_id` ASC),
  ADD CONSTRAINT `#__rs_prod_fk9`
    FOREIGN KEY (`manufacturer_id`)
    REFERENCES `#__redshopb_manufacturer` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE;

-- Remove old data sync of PIM brand services.
DELETE FROM `#__redshopb_sync` WHERE `reference` = 'erp.pim.brands';

SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
