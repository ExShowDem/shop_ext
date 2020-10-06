SET FOREIGN_KEY_CHECKS=0;

UPDATE `#__redshopb_country` SET `name` = CONCAT('COM_REDSHOPB_COUNTRY_', UPPER(`alpha3`)) WHERE 1;
DROP TABLE IF EXISTS `#__redshopb_country_rctranslations`;
SET @tableId = (SELECT `id` FROM `#__redcore_translation_tables` WHERE `name` = '#__redshopb_country');
DELETE FROM `#__redcore_translation_tables` WHERE `id` = @tableId;
DELETE FROM `#__redcore_translation_columns` WHERE `translation_table_id` = @tableId;

SET FOREIGN_KEY_CHECKS=1;
