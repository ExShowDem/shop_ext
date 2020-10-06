SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;

-- -----------------------------------------------------
-- Table `#__redshopb_product_price`
-- -----------------------------------------------------

ALTER TABLE `#__redshopb_product_price`
  ADD COLUMN `quantity_min` INT(11) UNSIGNED NULL DEFAULT NULL AFTER `ending_date`,
  ADD COLUMN `quantity_max` INT(11) UNSIGNED NULL DEFAULT NULL AFTER `quantity_min`,
  DROP INDEX `idx_product_price`;

ALTER TABLE `#__redshopb_product_price`
  ADD UNIQUE `idx_product_price` (`type_id` ASC, `type` ASC, `sales_type` ASC, `currency_id` ASC, `starting_date` ASC, `ending_date` ASC, `sales_code` ASC, `quantity_min` ASC, `quantity_max` ASC);

SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;