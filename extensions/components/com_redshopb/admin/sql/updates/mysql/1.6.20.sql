SET FOREIGN_KEY_CHECKS = 0;

ALTER TABLE `#__redshopb_newsletter_list`
	ADD `segmentation_json` TEXT NULL
	AFTER `segmentation_query`;

ALTER TABLE `#__redshopb_newsletter_list`
	CHANGE `id` `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT;

SET FOREIGN_KEY_CHECKS = 1;