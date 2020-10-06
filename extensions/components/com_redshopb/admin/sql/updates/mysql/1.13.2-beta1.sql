-- -----------------------------------------------------
-- Table `#__redshopb_order`
-- -----------------------------------------------------
ALTER TABLE `#__redshopb_order` ADD `token` VARCHAR(32) NULL DEFAULT NULL AFTER `ip_address`;
