SET FOREIGN_KEY_CHECKS=0;

INSERT INTO `#__redshopb_template` (`name`, `alias`, `template_group`, `scope`, `content`, `state`, `default`, `editable`, `checked_out`, `checked_out_time`, `created_by`, `created_date`, `modified_by`, `modified_date`, `params`, `description`) VALUES
	('Product List Massive', 'product-list-massive', 'shop', 'product-list-massive', '', 1, 1, 0, NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00', '', 'COM_REDSHOPB_TEMPLATE_PRODUCT_LIST_MASSIVE'),
	('Product List Style Massive', 'massive', 'shop', 'product-list-style', '', 1, 0, 0, NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00', '', 'COM_REDSHOPB_TEMPLATE_MASSIVE'),
	('Generic massive product template', 'massive-element', 'shop', 'massive-product', '', 1, 1, 0, NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00', '', 'COM_REDSHOPB_TEMPLATE_MASSIVE_ELEMENT');

ALTER TABLE `#__redshopb_free_shipping_threshold_purchases`
   ADD COLUMN `category_id` INT(10) UNSIGNED NULL DEFAULT NULL AFTER `product_discount_group_id`,
   MODIFY COLUMN `product_discount_group_id` INT(10) UNSIGNED NULL DEFAULT NULL;

ALTER TABLE `#__redshopb_free_shipping_threshold_purchases` ADD CONSTRAINT `#__rs_freeshipthrespur_fk2`
  FOREIGN KEY (`category_id`)
  REFERENCES `#__redshopb_category` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION;

SET FOREIGN_KEY_CHECKS=1;
