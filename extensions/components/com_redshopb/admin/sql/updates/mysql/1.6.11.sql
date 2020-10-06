SET FOREIGN_KEY_CHECKS = 0;
SET UNIQUE_CHECKS = 0;

-- -----------------------------------------------------
-- Table `#__redshopb_order`
-- -----------------------------------------------------
CALL `#__redshopb_order_1_6_11`();

DROP PROCEDURE `#__redshopb_order_1_6_11`;


-- -----------------------------------------------------
-- Table `#__redshopb_order_item`
-- -----------------------------------------------------
CALL `#__redshopb_order_item_1_6_11`();

DROP PROCEDURE `#__redshopb_order_item_1_6_11`;


SET FOREIGN_KEY_CHECKS = 1;
SET UNIQUE_CHECKS = 1;