SET FOREIGN_KEY_CHECKS = 0;

ALTER TABLE `#__redshopb_product_discount`
  ADD `kind` TINYINT(1) NOT NULL DEFAULT '0'
  COMMENT 'Kind of discount. 0 for percent. 1 for amount.' AFTER `ending_date`;

ALTER TABLE `#__redshopb_product_discount`
  ADD `total` DECIMAL(10,2) NOT NULL DEFAULT '0.00' AFTER `percent`;

ALTER TABLE `#__redshopb_product_discount` DROP INDEX `idx_common`;

ALTER TABLE `#__redshopb_product_discount`
  ADD INDEX `idx_common` (`type` ASC, `type_id` ASC, `sales_type` ASC, `sales_id` ASC, `starting_date` ASC, `ending_date` ASC, `kind` ASC, `percent` ASC, `total` ASC, `state` ASC);

ALTER TABLE `#__redshopb_product_discount`
  ADD `quantity_min` DOUBLE UNSIGNED NULL DEFAULT NULL AFTER `currency_id`;

ALTER TABLE `#__redshopb_product_discount`
  ADD `quantity_max` DOUBLE UNSIGNED NULL DEFAULT NULL AFTER `quantity_min`;

SET FOREIGN_KEY_CHECKS = 1;
