SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;

-- -----------------------------------------------------
-- Table `#__redshopb_address`
-- -----------------------------------------------------
ALTER TABLE `#__redshopb_address`
  MODIFY COLUMN `customer_type` ENUM('employee', 'department', 'company', 'stockroom', '') NOT NULL DEFAULT '' COMMENT 'Can be employee, department, company, stockroom or empty';

-- -----------------------------------------------------
-- Table `#__redshopb_stockroom`
-- -----------------------------------------------------
ALTER TABLE `#__redshopb_stockroom`
  ADD COLUMN `state` TINYINT(4) NOT NULL DEFAULT '1' AFTER `ordering`;

SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
