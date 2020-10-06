-- -----------------------------------------------------
-- Table `#__redshopb_template`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_template_1_6_91`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_template_1_6_91`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`columns` WHERE `table_schema` = DATABASE() AND `table_name` = '#__redshopb_template' AND `column_name` = 'editable') THEN
    ALTER TABLE `#__redshopb_template` DROP COLUMN `editable`;
  END IF;
END//

DELIMITER ;
