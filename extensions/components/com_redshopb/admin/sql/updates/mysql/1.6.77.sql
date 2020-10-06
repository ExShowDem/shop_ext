SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;-- ------------------------------------------------------- Table `#__redshopb_webservice_permission`-- -----------------------------------------------------DROP TABLE IF EXISTS `#__redshopb_webservice_permission` ;CREATE TABLE IF NOT EXISTS `#__redshopb_webservice_permission` (  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,  `scope` VARCHAR(255) NOT NULL DEFAULT 'product',  `name` VARCHAR(255) NOT NULL DEFAULT '',  `description` VARCHAR(500) NOT NULL DEFAULT '',  `state` TINYINT(4) NOT NULL DEFAULT '1',  PRIMARY KEY (`id`),  INDEX `idx_scope` (`scope` ASC, `name` ASC, `state` ASC))  ENGINE = InnoDB  DEFAULT CHARACTER SET = utf8;-- ------------------------------------------------------- Table `#__redshopb_webservice_permission_user_xref`-- -----------------------------------------------------DROP TABLE IF EXISTS `#__redshopb_webservice_permission_user_xref` ;CREATE TABLE IF NOT EXISTS `#__redshopb_webservice_permission_user_xref` (  `user_id` INT(11) NOT NULL,  `webservice_permission_id` INT(10) UNSIGNED NOT NULL,  PRIMARY KEY (`user_id`, `webservice_permission_id`),  INDEX `#__rs_webperuse_fk1` (`user_id` ASC),  INDEX `#__rs_webperuse_fk2` (`webservice_permission_id` ASC),  CONSTRAINT `#__rs_webperuse_fk1`  FOREIGN KEY (`user_id`)  REFERENCES `#__users` (`id`)    ON DELETE CASCADE    ON UPDATE CASCADE,  CONSTRAINT `#__rs_webperuse_fk2`  FOREIGN KEY (`webservice_permission_id`)  REFERENCES `#__redshopb_webservice_permission` (`id`)    ON DELETE CASCADE    ON UPDATE CASCADE)  ENGINE = InnoDB  DEFAULT CHARACTER SET = utf8;-- ------------------------------------------------------- Table `#__redshopb_webservice_permission_item_xref`-- -----------------------------------------------------DROP TABLE IF EXISTS `#__redshopb_webservice_permission_item_xref` ;CREATE TABLE IF NOT EXISTS `#__redshopb_webservice_permission_item_xref` (  `item_id` INT(11) NOT NULL COMMENT 'It depends on the webservice permission scope, it may be product ID or category ID or some other item ID',  `scope` VARCHAR(255) NOT NULL DEFAULT 'product',  `webservice_permission_id` INT(10) UNSIGNED NOT NULL,  PRIMARY KEY (`item_id`, `webservice_permission_id`),  INDEX `#__rs_webperite_fk1` (`webservice_permission_id` ASC),  CONSTRAINT `#__rs_webperite_fk1`  FOREIGN KEY (`webservice_permission_id`)  REFERENCES `#__redshopb_webservice_permission` (`id`)    ON DELETE CASCADE    ON UPDATE CASCADE)  ENGINE = InnoDB  DEFAULT CHARACTER SET = utf8;SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;