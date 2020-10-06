-- -----------------------------------------------------
-- Table `#__redshopb_product`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_product_1_6_16`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_product_1_6_16`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_product' AND `constraint_name` = '#__rs_prod_fk7') THEN
    ALTER TABLE `#__redshopb_product` DROP FOREIGN KEY `#__rs_prod_fk7`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_product' AND `index_name` = '#__rs_prod_fk7') THEN
    ALTER TABLE `#__redshopb_product` DROP INDEX `#__rs_prod_fk7`;
  END IF;
END//

DELIMITER ;
