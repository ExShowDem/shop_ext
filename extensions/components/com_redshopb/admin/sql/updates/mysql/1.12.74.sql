SET FOREIGN_KEY_CHECKS=0;

ALTER TABLE `#__redshopb_unit_measure` ADD `decimal_separator` VARCHAR(1) NULL;
ALTER TABLE `#__redshopb_unit_measure` ADD `thousand_separator` VARCHAR(1) NULL;
ALTER TABLE `#__redshopb_field_data` MODIFY `float_value` DOUBLE(16,4) NULL;

ALTER TABLE `#__redshopb_field` ADD `decimal_separator` VARCHAR(1) NULL;
ALTER TABLE `#__redshopb_field` ADD `thousand_separator` VARCHAR(1) NULL;
ALTER TABLE `#__redshopb_field` ADD `decimal_position` TINYINT(4) UNSIGNED NOT NULL DEFAULT '0';

ALTER TABLE `#__redshopb_field`
  ADD COLUMN `unit_measure_id` INT NULL DEFAULT NULL AFTER `field_group_id`,
  ADD CONSTRAINT `#__rs_field_fk5`
  FOREIGN KEY (`unit_measure_id`)
  REFERENCES `#__redshopb_unit_measure` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD INDEX `#__rs_field_fk5` (`unit_measure_id` ASC);

SET FOREIGN_KEY_CHECKS=1;
