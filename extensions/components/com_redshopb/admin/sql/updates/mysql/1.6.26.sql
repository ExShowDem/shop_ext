SET FOREIGN_KEY_CHECKS = 0;

ALTER TABLE `#__redshopb_order_item` ADD COLUMN `stockroom_id` INT(5) UNSIGNED NOT NULL AFTER `collection_erp_id`;
CREATE INDEX `#__rs_orderitem_fk3_idx` ON `#__redshopb_order_item` (`stockroom_id` ASC);

ALTER TABLE `#__redshopb_shipping_rates` DROP INDEX `idx_filter`;
ALTER TABLE `#__redshopb_shipping_rates` ADD INDEX `idx_filter` (`zip_start` ASC, `countries`(255) ASC, `zip_end` ASC, `weight_start` ASC, `weight_end` ASC, `volume_start` ASC, `volume_end` ASC, `length_start` ASC, `length_end` ASC, `width_start` ASC, `width_end` ASC, `height_start` ASC, `height_end` ASC, `order_total_start` ASC, `order_total_end` ASC);

SET FOREIGN_KEY_CHECKS = 1;

