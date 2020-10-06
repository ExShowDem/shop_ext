ALTER TABLE `#__redshopb_field`
	ADD COLUMN `only_available` TINYINT(4) NOT NULL DEFAULT 1 AFTER `multiple_values`;
