-- -----------------------------------------------------
-- Table `#__redshopb_newsletter`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_newsletter_1_6_10`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_newsletter_1_6_10`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_newsletter' AND `constraint_name` = '#__rs_newstemp_fk1') THEN
    ALTER TABLE `#__redshopb_newsletter` DROP FOREIGN KEY `#__rs_newstemp_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_newsletter' AND `index_name` = '#__rs_newstemp_fk1') THEN
    ALTER TABLE `#__redshopb_newsletter` DROP INDEX `#__rs_newstemp_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_newsletter' AND `index_name` = 'PRIMARY') THEN
    ALTER TABLE `#__redshopb_newsletter` DROP PRIMARY KEY;
  END IF;
END//

DELIMITER ;
