CREATE TABLE IF NOT EXISTS `#__redshopb_newsletter` (
  `id` INT(10) UNSIGNED NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `subject` VARCHAR(255) NOT NULL,
  `body` LONGTEXT NOT NULL,
  `template_id` INT(10) NOT NULL,
  `state` TINYINT(4) NOT NULL,
  PRIMARY KEY (`id`, `template_id`),
  INDEX `#__rs_newstemp_fk1` (`template_id` ASC),
  CONSTRAINT `#__rs_newstemp_fk1`
    FOREIGN KEY (`template_id`)
    REFERENCES `#__redshopb_template` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE)
ENGINE = InnoDB;
