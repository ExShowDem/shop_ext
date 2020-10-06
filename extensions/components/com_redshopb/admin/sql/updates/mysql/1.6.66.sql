SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;

-- -----------------------------------------------------
-- Table `#__redshopb_field_value`
-- -----------------------------------------------------

ALTER TABLE `#__redshopb_field_value`
  ADD COLUMN `ordering` INT(11) NOT NULL DEFAULT '0';

ALTER TABLE `#__redshopb_field_value`
  DROP INDEX `#__rs_fvalue_fk1`;

ALTER TABLE `#__redshopb_field_value`
  ADD INDEX `#__rs_fvalue_fk1` (`field_id` ASC, `ordering` ASC);

SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
