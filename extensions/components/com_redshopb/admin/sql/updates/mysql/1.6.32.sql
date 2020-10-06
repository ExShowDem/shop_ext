SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;

-- -----------------------------------------------------
-- Table `#__redshopb_order`
-- -----------------------------------------------------
CALL `#__redshopb_order_1_6_32`();

DROP PROCEDURE IF EXISTS `#__redshopb_order_1_6_32`;

ALTER TABLE `#__redshopb_order`
  ADD INDEX `#__rs_order_fk8` (`shipping_rate_id` ASC),
  ADD CONSTRAINT `#__rs_order_fk8`
    FOREIGN KEY (`shipping_rate_id`)
    REFERENCES `#__redshopb_shipping_rates` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION;

-- -----------------------------------------------------
-- Table `#__redshopb_shipping_rates`
-- -----------------------------------------------------
CALL `#__redshopb_shipping_rates_1_6_32`();

DROP PROCEDURE IF EXISTS `#__redshopb_shipping_rates_1_6_32`;

ALTER TABLE `#__redshopb_shipping_rates`
  MODIFY COLUMN `order_total_start` DECIMAL(10,2) NOT NULL DEFAULT '0.00',
  MODIFY COLUMN `price` DECIMAL(10,2) NOT NULL DEFAULT '0.00',
  ADD INDEX `#__rs_sr_config_fk_1` (`shipping_configuration_id` ASC),
  ADD CONSTRAINT `#__rs_sr_config_fk_1`
    FOREIGN KEY (`shipping_configuration_id`)
    REFERENCES `#__redshopb_shipping_configuration` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE;

SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
