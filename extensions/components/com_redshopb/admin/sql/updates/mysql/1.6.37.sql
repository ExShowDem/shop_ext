SET FOREIGN_KEY_CHECKS = 0;

ALTER TABLE `#__redshopb_template` DEFAULT CHARACTER SET utf8
COLLATE utf8_general_ci;

ALTER TABLE `#__redshopb_template` CHANGE `name` `name` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
ALTER TABLE `#__redshopb_template` CHANGE `content` `content` LONGTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;

ALTER TABLE `#__redshopb_template` ADD `default` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Indicates if this item is the default template.' AFTER `state`;

ALTER TABLE `#__redshopb_template` ADD INDEX `idx_common` (`scope` ASC, `default` ASC);
ALTER TABLE `#__redshopb_template` DROP INDEX `idx_alias`, ADD UNIQUE `idx_alias` (`alias` ASC, `scope` ASC);

INSERT INTO `#__redshopb_template` (`name`, `alias`, `scope`, `content`, `state`, `default`, `checked_out`, `checked_out_time`, `created_by`, `created_date`, `modified_by`, `modified_date`) VALUES
	('Generic Product Template', 'product', 'product', '', 1, 1, NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00');

SET FOREIGN_KEY_CHECKS = 1;
