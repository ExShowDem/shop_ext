SET FOREIGN_KEY_CHECKS=0;

-- -----------------------------------------------------
-- Table `#__redshopb_config`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_config` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_config` (
 `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
 `name` VARCHAR(200) NOT NULL,
 `value`  LONGTEXT NULL,
 PRIMARY KEY (`id`),
 UNIQUE INDEX `#__rs_config_fk1` (`name` ASC))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;

SET FOREIGN_KEY_CHECKS=1;
