SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;

-- -----------------------------------------------------
-- Table `#__redshopb_category`
-- -----------------------------------------------------

ALTER TABLE `#__redshopb_category`
  ADD COLUMN `product_list_template_id` INT(10) NULL AFTER `template_id`,
  ADD COLUMN `product_grid_template_id` INT(10) NULL AFTER `product_list_template_id`,
  ADD INDEX `#__rs_categ_fk8` (`product_list_template_id` ASC),
  ADD INDEX `#__rs_categ_fk9` (`product_grid_template_id` ASC),
  ADD CONSTRAINT `#__rs_categ_fk8`
    FOREIGN KEY (`product_list_template_id`)
    REFERENCES `#__redshopb_template` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_categ_fk9`
    FOREIGN KEY (`product_grid_template_id`)
    REFERENCES `#__redshopb_template` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE;

-- -----------------------------------------------------
-- Table `#__redshopb_template`
-- -----------------------------------------------------

INSERT INTO `#__redshopb_template` (`name`, `alias`, `template_group`, `scope`, `content`, `state`, `default`, `editable`, `checked_out`, `checked_out_time`, `created_by`, `created_date`, `modified_by`, `modified_date`, `params`) VALUES
  ('Generic list product template', 'list-element', 'shop', 'list-product', '', 1, 1, 0, NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00', ''),
  ('Generic grid product template', 'grid-element', 'shop', 'grid-product', '', 1, 1, 0, NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00', '');

SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
