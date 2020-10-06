-- -----------------------------------------------------
-- Table `#__redshopb_product_discount`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_product_discount_1_9_11`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_product_discount_1_9_11`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_product_discount' AND `index_name` = 'idx_common') THEN
    ALTER TABLE `#__redshopb_product_discount` DROP INDEX `idx_common`;
  END IF;
END//

DELIMITER ;
