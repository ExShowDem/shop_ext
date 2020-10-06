SET FOREIGN_KEY_CHECKS = 0;

-- -----------------------------------------------------
-- Table `#__redshopb_field_data`
-- -----------------------------------------------------
CALL `#__redshopb_field_data_1_9_10`();

DROP PROCEDURE IF EXISTS `#__redshopb_field_data_1_9_10`;

ALTER TABLE `#__redshopb_field_data`
  ADD INDEX `idx_common` (`state` ASC, `field_id` ASC, `item_id` ASC, `subitem_id` ASC, `field_value` ASC);

SET FOREIGN_KEY_CHECKS = 1;
