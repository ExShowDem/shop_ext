SET FOREIGN_KEY_CHECKS = 0;

ALTER TABLE `#__redshopb_template`
	ADD `template_group` ENUM('email','shop','email_tag','shop_tag') NOT NULL DEFAULT 'shop' AFTER `name`,
	ADD `params` TEXT NOT NULL AFTER `default`;

UPDATE `#__redshopb_template`
		SET `template_group` = 'email'
		WHERE `scope` = 'email';

ALTER TABLE `#__redshopb_template`
	CHANGE `scope` `scope` VARCHAR(255);

INSERT INTO `#__redshopb_template` (`name`, `template_group`, `scope`, `alias`, `content`, `state`, `default`, `checked_out`, `checked_out_time`, `created_by`, `created_date`, `modified_by`, `modified_date`, `params`) VALUES ('Send Offer', 'email', 'offer', 'send-offer', NULL, '1', '1', NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00', '{"0":{"mail_subject":"Offer Mail"}}');

ALTER TABLE `#__redshopb_offer` ADD `template_id` INT(10) NULL AFTER `status`,
	ADD INDEX `#__rs_offer_fk9` (`template_id` ASC),
	ADD CONSTRAINT `#__rs_offer_fk9` FOREIGN KEY (`template_id`)
		REFERENCES `#__redshopb_template`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;

SET FOREIGN_KEY_CHECKS = 1;
