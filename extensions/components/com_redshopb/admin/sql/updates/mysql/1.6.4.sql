SET FOREIGN_KEY_CHECKS = 0;

ALTER TABLE `#__redshopb_type`
  MODIFY `value_type` ENUM('string_value','float_value','int_value','text_value') NULL DEFAULT 'string_value' COMMENT 'Value field to use in the destination value table';

INSERT INTO `#__redshopb_type` (`id`, `name`, `alias`, `value_type`, `field_name`) VALUES
  (1, 'Textbox - string', 'textboxstring', 'string_value', 'rText'),
  (2, 'Textbox - float', 'textboxfloat', 'float_value', 'rText'),
  (3, 'Textbox - int', 'textboxint', 'int_value', 'rText'),
  (4, 'Textbox - text', 'textboxtext', 'text_value', 'rText'),
  (5, 'Dropdown - text', 'dropdowntext', 'string_value', 'rList')
;

CREATE TABLE IF NOT EXISTS `#__redshopb_product_company_xref` (
  `product_id` INT(10) UNSIGNED NOT NULL,
  `company_id` INT(10) UNSIGNED NOT NULL,
  KEY `idx_common` (`product_id`, `company_id`),
  KEY `idx_company_id` (`company_id`),
  CONSTRAINT `#__redshopb_product_company_xref_ibfk_2` FOREIGN KEY (`company_id`) REFERENCES `#__redshopb_company` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `#__redshopb_product_company_xref_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `#__redshopb_product` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
)
  ENGINE =InnoDB
  DEFAULT CHARSET =utf8;

ALTER TABLE `#__redshopb_field`
  ADD `default_value` VARCHAR(255) NULL AFTER `description`;

SET FOREIGN_KEY_CHECKS = 1;
