SET FOREIGN_KEY_CHECKS = 0;

DELETE FROM `#__redshopb_cron` WHERE `plugin` = 'pim' AND `name` IN (
'GetField', 'GetFieldValues', 'GetFilterFieldset', 'GetDepartmentCode', 'GetBrands', 'GetStockUnits', 'GetProductType',
'GetCategory', 'GetProduct', 'GetCategoryImage'
);

DELETE FROM `#__redshopb_field` WHERE `scope` = 'product' AND `name` IN (
'ProductDescr2s', 'ProductDescr3s', 'ProductName2', 'ProductName3', 'Instructions', 'Securitysheet', 'Video'
);

SET FOREIGN_KEY_CHECKS = 1;
