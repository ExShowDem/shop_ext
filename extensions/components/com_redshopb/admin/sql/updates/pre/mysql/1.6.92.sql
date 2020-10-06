-- -----------------------------------------------------
-- Table `#__redshopb_offer`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_offer_1_6_92`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_offer_1_6_92`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_offer' AND `constraint_name` = '#__rs_offer_fk1') THEN
    ALTER TABLE `#__redshopb_offer` DROP FOREIGN KEY `#__rs_offer_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_offer' AND `index_name` = '#__rs_offer_fk1') THEN
    ALTER TABLE `#__redshopb_offer` DROP INDEX `#__rs_offer_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_offer' AND `constraint_name` = '#__rs_offer_fk2') THEN
    ALTER TABLE `#__redshopb_offer` DROP FOREIGN KEY `#__rs_offer_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_offer' AND `index_name` = '#__rs_offer_fk2') THEN
    ALTER TABLE `#__redshopb_offer` DROP INDEX `#__rs_offer_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_offer' AND `constraint_name` = '#__rs_offer_fk3') THEN
    ALTER TABLE `#__redshopb_offer` DROP FOREIGN KEY `#__rs_offer_fk3`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_offer' AND `index_name` = '#__rs_offer_fk3') THEN
    ALTER TABLE `#__redshopb_offer` DROP INDEX `#__rs_offer_fk3`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_offer' AND `constraint_name` = '#__rs_offer_fk8') THEN
    ALTER TABLE `#__redshopb_offer` DROP FOREIGN KEY `#__rs_offer_fk8`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_offer' AND `index_name` = '#__rs_offer_fk8') THEN
    ALTER TABLE `#__redshopb_offer` DROP INDEX `#__rs_offer_fk8`;
  END IF;
END//

DELIMITER ;

-- -----------------------------------------------------
-- Table `#__redshopb_order`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_order_1_6_92`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_order_1_6_92`() BEGIN
IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_order' AND `constraint_name` = '#__rs_order_fk8') THEN
  ALTER TABLE `#__redshopb_order` DROP FOREIGN KEY `#__rs_order_fk8`;
END IF;

IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_order' AND `index_name` = '#__rs_order_fk8') THEN
  ALTER TABLE `#__redshopb_order` DROP INDEX `#__rs_order_fk8`;
END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_order' AND `constraint_name` = '#__rs_order_fk9') THEN
    ALTER TABLE `#__redshopb_order` DROP FOREIGN KEY `#__rs_order_fk9`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_order' AND `index_name` = '#__rs_order_fk9') THEN
    ALTER TABLE `#__redshopb_order` DROP INDEX `#__rs_order_fk9`;
  END IF;
END//

DELIMITER ;

-- -----------------------------------------------------
-- Table `#__redshopb_order_item`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_order_item_1_6_92`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_order_item_1_6_92`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_order_item' AND `constraint_name` = '#__rs_orderitem_fk3') THEN
    ALTER TABLE `#__redshopb_order_item` DROP FOREIGN KEY `#__rs_orderitem_fk3`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_order_item' AND `index_name` = '#__rs_orderitem_fk3') THEN
    ALTER TABLE `#__redshopb_order_item` DROP INDEX `#__rs_orderitem_fk3`;
  END IF;
END//

DELIMITER ;
