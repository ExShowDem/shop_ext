SET FOREIGN_KEY_CHECKS = 0;

-- -----------------------------------------------------
-- Table `#__redshopb_product_category_xref`
-- -----------------------------------------------------
ALTER TABLE `#__redshopb_product_category_xref`
  ADD COLUMN `ordering` INT(11) NOT NULL DEFAULT '0' AFTER `category_id`;

SET FOREIGN_KEY_CHECKS = 1;
