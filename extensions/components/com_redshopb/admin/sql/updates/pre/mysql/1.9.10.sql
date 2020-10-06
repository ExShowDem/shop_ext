-- -----------------------------------------------------
-- Table `#__redshopb_field_data`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_field_data_1_9_10`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_field_data_1_9_10`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_field_data' AND `index_name` = 'idx_common') THEN
    ALTER TABLE `#__redshopb_field_data` DROP INDEX `idx_common`;
  END IF;
END//

DELIMITER ;
