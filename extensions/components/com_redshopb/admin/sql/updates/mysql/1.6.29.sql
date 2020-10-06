SET FOREIGN_KEY_CHECKS = 0;

ALTER TABLE `#__redshopb_company`
 	DROP FOREIGN KEY `#__rs_company_fk9`,
	DROP `conversion_id`;

ALTER TABLE `#__redshopb_conversion`
	ADD `product_attribute_id` INT(10) UNSIGNED NOT NULL AFTER `id`,
	ADD INDEX `#__rs_conv_fk1` (`product_attribute_id` ASC),
	ADD CONSTRAINT `#__rs_conv_fk1`
	FOREIGN KEY (`product_attribute_id`)
	REFERENCES `#__redshopb_product_attribute`(`id`)
	ON DELETE CASCADE
	ON UPDATE CASCADE;

SET FOREIGN_KEY_CHECKS = 1;