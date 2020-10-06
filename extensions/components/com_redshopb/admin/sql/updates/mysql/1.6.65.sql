SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;

-- -----------------------------------------------------
-- Table `#__redshopb_field`
-- -----------------------------------------------------

ALTER TABLE `#__redshopb_field`
  ADD COLUMN `multiple_values` TINYINT(4) NOT NULL DEFAULT '0' AFTER `description`;

-- -----------------------------------------------------
-- Table `#__redshopb_field_data`
-- -----------------------------------------------------

ALTER TABLE `#__redshopb_field_data`
  ADD COLUMN `params` TEXT NULL;


INSERT INTO `#__redshopb_type` (`id`, `name`, `alias`, `value_type`, `field_name`) VALUES
  (12, 'Documents', 'documents', 'string_value', 'mediaRedshopb'),
  (13, 'Videos', 'videos', 'string_value', 'mediaRedshopb'),
  (14, 'Images', 'field-images', 'string_value', 'mediaRedshopb')
;

UPDATE `#__redshopb_field` SET `alias` = CONCAT(`scope`, '-', `alias`);

SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
