SET FOREIGN_KEY_CHECKS=0;

-- -----------------------------------------------------
-- Table `#__redshopb_company`
-- -----------------------------------------------------
ALTER TABLE `#__redshopb_company` ADD `invoice_email` VARCHAR(255) NULL DEFAULT NULL AFTER `phone`;

SET FOREIGN_KEY_CHECKS=1;
