-- -----------------------------------------------------
-- Table `#__redshopb_order`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_order_1_6_11`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_order_1_6_11`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_order' AND `constraint_name` = '#__rs_order_fk6') THEN
    ALTER TABLE `#__redshopb_order` DROP FOREIGN KEY `#__rs_order_fk6`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_order' AND `constraint_name` = '#__rs_order_fk7') THEN
    ALTER TABLE `#__redshopb_order` DROP FOREIGN KEY `#__rs_order_fk7`;
  END IF;
END//

DELIMITER ;


-- -----------------------------------------------------
-- Table `#__redshopb_order_item`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_order_item_1_6_11`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_order_item_1_6_11`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_order_item' AND `constraint_name` = '#__rs_orderitem_fk3') THEN
    ALTER TABLE `#__redshopb_order_item` DROP FOREIGN KEY `#__rs_orderitem_fk3`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_order_item' AND `constraint_name` = '#__rs_orderitem_fk4') THEN
    ALTER TABLE `#__redshopb_order_item` DROP FOREIGN KEY `#__rs_orderitem_fk4`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_order_item' AND `constraint_name` = '#__rs_orderitem_fk5') THEN
    ALTER TABLE `#__redshopb_order_item` DROP FOREIGN KEY `#__rs_orderitem_fk5`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_order_item' AND `constraint_name` = '#__rs_orderitem_fk6') THEN
    ALTER TABLE `#__redshopb_order_item` DROP FOREIGN KEY `#__rs_orderitem_fk6`;
  END IF;
END//

DELIMITER ;
