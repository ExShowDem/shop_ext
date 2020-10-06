SET FOREIGN_KEY_CHECKS = 0;

ALTER TABLE `#__redshopb_template` DROP INDEX `idx_common`, ADD INDEX `idx_common` (`scope` ASC, `default` ASC);

SET FOREIGN_KEY_CHECKS = 1;
