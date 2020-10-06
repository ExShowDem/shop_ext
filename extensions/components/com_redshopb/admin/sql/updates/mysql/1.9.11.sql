SET FOREIGN_KEY_CHECKS = 0;

-- -----------------------------------------------------
-- Table `#__redshopb_product_discount`
-- -----------------------------------------------------
CALL `#__redshopb_product_discount_1_9_11`();

DROP PROCEDURE IF EXISTS `#__redshopb_product_discount_1_9_11`;

ALTER TABLE `#__redshopb_product_discount`
	ADD INDEX `idx_common` (`type` ASC, `type_id` ASC, `sales_type` ASC, `sales_id` ASC, `starting_date` ASC, `ending_date` ASC, `percent` ASC, `kind` ASC, `total` ASC, `state` ASC);

ALTER TABLE `#__redshopb_order`
	ADD `params` TEXT NOT NULL AFTER `sales_header_type`;

SET FOREIGN_KEY_CHECKS = 1;
