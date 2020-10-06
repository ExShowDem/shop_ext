SET FOREIGN_KEY_CHECKS = 0;

DELETE FROM `#__redshopb_cron` WHERE `plugin` = 'webservice' AND `name` IN (
'GetManufacturers', 'GetUnitsmeasure', 'GetFields', 'GetFieldvalues', 'GetFilterfieldset', 'GetCategories', 'GetTags',
'GetProducts', 'GetFielddata', 'GetProductImages', 'GetProductDescriptions', 'GetWords', 'SetWords'
);

SET FOREIGN_KEY_CHECKS = 1;
