SET FOREIGN_KEY_CHECKS = 0;

ALTER TABLE `#__redshopb_offer_item_xref`
	ADD `discount_type` ENUM('total','percent') NOT NULL DEFAULT 'percent' AFTER `subtotal`,
	DROP `discount_perc`;

ALTER TABLE `#__redshopb_offer`
	ADD `discount_type` ENUM('total','percent') NOT NULL DEFAULT 'percent' AFTER `subtotal`,
	ADD `customer_type` ENUM('employee','department','company','') NOT NULL DEFAULT '' AFTER `vendor_id`,
	ADD `collection_id` INT(10) UNSIGNED NULL DEFAULT NULL AFTER `user_id`,
	DROP `discount_perc`,
	ADD INDEX `#__rs_offer_fk8` (`collection_id` ASC),
	ADD CONSTRAINT `#__rs_offer_fk8` FOREIGN KEY (`collection_id`) REFERENCES `#__redshopb_collection`(`id`)
		ON DELETE RESTRICT
		ON UPDATE CASCADE;

SET FOREIGN_KEY_CHECKS = 1;
