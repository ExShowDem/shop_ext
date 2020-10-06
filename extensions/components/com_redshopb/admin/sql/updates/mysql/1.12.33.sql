CREATE TABLE IF NOT EXISTS `#__redshopb_field_group` (
	`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `scope` ENUM('product', 'order', 'category', 'company', 'department', 'user') NOT NULL DEFAULT 'product',
    `name` VARCHAR(255) NOT NULL,
    `alias` VARCHAR(255) NOT NULL,
    `ordering` INT(11) UNSIGNED NOT NULL,
    `checked_out` INT(11) NULL,
    `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
    `created_by` INT(11) NULL DEFAULT NULL,
    `created_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
    `modified_by` INT(11) NULL DEFAULT NULL,
    `modified_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
	PRIMARY KEY (`id`)
)
	ENGINE = InnoDB
	DEFAULT CHARSET = utf8;

ALTER TABLE `#__redshopb_field`
	ADD `field_group_id` INT(11) UNSIGNED NULL DEFAULT NULL
		AFTER `field_value_xref_id`,
	ADD CONSTRAINT `#__rs_field_fk4`
		FOREIGN KEY (`field_group_id`)
		REFERENCES `#__redshopb_field_group` (`id`)
			ON DELETE SET NULL
			ON UPDATE CASCADE;
