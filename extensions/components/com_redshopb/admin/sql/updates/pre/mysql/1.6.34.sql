-- -----------------------------------------------------
-- Table `#__redshopb_tag`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_tag_1_6_34`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_tag_1_6_34`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_tag' AND `constraint_name` = '#__rs_tag_fk5') THEN
    ALTER TABLE `#__redshopb_tag` DROP FOREIGN KEY `#__rs_tag_fk5`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_tag' AND `index_name` = '#__rs_tag_fk5') THEN
    ALTER TABLE `#__redshopb_tag` DROP INDEX `#__rs_tag_fk5`;
  END IF;
END//

DELIMITER ;
