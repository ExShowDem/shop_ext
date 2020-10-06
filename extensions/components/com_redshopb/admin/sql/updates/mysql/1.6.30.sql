SET FOREIGN_KEY_CHECKS = 0;

ALTER TABLE `#__redshopb_product_attribute`
  ADD COLUMN `image` VARCHAR(255) NULL AFTER `conversion_sets`;

SET FOREIGN_KEY_CHECKS = 1;