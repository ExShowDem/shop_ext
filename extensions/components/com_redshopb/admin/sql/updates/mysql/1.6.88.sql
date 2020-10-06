SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;

-- -----------------------------------------------------
-- Table `#__redshopb_stockroom_product_xref`
-- -----------------------------------------------------
ALTER TABLE `#__redshopb_stockroom_product_xref`
  DROP PRIMARY KEY;

ALTER TABLE `#__redshopb_stockroom_product_xref`
  ADD COLUMN `id` INT(10) NOT NULL AUTO_INCREMENT FIRST,
  ADD PRIMARY KEY (`id`);


-- -----------------------------------------------------
-- Table `#__redshopb_stockroom_product_item_xref`
-- -----------------------------------------------------
ALTER TABLE `#__redshopb_stockroom_product_item_xref`
  DROP PRIMARY KEY;

ALTER TABLE `#__redshopb_stockroom_product_item_xref`
  ADD COLUMN `id` INT(10) NOT NULL AUTO_INCREMENT FIRST,
  ADD PRIMARY KEY (`id`);


SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
