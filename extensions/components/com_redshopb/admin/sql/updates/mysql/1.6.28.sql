SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;

-- -----------------------------------------------------
-- Table `#__redshopb_order_item`
-- -----------------------------------------------------
CALL `#__redshopb_order_item_1_6_28`();

DROP PROCEDURE IF EXISTS `#__redshopb_order_item_1_6_28`;

ALTER TABLE `#__redshopb_order_item`
  ADD INDEX `#__rs_orderitem_fk3` (`collection_id` ASC),
  ADD INDEX `#__rs_orderitem_fk7` (`stockroom_id` ASC);

-- -----------------------------------------------------
-- Table `#__redshopb_offer`
-- -----------------------------------------------------
ALTER TABLE `#__redshopb_offer`
  MODIFY COLUMN `name` VARCHAR(255) NOT NULL,
  MODIFY COLUMN `state` TINYINT(4) NOT NULL DEFAULT '1';

SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;