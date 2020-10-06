SET FOREIGN_KEY_CHECKS = 0;

ALTER TABLE `#__redshopb_order_item`
  ADD COLUMN `stockroom_name` VARCHAR(255) NOT NULL AFTER `stockroom_id`;

SET FOREIGN_KEY_CHECKS = 1;