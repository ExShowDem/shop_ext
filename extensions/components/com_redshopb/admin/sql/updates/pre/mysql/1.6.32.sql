-- -----------------------------------------------------
-- Table `#__redshopb_order`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_order_1_6_32`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_order_1_6_32`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_order' AND `constraint_name` = '#__rs_order_fk8') THEN
    ALTER TABLE `#__redshopb_order` DROP FOREIGN KEY `#__rs_order_fk8`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_order' AND `index_name` = '#__rs_order_fk8_idx') THEN
    ALTER TABLE `#__redshopb_order` DROP INDEX `#__rs_order_fk8_idx`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_order' AND `index_name` = '#__rs_order_fk8') THEN
    ALTER TABLE `#__redshopb_order` DROP INDEX `#__rs_order_fk8`;
  END IF;
END//

DELIMITER ;

-- -----------------------------------------------------
-- Table `#__redshopb_shipping_rates`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_shipping_rates_1_6_32`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_shipping_rates_1_6_32`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_shipping_rates' AND `constraint_name` = '#__rsb_sr_config_fk_1') THEN
    ALTER TABLE `#__redshopb_shipping_rates` DROP FOREIGN KEY `#__rsb_sr_config_fk_1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_shipping_rates' AND `constraint_name` = '#__rs_sr_config_fk_1') THEN
    ALTER TABLE `#__redshopb_shipping_rates` DROP FOREIGN KEY `#__rs_sr_config_fk_1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_shipping_rates' AND `index_name` = 'idx_shipping_configuration_id') THEN
    ALTER TABLE `#__redshopb_shipping_rates` DROP INDEX `idx_shipping_configuration_id`;
  END IF;
END//

DELIMITER ;
