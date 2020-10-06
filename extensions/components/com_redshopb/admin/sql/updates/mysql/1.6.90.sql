SET FOREIGN_KEY_CHECKS = 0;

ALTER TABLE `#__redshopb_category` ADD `filter_fieldset_id` INT(11) UNSIGNED NULL DEFAULT NULL AFTER `description`;
ALTER TABLE `#__redshopb_category`
  ADD INDEX `#__rs_categ_fk7` (`filter_fieldset_id` ASC),
  ADD CONSTRAINT `#__rs_categ_fk7`
    FOREIGN KEY (`filter_fieldset_id`)
    REFERENCES `#__redshopb_filter_fieldset` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE;

UPDATE `#__redshopb_category` AS cat
LEFT JOIN `#__redshopb_product_category_xref` prodcatxref ON cat.id = prodcatxref.category_id
LEFT JOIN `#__redshopb_product` prod ON prod.id = prodcatxref.product_id
SET
    cat.`filter_fieldset_id` = prod.`filter_fieldset_id`;

SET FOREIGN_KEY_CHECKS = 1;
