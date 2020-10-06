SET FOREIGN_KEY_CHECKS=0;

ALTER TABLE `#__redshopb_customer_price_group`
  ADD COLUMN `default` TINYINT(1) NOT NULL DEFAULT 0 AFTER `show_stock_as`;

ALTER TABLE `#__redshopb_product`
  MODIFY `calc_type` INT(10) NULL,
  ADD INDEX `#__rs_prod_fk12` (`calc_type` ASC),
  ADD CONSTRAINT `#__rs_prod_fk12`
    FOREIGN KEY (`calc_type`)
    REFERENCES `#__redshopb_calc_type` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE;

ALTER TABLE `#__redshopb_field`
    MODIFY `importable` TINYINT(1) NOT NULL DEFAULT '0';

SET FOREIGN_KEY_CHECKS=1;
