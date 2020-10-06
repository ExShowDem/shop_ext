SET FOREIGN_KEY_CHECKS = 0;

ALTER TABLE `#__redshopb_order_item`
	ADD CONSTRAINT `b2b_rs_orderitem_fk3` FOREIGN KEY (`product_id`) REFERENCES `#__redshopb_product`(`id`)
	ON DELETE SET NULL
	ON UPDATE CASCADE;

SET FOREIGN_KEY_CHECKS = 1;
