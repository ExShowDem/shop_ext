SET FOREIGN_KEY_CHECKS = 0;

-- -----------------------------------------------------
-- Table `#__redshopb_manufacturer`
-- -----------------------------------------------------
ALTER TABLE `#__redshopb_manufacturer`
  ADD COLUMN `category` VARCHAR(255) NULL DEFAULT NULL AFTER `description`;

SET FOREIGN_KEY_CHECKS = 1;
