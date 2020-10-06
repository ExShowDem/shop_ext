SET FOREIGN_KEY_CHECKS = 0;
SET UNIQUE_CHECKS = 0;

-- -----------------------------------------------------
-- Table `#__redshopb_order`
-- -----------------------------------------------------
ALTER TABLE `#__redshopb_order`
  ADD COLUMN `payment_title` VARCHAR(255) NOT NULL DEFAULT '' AFTER `payment_name`;

SET FOREIGN_KEY_CHECKS = 1;
SET UNIQUE_CHECKS = 1;
