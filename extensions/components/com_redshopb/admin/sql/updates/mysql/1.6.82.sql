SET FOREIGN_KEY_CHECKS = 0;

ALTER TABLE `#__redshopb_order_item`
ADD `discount_type` ENUM('total', 'percent') NOT NULL DEFAULT 'percent' AFTER `currency_id`;

ALTER TABLE `#__redshopb_order`
ADD `discount_type` ENUM('total', 'percent') NOT NULL DEFAULT 'percent' AFTER `currency_id`,
ADD `discount` DECIMAL(10,2) NOT NULL DEFAULT '0.00' AFTER `discount_type`,
ADD `offer_id` INT(10) UNSIGNED NULL DEFAULT NULL AFTER `discount`,
ADD INDEX `#__rs_order_fk9` (`offer_id` ASC),
ADD CONSTRAINT `#__rs_order_fk9`
FOREIGN KEY (`offer_id`)
REFERENCES `#__redshopb_offer` (`id`)
	ON DELETE NO ACTION
	ON UPDATE NO ACTION;

SET FOREIGN_KEY_CHECKS = 1;