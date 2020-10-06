DROP TABLE IF EXISTS `#__redshopb_product_complimentary` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_product_complimentary` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` INT(10) UNSIGNED NOT NULL,
  `complimentary_product_id` INT(10) UNSIGNED NOT NULL,
  `state` TINYINT(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  INDEX `#__rs_prod_compl_fk2` (`complimentary_product_id` ASC),
  INDEX `#__rs_prod_compl_fk1` (`product_id` ASC),
  CONSTRAINT `#__rs_prod_compl_fk1`
  FOREIGN KEY (`product_id`)
  REFERENCES `#__redshopb_product` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_prod_compl_fk2`
  FOREIGN KEY (`complimentary_product_id`)
  REFERENCES `#__redshopb_product` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
  ENGINE = InnoDB
  DEFAULT CHARACTER SET = utf8;
