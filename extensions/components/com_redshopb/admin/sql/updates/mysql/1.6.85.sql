ALTER TABLE `#__redshopb_product`
	CHANGE `stock_upper_level` `stock_upper_level` DOUBLE UNSIGNED NULL DEFAULT NULL COMMENT 'Above this limit, the stock is considered enough, below it, it presents a first level warning',
	CHANGE `stock_lower_level` `stock_lower_level` DOUBLE UNSIGNED NULL DEFAULT NULL COMMENT 'Below this level, it presents an alarm';

ALTER TABLE `#__redshopb_product_item`
	CHANGE `stock_upper_level` `stock_upper_level` DOUBLE UNSIGNED NULL DEFAULT NULL COMMENT 'Above this limit, the stock is considered enough, below it, it presents a first level warning',
	CHANGE `stock_lower_level` `stock_lower_level` DOUBLE UNSIGNED NULL DEFAULT NULL COMMENT 'Below this level, it presents an alarm';

ALTER TABLE `#__redshopb_stockroom`
	CHANGE `stock_upper_level` `stock_upper_level` DOUBLE UNSIGNED NULL DEFAULT NULL COMMENT 'Above this limit, the stock is considered enough, below it, it presents a first level warning',
	CHANGE `stock_lower_level` `stock_lower_level` DOUBLE UNSIGNED NULL DEFAULT NULL COMMENT 'Below this level, it presents an alarm';