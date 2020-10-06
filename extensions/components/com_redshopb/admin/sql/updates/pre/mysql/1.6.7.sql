-- -----------------------------------------------------
-- Table `#__redshopb_product_attribute_value`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_product_attribute_value_1_6_7`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_product_attribute_value_1_6_7`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_product_attribute_value' AND `constraint_name` = '#__rs_prod_av_fk1') THEN
    ALTER TABLE `#__redshopb_product_attribute_value` DROP FOREIGN KEY `#__rs_prod_av_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_product_attribute_value' AND `index_name` = '#__rs_prod_av_fk1') THEN
    ALTER TABLE `#__redshopb_product_attribute_value` DROP INDEX `#__rs_prod_av_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_product_attribute_value' AND `constraint_name` = 'I') THEN
    ALTER TABLE `#__redshopb_product_attribute_value` DROP FOREIGN KEY `I`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_product_attribute_value' AND `index_name` = 'I') THEN
    ALTER TABLE `#__redshopb_product_attribute_value` DROP INDEX `I`;
  END IF;
END//

DELIMITER ;


-- -----------------------------------------------------
-- Table `#__redshopb_order`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_order_1_6_7`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_order_1_6_7`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_order' AND `constraint_name` = '#__rs_order_fk6') THEN
    ALTER TABLE `#__redshopb_order` DROP FOREIGN KEY `#__rs_order_fk6`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_order' AND `index_name` = '#__rs_order_fk6') THEN
    ALTER TABLE `#__redshopb_order` DROP INDEX `#__rs_order_fk6`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_order' AND `constraint_name` = 'idx_currency') THEN
    ALTER TABLE `#__redshopb_order` DROP FOREIGN KEY `idx_currency`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_order' AND `index_name` = 'idx_currency') THEN
    ALTER TABLE `#__redshopb_order` DROP INDEX `idx_currency`;
  END IF;

  -- Column `delivery_address_code` added back in v.1.6.5
  IF EXISTS (SELECT * FROM `information_schema`.`columns` WHERE `table_schema` = DATABASE() AND `table_name` = '#__redshopb_order' AND `column_name` = 'temp_delivery_address_code') THEN
    UPDATE `#__redshopb_order` SET `delivery_address_code` = `temp_delivery_address_code`;
    ALTER TABLE `#__redshopb_order` DROP COLUMN `temp_delivery_address_code`;
  END IF;

  -- Column `delivery_address_type` added back in v.1.6.5
  IF EXISTS (SELECT * FROM `information_schema`.`columns` WHERE `table_schema` = DATABASE() AND `table_name` = '#__redshopb_order' AND `column_name` = 'temp_delivery_address_type') THEN
    UPDATE `#__redshopb_order` SET `delivery_address_type` = `temp_delivery_address_type`;
    ALTER TABLE `#__redshopb_order` DROP COLUMN `temp_delivery_address_type`;
  END IF;
END//

DELIMITER ;


-- -----------------------------------------------------
-- Table `#__redshopb_product_price`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_product_price_1_6_7`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_product_price_1_6_7`() BEGIN
  -- Column `retail_price` added back in v.1.6.5
  IF EXISTS (SELECT * FROM `information_schema`.`columns` WHERE `table_schema` = DATABASE() AND `table_name` = '#__redshopb_product_price' AND `column_name` = 'temp_retail_price') THEN
    UPDATE `#__redshopb_product_price` SET `retail_price` = `temp_retail_price`;
    ALTER TABLE `#__redshopb_product_price` DROP COLUMN `temp_retail_price`;
  END IF;
END//

DELIMITER ;


-- -----------------------------------------------------
-- Table `#__redshopb_company`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_company_1_6_7`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_company_1_6_7`() BEGIN
  -- Column `show_retail_price` added back in v.1.6.5
  IF EXISTS (SELECT * FROM `information_schema`.`columns` WHERE `table_schema` = DATABASE() AND `table_name` = '#__redshopb_company' AND `column_name` = 'temp_show_retail_price') THEN
    UPDATE `#__redshopb_company` SET `show_retail_price` = `temp_show_retail_price`;
    ALTER TABLE `#__redshopb_company` DROP COLUMN `temp_show_retail_price`;
  END IF;

  -- Index idx_alias to be modified in this version
  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_company' AND `index_name` = 'idx_alias') THEN
    ALTER TABLE `#__redshopb_company` DROP INDEX `idx_alias`;
  END IF;
END//

DELIMITER ;


-- -----------------------------------------------------
-- Table `#__redshopb_product_attribute`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_product_attribute_1_6_7`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_product_attribute_1_6_7`() BEGIN
  -- Converted old types to new type_id (same order but starting from 1), as populated in v.1.6.4
  IF EXISTS (SELECT * FROM `information_schema`.`columns` WHERE `table_schema` = DATABASE() AND `table_name` = '#__redshopb_product_attribute' AND `column_name` = 'type') THEN
    UPDATE `#__redshopb_product_attribute` SET `type_id` = `type` + 1;
    ALTER TABLE `#__redshopb_product_attribute` DROP COLUMN `type`;
  END IF;
END//

DELIMITER ;


-- -----------------------------------------------------
-- Table `#__redshopb_category`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_category_1_6_7`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_category_1_6_7`() BEGIN
  -- Index idx_alias to be modified in this version
  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_category' AND `index_name` = 'idx_alias') THEN
    ALTER TABLE `#__redshopb_category` DROP INDEX `idx_alias`;
  END IF;
END//

DELIMITER ;


-- -----------------------------------------------------
-- Table `#__redshopb_type`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_type_1_6_7`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_type_1_6_7`() BEGIN
  -- #__rs_type_uq1 index to be renamed
  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_type' AND `index_name` = '#__rs_type_uq1') THEN
    ALTER TABLE `#__redshopb_type` DROP INDEX `#__rs_type_uq1`;
  END IF;
END//

DELIMITER ;


-- -----------------------------------------------------
-- Table `#__redshopb_field`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_field_1_6_7`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_field_1_6_7`() BEGIN
  -- #__rs_type_uq1 index to be renamed
  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_field' AND `index_name` = '#__rs_field_uq1') THEN
    ALTER TABLE `#__redshopb_field` DROP INDEX `#__rs_field_uq1`;
  END IF;
END//

DELIMITER ;


-- -----------------------------------------------------
-- Table `#__redshopb_tag`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_tag_1_6_7`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_tag_1_6_7`() BEGIN
  -- #__rs_type_uq1 index to be renamed
  IF NOT EXISTS (SELECT * FROM `#__redshopb_tag` WHERE `alias` = 'root') THEN
    INSERT INTO `#__redshopb_tag` (`parent_id`, `lft`, `rgt`, `level`, `path`, `name`, `alias`, `state`)
    VALUES
      (0, 0, 0, 0, 'root', 'ROOT', 'root', 1);
  END IF;
END//

DELIMITER ;

-- -----------------------------------------------------
-- Table `#__redshopb_cron`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_cron_1_6_7`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_cron_1_6_7`() BEGIN
  SET @id = (SELECT max(id) + 1 FROM  `#__redshopb_cron`);
  SET @s = CONCAT("ALTER TABLE `#__redshopb_cron` AUTO_INCREMENT = ", @id); 
  PREPARE stmt FROM @s; 
  EXECUTE stmt; 
  DEALLOCATE PREPARE stmt;
END//

DELIMITER ;
