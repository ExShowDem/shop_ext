SET FOREIGN_KEY_CHECKS = 0;
SET UNIQUE_CHECKS = 0;

-- Table `#__redshopb_collection_product_xref`
-- -----------------------------------------------------
ALTER TABLE `#__redshopb_collection_product_xref`
  ADD COLUMN `ordering` INT(10) NULL AFTER `price`;

SET FOREIGN_KEY_CHECKS = 1;
SET UNIQUE_CHECKS = 1;
