-- -----------------------------------------------------
-- Table `#__redshopb_favoritelist`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_favoritelist_1_6_21`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_favoritelist_1_6_21`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_favoritelist' AND `constraint_name` = '#__rs_favlist_fk1') THEN
    ALTER TABLE `#__redshopb_favoritelist` DROP FOREIGN KEY `#__rs_favlist_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_favoritelist' AND `index_name` = '#__rs_favlist_fk1') THEN
    ALTER TABLE `#__redshopb_favoritelist` DROP INDEX `#__rs_favlist_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_favoritelist' AND `constraint_name` = '#__rs_favlist_fk2') THEN
    ALTER TABLE `#__redshopb_favoritelist` DROP FOREIGN KEY `#__rs_favlist_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_favoritelist' AND `index_name` = '#__rs_favlist_fk2') THEN
    ALTER TABLE `#__redshopb_favoritelist` DROP INDEX `#__rs_favlist_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_favoritelist' AND `constraint_name` = '#__rs_favlist_fk3') THEN
    ALTER TABLE `#__redshopb_favoritelist` DROP FOREIGN KEY `#__rs_favlist_fk3`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_favoritelist' AND `index_name` = '#__rs_favlist_fk3') THEN
    ALTER TABLE `#__redshopb_favoritelist` DROP INDEX `#__rs_favlist_fk3`;
  END IF;
END//

DELIMITER ;
