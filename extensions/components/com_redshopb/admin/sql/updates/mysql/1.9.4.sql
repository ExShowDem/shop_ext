SET FOREIGN_KEY_CHECKS = 0;

ALTER TABLE `#__redshopb_template`
  MODIFY COLUMN `editable` TINYINT(3) UNSIGNED NOT NULL DEFAULT '1' COMMENT 'Indicates if this template can be edited or not';

SET FOREIGN_KEY_CHECKS = 1;
