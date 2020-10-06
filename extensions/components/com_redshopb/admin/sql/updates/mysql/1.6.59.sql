SET FOREIGN_KEY_CHECKS = 0;

-- -----------------------------------------------------
-- Table `#__redshopb_product_price`
-- -----------------------------------------------------

ALTER TABLE `#__redshopb_product`
  ADD INDEX `idx_product_state` (`state` ASC, `discontinued` ASC, `service` ASC);

SET FOREIGN_KEY_CHECKS = 1;