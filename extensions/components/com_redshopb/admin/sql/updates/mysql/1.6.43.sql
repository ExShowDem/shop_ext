SET FOREIGN_KEY_CHECKS = 0;

ALTER TABLE `#__redshopb_field`
	ADD `title` VARCHAR(255) NOT NULL AFTER `name`,
	ADD `field_value_xref_id` INT(10) UNSIGNED NULL DEFAULT NULL AFTER `filter_type_id`,
	ADD INDEX `#__rs_field_fk3` (`field_value_xref_id` ASC),
	ADD CONSTRAINT `#__rs_field_fk3`
  FOREIGN KEY (`field_value_xref_id`)
  REFERENCES `#__redshopb_field` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE;

INSERT INTO `#__redshopb_type` (`id`, `name`, `alias`, `value_type`, `field_name`) VALUES
  (10, 'Date', 'date', 'string_value', 'rdatepicker'),
  (11, 'Radio - boolean', 'radioboolean', 'int_value', 'radioRedshopb')
;

SET FOREIGN_KEY_CHECKS = 1;
