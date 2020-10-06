-- -----------------------------------------------------
-- Table `#__redshopb_company`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_company_1_6_86`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_company_1_6_86`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_company' AND `index_name` = 'idx_url_unique') THEN
    ALTER TABLE `#__redshopb_company` DROP INDEX `idx_url_unique`;
  END IF;
END//

DELIMITER ;
