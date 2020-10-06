-- -----------------------------------------------------
-- Table `#__redshopb_collection_product_xref`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_collection_product_xref_1_6_58`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_collection_product_xref_1_6_58`() BEGIN
  IF NOT EXISTS (SELECT * FROM `information_schema`.`columns` WHERE `table_schema` = DATABASE() AND `table_name` = '#__redshopb_collection_product_xref' AND `column_name` = 'ordering') THEN
      ALTER TABLE `#__redshopb_collection_product_xref` ADD COLUMN `ordering` INT(10) NULL AFTER `price`;
  END IF;
END//

DELIMITER ;
