SET FOREIGN_KEY_CHECKS = 0;

-- -----------------------------------------------------
-- Table `#__redshopb_user`
-- -----------------------------------------------------
ALTER TABLE `#__redshopb_user`
	ADD COLUMN `image` VARCHAR(255) NULL AFTER `employee_number`;

SET FOREIGN_KEY_CHECKS = 1;
