SET FOREIGN_KEY_CHECKS = 0;

ALTER TABLE `#__redshopb_media`
  ADD COLUMN `ordering` INT(11) NOT NULL DEFAULT '0' AFTER `product_id`,
  ADD INDEX `idx_ordering` (`product_id` ASC, `ordering` ASC);

SET FOREIGN_KEY_CHECKS = 1;
