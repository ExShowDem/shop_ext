SET FOREIGN_KEY_CHECKS = 0;

-- -----------------------------------------------------
-- Table `#__redshopb_field_data`
-- -----------------------------------------------------
ALTER TABLE `#__redshopb_field_data`
  ADD INDEX `idx_value` (`field_value` ASC);

ALTER TABLE `#__redshopb_field_data`
  ADD INDEX `idx_value_string` (`string_value` (255) ASC);

ALTER TABLE `#__redshopb_field_data`
  ADD INDEX `idx_value_int` (`int_value` ASC);

ALTER TABLE `#__redshopb_field_data`
  ADD INDEX `idx_value_float` (`float_value` ASC);

ALTER TABLE `#__redshopb_field_data`
  ADD INDEX `idx_value_text` (`text_value` (255) ASC);

SET FOREIGN_KEY_CHECKS = 1;