SET FOREIGN_KEY_CHECKS = 0;

-- -----------------------------------------------------
-- Table `#__redshopb_company`
-- -----------------------------------------------------
ALTER TABLE `#__redshopb_company`
  CHANGE `show_retail_price` `show_retail_price` TINYINT(1) NOT NULL DEFAULT '-1';

SET FOREIGN_KEY_CHECKS = 1;