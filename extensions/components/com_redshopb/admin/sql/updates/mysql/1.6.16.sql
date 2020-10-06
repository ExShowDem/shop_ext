SET FOREIGN_KEY_CHECKS = 0;
SET UNIQUE_CHECKS = 0;

-- -----------------------------------------------------
-- Table `#__redshopb_product`
-- -----------------------------------------------------
CALL `#__redshopb_product_1_6_16`();

DROP PROCEDURE `#__redshopb_product_1_6_16`;

ALTER TABLE `#__redshopb_product`
  ADD INDEX `#__rs_prod_fk7` (`category_id` ASC),
  ADD CONSTRAINT `#__rs_prod_fk7`
    FOREIGN KEY (`category_id`)
    REFERENCES `#__redshopb_category` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE;

SET FOREIGN_KEY_CHECKS = 1;
SET UNIQUE_CHECKS = 1;
