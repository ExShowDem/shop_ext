ALTER TABLE `#__redshopb_customer_price_group`
ADD `show_stock_as` ENUM ('actual_stock', 'color_codes', 'hide', 'not_set') NOT NULL DEFAULT 'not_set' AFTER `company_id`;

ALTER TABLE `#__redshopb_company`
CHANGE `show_stock_as` `show_stock_as` ENUM('actual_stock','color_codes','hide','not_set') NOT NULL DEFAULT 'not_set';
