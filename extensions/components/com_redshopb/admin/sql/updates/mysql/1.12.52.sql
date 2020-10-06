SET FOREIGN_KEY_CHECKS = 0;

-- -----------------------------------------------------
-- Table `#__redshopb_address`
-- -----------------------------------------------------
ALTER TABLE `#__redshopb_address`
  ADD COLUMN `phone` VARCHAR(255) NULL DEFAULT NULL AFTER `email`;

SET FOREIGN_KEY_CHECKS = 1;
