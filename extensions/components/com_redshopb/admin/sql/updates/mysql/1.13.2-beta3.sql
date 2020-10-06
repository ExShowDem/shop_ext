SET FOREIGN_KEY_CHECKS=0;

-- -----------------------------------------------------
-- Table `#__redshopb_product`
-- -----------------------------------------------------

ALTER TABLE `#__redshopb_product`
   ADD COLUMN `calc_type` INT(10) NOT NULL AFTER `volume`;

-- -----------------------------------------------------
-- Table `#__redshopb_calc_type`
-- -----------------------------------------------------

DROP TABLE IF EXISTS `#__redshopb_calc_type`;

CREATE TABLE IF NOT EXISTS `#__redshopb_calc_type` (
	`id`   INT(10) NOT NULL,
	`name` TEXT    NOT NULL,
	PRIMARY KEY (`id`)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;

INSERT INTO `#__redshopb_calc_type` (`id`, `name`) VALUES
  (1, 'weight'),
  (2, 'volume');

SET FOREIGN_KEY_CHECKS=1;
