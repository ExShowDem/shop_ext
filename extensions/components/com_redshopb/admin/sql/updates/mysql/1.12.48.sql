SET FOREIGN_KEY_CHECKS = 0;

INSERT INTO `#__redshopb_template` (`name`, `alias`, `template_group`, `scope`, `content`, `state`, `default`, `editable`, `checked_out`, `checked_out_time`, `created_by`, `created_date`, `modified_by`, `modified_date`, `params`) VALUES
  ('Generic Print Product Template', 'product-print', 'shop', 'product-print', '', 1, 1, 0, NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00', '');
ALTER TABLE `#__redshopb_product`
  ADD COLUMN `print_template_id` INT(10) NULL AFTER `template_id`,
  ADD CONSTRAINT `#__rs_prod_fk10`
  FOREIGN KEY (`print_template_id`) REFERENCES `#__redshopb_template` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD INDEX `#__rs_prod_fk11` (`print_template_id` ASC);

SET FOREIGN_KEY_CHECKS = 1;