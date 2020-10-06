SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;

-- -----------------------------------------------------
-- Table `#__redshopb_company`
-- -----------------------------------------------------
CALL `#__redshopb_company_1_6_86`();

DROP PROCEDURE IF EXISTS `#__redshopb_company_1_6_86`;

-- -----------------------------------------------------
-- Table `#__redshopb_product_price`
-- -----------------------------------------------------
ALTER TABLE `#__redshopb_product_price`
  MODIFY COLUMN `sales_type` ENUM('all_customers', 'customer_price_group', 'customer_price', 'campaign') NOT NULL DEFAULT 'all_customers';

SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
