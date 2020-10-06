-- -----------------------------------------------------
-- Table `#__redshopb_order_item`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_order_item_1_6_28`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_order_item_1_6_28`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_order_item' AND `constraint_name` = '#__rs_orderitem_fk3') THEN
    ALTER TABLE `#__redshopb_order_item` DROP FOREIGN KEY `#__rs_orderitem_fk3`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_order_item' AND `index_name` = '#__rs_orderitem_fk3') THEN
    ALTER TABLE `#__redshopb_order_item` DROP INDEX `#__rs_orderitem_fk3`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_order_item' AND `index_name` = '#__rs_orderitem_fk3_idx') THEN
    ALTER TABLE `#__redshopb_order_item` DROP INDEX `#__rs_orderitem_fk3_idx`;
  END IF;
END//

DELIMITER ;
