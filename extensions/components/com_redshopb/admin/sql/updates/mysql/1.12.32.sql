ALTER TABLE `#__redshopb_field`
	ADD `global` TINYINT(1) NOT NULL DEFAULT '0' AFTER `searchable_backend`;

CREATE TABLE IF NOT EXISTS `#__redshopb_category_field_xref` (
	`category_id` int(10) UNSIGNED NOT NULL,
	`field_id` int(10) UNSIGNED NOT NULL,
	PRIMARY KEY (`category_id`, `field_id`),
	CONSTRAINT `#__redshopb_category_field_xref_ibfk_1`
	FOREIGN KEY (`category_id`)
	REFERENCES `#__redshopb_category` (`id`)
			ON DELETE CASCADE
			ON UPDATE CASCADE,
	CONSTRAINT `#__redshopb_category_field_xref_ibfk_2`
	FOREIGN KEY (`field_id`)
	REFERENCES `#__redshopb_field` (`id`)
		ON DELETE CASCADE
		ON UPDATE CASCADE
) 
	ENGINE = InnoDB 
	DEFAULT CHARSET = utf8;
