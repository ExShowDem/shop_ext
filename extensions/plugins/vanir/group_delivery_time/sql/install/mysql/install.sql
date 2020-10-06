SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;

CREATE TABLE IF NOT EXISTS `#__redshopb_delivery_time_group` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`min_time` INT(11) NULL,
	`max_time` INT(11) NULL,
	`color` VARCHAR(11) NULL,
	`label` VARCHAR(255) NOT NULL DEFAULT '',
	PRIMARY KEY (`id`)
)
	ENGINE = InnoDB;

SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;