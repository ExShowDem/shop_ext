-- -----------------------------------------------------
-- Table `#__redshopb_favoritelist_product_xref`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_favoritelist_product_xref_1_12_49`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_favoritelist_product_xref_1_12_49`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_favoritelist_product_xref' AND `constraint_name` = '#__rs_favlistprod_fk2') THEN
    ALTER TABLE `#__redshopb_favoritelist_product_xref` DROP FOREIGN KEY `#__rs_favlistprod_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_favoritelist_product_xref' AND `index_name` = '#__rs_favlistprod_fk2') THEN
    ALTER TABLE `#__redshopb_favoritelist_product_xref` DROP INDEX `#__rs_favlistprod_fk2`;
  END IF;
END//

DELIMITER ;

-- -----------------------------------------------------
-- Table `#__redshopb_product`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_product_1_12_49`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_product_1_12_49`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_product' AND `constraint_name` = '#__rs_prod_fk10') THEN
    ALTER TABLE `#__redshopb_product` DROP FOREIGN KEY `#__rs_prod_fk10`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_product' AND `index_name` = '#__rs_prod_fk10') THEN
    ALTER TABLE `#__redshopb_product` DROP INDEX `#__rs_prod_fk10`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_product' AND `constraint_name` = '#__rs_prod_fk11') THEN
    ALTER TABLE `#__redshopb_product` DROP FOREIGN KEY `#__rs_prod_fk11`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_product' AND `index_name` = '#__rs_prod_fk11') THEN
    ALTER TABLE `#__redshopb_product` DROP INDEX `#__rs_prod_fk11`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_product' AND `constraint_name` = '#__redshopb_product_ibfk_1') THEN
    ALTER TABLE `#__redshopb_product` DROP FOREIGN KEY `#__redshopb_product_ibfk_1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_product' AND `index_name` = '#__redshopb_product_ibfk_1') THEN
    ALTER TABLE `#__redshopb_product` DROP INDEX `#__redshopb_product_ibfk_1`;
  END IF;
END//

DELIMITER ;
