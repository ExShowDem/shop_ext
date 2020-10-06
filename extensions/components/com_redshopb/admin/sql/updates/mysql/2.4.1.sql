SET FOREIGN_KEY_CHECKS=0;

ALTER TABLE `#__redshopb_favoritelist_product_item_xref`
  ADD COLUMN `quantity` DOUBLE UNSIGNED NOT NULL DEFAULT 1 AFTER `product_item_id`;

SET FOREIGN_KEY_CHECKS=1;
