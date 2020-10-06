SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;

-- -----------------------------------------------------
-- Table `#__redshopb_field_group`
-- -----------------------------------------------------

ALTER TABLE `#__redshopb_field_group`
  MODIFY `checked_out` INT(11) NULL,
  MODIFY `created_by` INT(11) NULL,
  MODIFY `modified_by` INT(11) NULL,
  ADD INDEX `#__rs_fieldgroup_fk1` (`checked_out` ASC),
  ADD INDEX `#__rs_fieldgroup_fk2` (`created_by` ASC),
  ADD INDEX `#__rs_fieldgroup_fk3` (`modified_by` ASC),
  ADD CONSTRAINT `#__rs_fieldgroup_fk1`
    FOREIGN KEY (`checked_out`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_fieldgroup_fk2`
    FOREIGN KEY (`created_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_fieldgroup_fk3`
    FOREIGN KEY (`modified_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE;

-- -----------------------------------------------------
-- Table `#__redshopb_field`
-- -----------------------------------------------------

CALL `#__redshopb_field_1_12_34`();

DROP PROCEDURE IF EXISTS `#__redshopb_field_1_12_34`;

ALTER TABLE `#__redshopb_field`
  ADD INDEX `#__rs_field_fk4` (`field_group_id` ASC),
  ADD CONSTRAINT `#__rs_field_fk4`
    FOREIGN KEY (`field_group_id`)
    REFERENCES `#__redshopb_field_group` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE;

-- -----------------------------------------------------
-- Table `#__redshopb_category_field_xref`
-- -----------------------------------------------------

CALL `#__redshopb_category_field_xref_1_12_34`();

DROP PROCEDURE IF EXISTS `#__redshopb_category_field_xref_1_12_34`;

ALTER TABLE `#__redshopb_category_field_xref`
  ADD INDEX `#__rs_catfield_fk2` (`field_id` ASC),
  ADD INDEX `#__rs_catfield_fk1` (`category_id` ASC),
  ADD CONSTRAINT `#__rs_catfield_fk1`
    FOREIGN KEY (`category_id`)
    REFERENCES `#__redshopb_category` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_catfield_fk2`
    FOREIGN KEY (`field_id`)
    REFERENCES `#__redshopb_field` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE;

SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
