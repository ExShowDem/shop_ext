SET FOREIGN_KEY_CHECKS = 0;

ALTER TABLE `#__redshopb_company`
	ADD `show_retail_price` TINYINT(1) NOT NULL DEFAULT '0'
	AFTER `send_mail_on_order`;
ALTER TABLE `#__redshopb_product_price`
	ADD `retail_price` DECIMAL(10, 2) NOT NULL
	AFTER `price`;

CREATE TABLE IF NOT EXISTS `#__redshopb_product_company_xref` (
	`product_id` INT(10) UNSIGNED NOT NULL,
	`company_id` INT(10) UNSIGNED NOT NULL,
	KEY `idx_common` (`product_id`, `company_id`),
	KEY `idx_company_id` (`company_id`),
	CONSTRAINT `#__redshopb_product_company_xref_ibfk_2` FOREIGN KEY (`company_id`) REFERENCES `#__redshopb_company` (`id`)
		ON DELETE CASCADE
		ON UPDATE CASCADE,
	CONSTRAINT `#__redshopb_product_company_xref_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `#__redshopb_product` (`id`)
		ON DELETE CASCADE
		ON UPDATE CASCADE
)
	ENGINE =InnoDB
	DEFAULT CHARSET =utf8;

ALTER TABLE `#__redshopb_order`
ADD `delivery_address_code` VARCHAR(255) NOT NULL AFTER `delivery_address_id`,
ADD `delivery_address_type` ENUM('employee','department','company','') NOT NULL DEFAULT '' AFTER `delivery_address_code`;

SET FOREIGN_KEY_CHECKS = 1;
