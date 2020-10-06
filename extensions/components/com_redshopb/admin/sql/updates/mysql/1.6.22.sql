SET FOREIGN_KEY_CHECKS = 0;

ALTER TABLE `#__redshopb_newsletter` CHANGE `id` `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT;

INSERT INTO `#__redshopb_template` (`id`, `name`, `alias`, `scope`, `content`, `state`, `checked_out`, `checked_out_time`, `created_by`, `created_date`, `modified_by`, `modified_date`) VALUES
	(1, 'Generic mail template', 'generic-mail-template', 'email', NULL, 1, NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00');

CREATE TABLE IF NOT EXISTS `#__redshopb_newsletter_user_stats` (
	`newsletter_id` int(10) unsigned NOT NULL,
	`user_id` int(10) unsigned NOT NULL,
	`html` tinyint(3) unsigned NOT NULL DEFAULT '1',
	`sent` tinyint(3) unsigned NOT NULL DEFAULT '1',
	`send_date` int(10) unsigned NOT NULL,
	`open` tinyint(3) unsigned NOT NULL DEFAULT '0',
	`open_date` int(11) NOT NULL,
	`bounce` tinyint(4) NOT NULL DEFAULT '0',
	`fail` tinyint(4) NOT NULL DEFAULT '0',
	`ip` varchar(100) DEFAULT NULL,
	PRIMARY KEY (`newsletter_id`,`user_id`),
	KEY `idx_user_id` (`user_id`),
	KEY `idx_send_date` (`send_date`),
	CONSTRAINT `#__rs_nl_userstats_fk_2`
	FOREIGN KEY (`user_id`)
	REFERENCES `#__redshopb_user` (`id`)
		ON DELETE CASCADE
		ON UPDATE CASCADE,
	CONSTRAINT `#__rs_nl_userstats_fk_1`
	FOREIGN KEY (`newsletter_id`)
	REFERENCES `#__redshopb_newsletter` (`id`)
		ON DELETE CASCADE
		ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

SET FOREIGN_KEY_CHECKS = 1;