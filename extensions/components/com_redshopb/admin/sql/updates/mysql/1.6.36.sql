SET FOREIGN_KEY_CHECKS = 0;

ALTER TABLE `#__redshopb_type`
  MODIFY COLUMN `value_type` ENUM('string_value','float_value','int_value','text_value', 'field_value') NULL DEFAULT 'string_value' COMMENT 'Value field to use in the destination value table';

UPDATE `#__redshopb_type`
SET
  `value_type` = 'field_value',
  `name` = 'Dropdown - single',
  `alias` = 'dropdownsingle'
WHERE
  `name` = 'Dropdown - text';

INSERT INTO `#__redshopb_type` (`id`, `name`, `alias`, `value_type`, `field_name`) VALUES
  (6, 'Dropdown - multiple', 'dropdownmultiple', 'field_value', 'rList'),
  (7, 'Checkbox', 'checkbox', 'field_value', 'checkboxes'),
  (8, 'Radio', 'radio', 'field_value', 'radio'),
  (9, 'Scale', 'scale', 'field_value', 'range')
;

ALTER TABLE `#__redshopb_field_data`
  ADD COLUMN `field_value` INT(11) NULL DEFAULT NULL;

ALTER TABLE `#__redshopb_field`
  ADD COLUMN `filter_type_id` INT(11) NULL DEFAULT NULL;

ALTER TABLE `#__redshopb_field`
  ADD INDEX `#__rs_field_fk2` (`filter_type_id` ASC),
  ADD CONSTRAINT `#__rs_field_fk2`
    FOREIGN KEY (`filter_type_id`)
    REFERENCES `#__redshopb_type` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE;

    -- -----------------------------------------------------
-- Table `#__redshopb_filter_fieldset`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_filter_fieldset` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_filter_fieldset` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL DEFAULT '',
  `state` TINYINT(4) NOT NULL DEFAULT '1',
  `created_by` INT(11) NULL DEFAULT NULL,
  `created_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` INT(11) NULL DEFAULT NULL,
  `modified_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  INDEX `idx_name` (`name` ASC))
  ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `#__redshopb_filter_fieldset_xref`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_filter_fieldset_xref` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_filter_fieldset_xref` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `fieldset_id` INT(11) UNSIGNED NOT NULL,
  `field_id` INT(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `#__rs_filfiefld_fk1` (`fieldset_id` ASC),
  INDEX `#__rs_filfiefld_fk2` (`field_id` ASC),
  CONSTRAINT `#__rs_filfiefld_fk1`
  FOREIGN KEY (`fieldset_id`)
  REFERENCES `#__redshopb_filter_fieldset` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_filfiefld_fk2`
  FOREIGN KEY (`field_id`)
  REFERENCES `#__redshopb_field` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
  ENGINE = InnoDB;

SET FOREIGN_KEY_CHECKS = 1;
