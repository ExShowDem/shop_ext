SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;

-- -----------------------------------------------------
-- Table `#__redshopb_product`
-- -----------------------------------------------------
ALTER TABLE `#__redshopb_product`
  DROP INDEX `idx_sku`;

ALTER TABLE `#__redshopb_product`
  ADD COLUMN `related_sku` VARCHAR(255) NULL AFTER `manufacturer_sku`,
  ADD INDEX `idx_sku` (`sku` ASC, `manufacturer_sku` ASC, `related_sku` ASC);

SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
