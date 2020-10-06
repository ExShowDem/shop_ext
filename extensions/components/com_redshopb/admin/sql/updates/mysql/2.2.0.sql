-- -----------------------------------------------------
-- Table `#__redshopb_table_lock`
-- -----------------------------------------------------

CREATE TABLE IF NOT EXISTS `#__redshopb_table_lock` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `table_name` VARCHAR(100) NOT NULL,
  `table_id` INT(10) UNSIGNED NOT NULL,
  `column_name` VARCHAR(255) NOT NULL,
  `locked_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `locked_by` INT(11) NULL DEFAULT NULL,
  `locked_method` VARCHAR(100) NOT NULL DEFAULT 'User',
  PRIMARY KEY (`id`),
  UNIQUE INDEX `rs_table_lock_UNIQUE` (`table_name` ASC, `table_id` ASC, `column_name` ASC),
  CONSTRAINT `#__rs_datalock_fk1`
    FOREIGN KEY (`locked_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;
