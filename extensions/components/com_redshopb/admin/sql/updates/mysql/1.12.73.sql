SET FOREIGN_KEY_CHECKS = 0;

ALTER TABLE `#__redshopb_product_discount` CHANGE `type` `type` ENUM('product','product_item','product_discount_group') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'product';

CREATE TABLE IF NOT EXISTS `#__redshopb_product_item_discount_group_xref` (
	`product_item_id` INT(10) UNSIGNED NOT NULL,
	`discount_group_id` INT(10) UNSIGNED NOT NULL,
	PRIMARY KEY (`product_item_id`, `discount_group_id`),
	INDEX `#__rs_prod_item_dgx_fk1` (`discount_group_id` ASC),
	INDEX `#__rs_prod_item_dgx_fk2` (`product_item_id` ASC),
	CONSTRAINT `#__rs_prod_item_dgx_fk1`
	FOREIGN KEY (`discount_group_id`)
	REFERENCES `#__redshopb_product_discount_group` (`id`)
		ON DELETE CASCADE
		ON UPDATE CASCADE,
	CONSTRAINT `#__rs_prod_item_dgx_fk2`
	FOREIGN KEY (`product_item_id`)
	REFERENCES `#__redshopb_product_item` (`id`)
		ON DELETE CASCADE
		ON UPDATE CASCADE)
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8;

SET FOREIGN_KEY_CHECKS = 1;
