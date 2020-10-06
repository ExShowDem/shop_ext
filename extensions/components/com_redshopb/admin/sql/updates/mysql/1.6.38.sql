SET FOREIGN_KEY_CHECKS = 0;

ALTER TABLE `#__redshopb_product` ADD `filter_fieldset_id` INT(11) UNSIGNED NULL DEFAULT NULL AFTER `template_id`;
ALTER TABLE `#__redshopb_product`
  ADD INDEX `#__rs_prod_fk8` (`filter_fieldset_id` ASC),
  ADD CONSTRAINT `#__rs_prod_fk8`
    FOREIGN KEY (`filter_fieldset_id`)
    REFERENCES `#__redshopb_filter_fieldset` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE;

SET FOREIGN_KEY_CHECKS = 1;
