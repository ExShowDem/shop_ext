SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;

-- -----------------------------------------------------
-- Table `#__redshopb_template`
-- -----------------------------------------------------
ALTER TABLE `#__redshopb_template`
  MODIFY COLUMN `alias` VARCHAR(255) NOT NULL;

-- -----------------------------------------------------
-- Table `#__redshopb_unit_measure`
-- -----------------------------------------------------
ALTER TABLE `#__redshopb_unit_measure`
  ADD COLUMN `alias` VARCHAR(255) NULL AFTER `name`;

UPDATE `#__redshopb_unit_measure`
  SET `alias` = `id`;

ALTER TABLE `#__redshopb_unit_measure`
  MODIFY COLUMN `alias` VARCHAR(255) NOT NULL AFTER `name`,
  ADD UNIQUE INDEX `idx_alias` (`alias` ASC);

-- -----------------------------------------------------
-- Table `#__redshopb_type`
-- -----------------------------------------------------
ALTER TABLE  `#__redshopb_type`
  MODIFY COLUMN `alias` VARCHAR(255) NOT NULL;

-- -----------------------------------------------------
-- Table `#__redshopb_field`
-- -----------------------------------------------------
ALTER TABLE `#__redshopb_field`
  MODIFY COLUMN `multiple_values` TINYINT(4) NOT NULL DEFAULT 0;

-- -----------------------------------------------------
-- Table `#__redshopb_field_value`
-- -----------------------------------------------------
ALTER TABLE `#__redshopb_field_value`
  MODIFY COLUMN `ordering` INT(11) NOT NULL DEFAULT 0;

SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
