SET FOREIGN_KEY_CHECKS = 0;

ALTER TABLE `#__redshopb_field_data`
	MODIFY `item_id` INT(10) UNSIGNED NOT NULL COMMENT 'Entity id to extend (i.e. product id) - variable FK depending on the extended entity',
  ADD `subitem_id` INT(10) UNSIGNED NULL COMMENT 'Sub item id that overrides the main item value (i.e. product_item)' AFTER `item_id`;

ALTER TABLE `#__redshopb_field_data`
  DROP INDEX `idx_item`;

ALTER TABLE `#__redshopb_field_data`
  ADD INDEX `idx_item` (`item_id` ASC, `subitem_id` ASC);

SET FOREIGN_KEY_CHECKS = 1;
