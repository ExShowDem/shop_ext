SET FOREIGN_KEY_CHECKS = 0;

ALTER TABLE `#__redshopb_tag`
	ADD `image` VARCHAR(255) NULL DEFAULT '' AFTER `path`;

SET FOREIGN_KEY_CHECKS = 1;