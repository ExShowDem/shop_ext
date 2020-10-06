SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;

-- -----------------------------------------------------
-- Table `#__redshopb_company`
-- -----------------------------------------------------

ALTER TABLE `#__redshopb_company`
  ADD `wallet_product_id` INT(10) UNSIGNED NULL DEFAULT NULL COMMENT 'Wallet product to be added' AFTER `deleted`;

SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
