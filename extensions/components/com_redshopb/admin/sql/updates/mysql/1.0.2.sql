SET FOREIGN_KEY_CHECKS = 0;

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

SET FOREIGN_KEY_CHECKS = 1;