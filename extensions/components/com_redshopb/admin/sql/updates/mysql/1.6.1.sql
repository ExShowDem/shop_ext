ALTER TABLE `#__redshopb_conversion`
	MODIFY `name` VARCHAR(255) NOT NULL,
	MODIFY `alias` VARCHAR(255) NULL;

ALTER TABLE `#__redshopb_template`
	MODIFY `name` VARCHAR(255) NOT NULL;

CREATE TABLE IF NOT EXISTS `#__redshopb_unit_measure` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `description` VARCHAR(255) NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;

ALTER TABLE `#__redshopb_product`
	MODIFY `stock_upper_level` FLOAT(11,2) NULL COMMENT 'Above this limit, the stock is considered enough, below it, it presents a first level warning',
	MODIFY `stock_lower_level` FLOAT(11,2) NULL COMMENT 'Below this level, it presents an alarm',
	ADD `unit_measure_id` INT NULL AFTER `featured`,
	ADD INDEX `#__rs_prod_fk6` (`unit_measure_id` ASC),
	ADD CONSTRAINT `#__rs_prod_fk6`
	    FOREIGN KEY (`unit_measure_id`)
	    REFERENCES `#__redshopb_unit_measure` (`id`)
	    ON DELETE SET NULL
	    ON UPDATE CASCADE;

ALTER TABLE `#__redshopb_product_item`
	MODIFY `stock_upper_level` FLOAT(11,2) NULL COMMENT 'Above this limit, the stock is considered enough, below it, it presents a first level warning',
	MODIFY `stock_lower_level` FLOAT(11,2) NULL COMMENT 'Below this level, it presents an alarm';

ALTER TABLE `#__redshopb_type`
	MODIFY `name` VARCHAR(255) NOT NULL;

ALTER TABLE `#__redshopb_stockroom`
	MODIFY `stock_upper_level` FLOAT(11,2) NULL COMMENT 'Above this limit, the stock is considered enough, below it, it presents a first level warning',
	MODIFY `stock_lower_level` FLOAT(11,2) NULL COMMENT 'Below this level, it presents an alarm';

ALTER TABLE  `#__redshopb_stockroom_product_xref`
	MODIFY `amount` FLOAT(11,2) NOT NULL DEFAULT 0,
	MODIFY `stock_upper_level` FLOAT(11,2) NULL,
	MODIFY `stock_lower_level` FLOAT(11,2) NULL;

ALTER TABLE  `#__redshopb_stockroom_product_item_xref`
	MODIFY `amount` FLOAT(11,2) NOT NULL DEFAULT 0,
	MODIFY `stock_upper_level` FLOAT(11,2) NULL,
	MODIFY `stock_lower_level` FLOAT(11,2) NULL;
