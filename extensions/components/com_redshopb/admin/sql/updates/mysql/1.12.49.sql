SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;

-- -----------------------------------------------------
-- Table `#__redshopb_favoritelist_product_xref`
-- -----------------------------------------------------

CALL `#__redshopb_favoritelist_product_xref_1_12_49`();

DROP PROCEDURE IF EXISTS `#__redshopb_favoritelist_product_xref_1_12_49`;

ALTER TABLE `#__redshopb_favoritelist_product_xref`
  MODIFY `quantity` DOUBLE UNSIGNED NOT NULL DEFAULT 1,
  ADD INDEX `#__rs_favlistprod_fk2` (`product_id` ASC),
  ADD CONSTRAINT `#__rs_favlistprod_fk2`
    FOREIGN KEY (`product_id`)
    REFERENCES `#__redshopb_product` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE;

-- -----------------------------------------------------
-- Table `#__redshopb_product`
-- -----------------------------------------------------

CALL `#__redshopb_product_1_12_49`();

DROP PROCEDURE IF EXISTS `#__redshopb_product_1_12_49`;

ALTER TABLE `#__redshopb_product`
  MODIFY `min_sale` DOUBLE UNSIGNED NOT NULL DEFAULT 1,
  MODIFY `max_sale` DOUBLE UNSIGNED NULL DEFAULT NULL,
  MODIFY `pkg_size` DOUBLE UNSIGNED NOT NULL DEFAULT 1,
  MODIFY `print_template_id` INT(10) NULL DEFAULT NULL,
  ADD INDEX `#__rs_prod_fk10` (`tax_group_id` ASC),
  ADD INDEX `#__rs_prod_fk11` (`print_template_id` ASC),
  ADD CONSTRAINT `#__rs_prod_fk10`
    FOREIGN KEY (`tax_group_id`)
    REFERENCES `#__redshopb_tax_group` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_prod_fk11`
    FOREIGN KEY (`print_template_id`)
    REFERENCES `#__redshopb_template` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE;

UPDATE `#__redshopb_product`
SET
  `min_sale` = 1
WHERE
  `min_sale` = 0;

SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
