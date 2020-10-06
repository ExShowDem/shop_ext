SET FOREIGN_KEY_CHECKS = 0;

ALTER TABLE `#__redshopb_company`
	ADD `image` VARCHAR(255) NULL AFTER `alias`;

ALTER TABLE `#__redshopb_department`
	ADD `image` VARCHAR(255) NULL AFTER `alias`;

DROP TABLE `#__redshopb_field_data_value`;

CREATE TABLE `#__redshopb_field_value` (
  `id` INT(10) NOT NULL AUTO_INCREMENT,
  `field_id` INT(10) UNSIGNED NOT NULL,
  `name` VARCHAR(255) NULL,
  `value` VARCHAR(255) NULL,
  `default` TINYINT(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  INDEX `#__rs_fvalue_fk1` (`field_id` ASC),
  CONSTRAINT `#__rs_fvalue_fk1`
    FOREIGN KEY (`field_id`)
    REFERENCES `#__redshopb_field` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

SET FOREIGN_KEY_CHECKS = 1;