SET FOREIGN_KEY_CHECKS = 0;
SET UNIQUE_CHECKS = 0;

-- -----------------------------------------------------
-- Table `#__redshopb_order`
-- -----------------------------------------------------
ALTER TABLE `#__redshopb_order`
  ADD COLUMN `payment_name` VARCHAR(50) NOT NULL DEFAULT '' AFTER `status`;
ALTER TABLE `#__redshopb_order`
  ADD COLUMN `payment_status` VARCHAR(50) NULL DEFAULT '' AFTER `payment_name`;
ALTER TABLE `#__redshopb_order`
  ADD COLUMN `total_price_paid` DECIMAL(10,2) UNSIGNED NOT NULL AFTER `total_price`;

SET FOREIGN_KEY_CHECKS = 1;
SET UNIQUE_CHECKS = 1;
