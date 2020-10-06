ALTER TABLE `#__redshopb_webservice_permission`
	ADD COLUMN `manual` TINYINT(4) NOT NULL DEFAULT 0 AFTER `description`;
