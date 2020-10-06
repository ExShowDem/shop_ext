ALTER TABLE `#__redshopb_product`
  ADD COLUMN `min_sale` INT NULL DEFAULT 0 AFTER `decimal_position`,
  ADD COLUMN `max_sale` INT NULL DEFAULT NULL AFTER `min_sale`,
  ADD COLUMN `pkg_size` INT NULL DEFAULT 1 AFTER `max_sale`;
