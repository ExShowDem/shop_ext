SET FOREIGN_KEY_CHECKS=0;

ALTER TABLE `#__redshopb_field` ADD `field_value_ordering` TINYINT(4) NOT NULL AFTER `ordering`;

SET FOREIGN_KEY_CHECKS=1;
