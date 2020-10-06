SET FOREIGN_KEY_CHECKS=0;

-- -----------------------------------------------------
-- Table `#__redshopb_free_shipping_threshold_purchases`
-- -----------------------------------------------------

ALTER TABLE `#__redshopb_free_shipping_threshold_purchases`
  ADD `created_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `threshold_expenditure`,
  ADD `created_by` INT(11) NULL DEFAULT NULL AFTER `created_date`,
  ADD `modified_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `created_by`,
  ADD `modified_by` INT(11) NULL DEFAULT NULL AFTER `modified_date`,
  ADD `checked_out` INT(11) NULL DEFAULT NULL AFTER `modified_by`,
  ADD `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `checked_out`;

ALTER TABLE `#__redshopb_free_shipping_threshold_purchases`
  CHANGE `id` `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT;

SET FOREIGN_KEY_CHECKS=1;
