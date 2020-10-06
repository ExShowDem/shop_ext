ALTER TABLE `#__redshopb_company`
	ADD `show_retail_price` TINYINT(1) NOT NULL DEFAULT '0'
	AFTER `send_mail_on_order`;
ALTER TABLE `#__redshopb_product_price`
	ADD `retail_price` DECIMAL(10, 2) NOT NULL
	AFTER `price`;