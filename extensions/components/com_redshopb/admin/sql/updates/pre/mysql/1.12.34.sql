-- -----------------------------------------------------
-- Table `#__redshopb_field`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_field_1_12_34`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_field_1_12_34`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_field' AND `constraint_name` = '#__rs_field_fk4_idx') THEN
    ALTER TABLE `#__redshopb_field` DROP FOREIGN KEY `#__rs_field_fk4_idx`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_field' AND `index_name` = '#__rs_field_fk4_idx') THEN
    ALTER TABLE `#__redshopb_field` DROP INDEX `#__rs_field_fk4_idx`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_field' AND `constraint_name` = '#__rs_field_fk4') THEN
    ALTER TABLE `#__redshopb_field` DROP FOREIGN KEY `#__rs_field_fk4`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_field' AND `index_name` = '#__rs_field_fk4') THEN
    ALTER TABLE `#__redshopb_field` DROP INDEX `#__rs_field_fk4`;
  END IF;
END//

DELIMITER ;

-- -----------------------------------------------------
-- Table `#__redshopb_category_field_xref`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_category_field_xref_1_12_34`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_category_field_xref_1_12_34`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_category_field_xref_ibfk_1' AND `constraint_name` = '#__rs_field_fk4_idx') THEN
    ALTER TABLE `#__redshopb_category_field_xref` DROP FOREIGN KEY `#__redshopb_category_field_xref_ibfk_1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_category_field_xref' AND `index_name` = '#__redshopb_category_field_xref_ibfk_1') THEN
    ALTER TABLE `#__redshopb_category_field_xref` DROP INDEX `#__redshopb_category_field_xref_ibfk_1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_category_field_xref_ibfk_2_idx' AND `constraint_name` = '#__rs_field_fk4_idx') THEN
    ALTER TABLE `#__redshopb_category_field_xref` DROP FOREIGN KEY `#__redshopb_category_field_xref_ibfk_2_idx`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_category_field_xref' AND `index_name` = '#__redshopb_category_field_xref_ibfk_2_idx') THEN
    ALTER TABLE `#__redshopb_category_field_xref` DROP INDEX `#__redshopb_category_field_xref_ibfk_2_idx`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_category_field_xref_ibfk_2' AND `constraint_name` = '#__rs_field_fk4_idx') THEN
    ALTER TABLE `#__redshopb_category_field_xref` DROP FOREIGN KEY `#__redshopb_category_field_xref_ibfk_2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_category_field_xref' AND `index_name` = '#__redshopb_category_field_xref_ibfk_2') THEN
    ALTER TABLE `#__redshopb_category_field_xref` DROP INDEX `#__redshopb_category_field_xref_ibfk_2`;
  END IF;
END//

DELIMITER ;
