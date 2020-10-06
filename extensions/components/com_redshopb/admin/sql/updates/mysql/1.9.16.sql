SET FOREIGN_KEY_CHECKS = 0;

-- -----------------------------------------------------
-- Table `#__redshopb_user`
-- -----------------------------------------------------
ALTER TABLE `#__redshopb_field`
	ADD COLUMN `b2c` TINYINT(4) NOT NULL DEFAULT 0 AFTER `default_value`;

SET FOREIGN_KEY_CHECKS = 1;
