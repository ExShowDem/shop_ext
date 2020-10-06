UPDATE `#__redshopb_cron`
SET `parent_alias` = 'pim-getcategory'
WHERE `plugin` = 'pim' AND `name` = 'GetCategoryImage';

DELETE FROM `#__redshopb_cron` WHERE `plugin` = 'pim' AND `name` IN (
'GetFilterField', 'GetFilterFieldset', 'GetDepartmentCode', 'GetBrands', 'GetStockUnits', 'GetProductType',
'GetCategory', 'GetProduct', 'GetCategoryImage', 'GetColor', 'GetSize', 'GetDiameterInch'
);

INSERT INTO `#__redshopb_cron` (`name`, `plugin`, `state`, `start_time`, `finish_time`, `next_start`, `alias`, `parent_alias`, `execute_sync`, `mask_time`, `offset_time`, `params`, `checked_out`, `checked_out_time`) VALUES
  ('GetFilterField', 'pim', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'pim-getfilterfield', 'ftpsync-ftpsync', 0, 'Y-m-d H:00:00', '+1 hour', '', 0, '0000-00-00 00:00:00'),
  ('GetFilterFieldset', 'pim', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'pim-getfilterfieldset', 'pim-getfilterfield', 0, 'Y-m-d H:00:00', '+1 hour', '', 0, '0000-00-00 00:00:00'),
  ('GetDepartmentCode', 'pim', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'pim-getdepartmentcode', 'pim-getfilterfield', 0, 'Y-m-d H:00:00', '+1 hour', '', 0, '0000-00-00 00:00:00'),
  ('GetBrands', 'pim', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'pim-getbrands', 'pim-getdepartmentcode', 0, 'Y-m-d H:00:00', '+1 hour', '', 0, '0000-00-00 00:00:00'),
  ('GetStockUnits', 'pim', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'pim-getstockunits', 'pim-getbrands', 0, 'Y-m-d H:00:00', '+1 hour', '', 0, '0000-00-00 00:00:00'),
  ('GetProductType', 'pim', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'pim-getproducttype', 'pim-getstockunits', 0, 'Y-m-d H:00:00', '+1 hour', '', 0, '0000-00-00 00:00:00'),
  ('GetCategory', 'pim', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'pim-getcategory', 'pim-getproducttype', 0, 'Y-m-d H:00:00', '+1 hour', '', 0, '0000-00-00 00:00:00'),
  ('GetProduct', 'pim', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'pim-getproduct', 'pim-getcategory', 0, 'Y-m-d H:00:00', '+1 hour', '', 0, '0000-00-00 00:00:00'),
  ('GetCategoryImage', 'pim', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'getcategoryimage', 'pim-getcategory', 0, 'Y-m-d H:00:00', '+1 hour', '', 0, '0000-00-00 00:00:00'),
  ('GetColor', 'pim', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'pim-getcolor', 'pim-getfilterfield', 0, 'Y-m-d H:00:00', '+1 hour', '', 0, '0000-00-00 00:00:00'),
  ('GetSize', 'pim', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'pim-getsize', 'pim-getfilterfield', 0, 'Y-m-d H:00:00', '+1 hour', '', 0, '0000-00-00 00:00:00'),
  ('GetDiameterInch', 'pim', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'pim-getdiameterinch', 'pim-getfilterfield', 0, 'Y-m-d H:00:00', '+1 hour', '', 0, '0000-00-00 00:00:00')
	;

INSERT INTO `#__redshopb_field` (`id`, `scope`, `type_id`, `name`, `alias`, `description`, `ordering`, `state`, `searchable_frontend`, `searchable_backend`) VALUES
  (122, 'product', 5, 'Color', 'color', 'Product Color', 23, 1, 1, 1),
  (123, 'product', 5, 'Size', 'size', 'Product Size', 24, 1, 1, 1),
  (124, 'product', 5, 'DiameterInch', 'diameterinch', 'Product Diameter Inch', 25, 1, 1, 1),
  (125, 'product', 1, 'PressureLevels', 'pressurelevels', 'Product Pressure Levels', 26, 1, 1, 1),
  (126, 'product', 1, 'Seriess', 'seriess', 'Product Seriess', 27, 1, 1, 1),
  (127, 'product', 1, 'Applications', 'applications', 'Product Applications', 28, 1, 1, 1),
  (128, 'product', 1, 'DimensionInch', 'dimensioninch', 'Product Dimension Inch', 29, 1, 1, 1),
  (129, 'product', 1, 'DimensionMms', 'dimensionmms', 'Product Dimension Mms', 30, 1, 1, 1),
  (130, 'product', 1, 'Types', 'types', 'Product Types', 31, 1, 1, 1),
  (131, 'product', 1, 'ItemSalesUnit', 'itemsalesunit', 'Product Item Sales Unit', 32, 1, 1, 1),
  (132, 'product', 1, 'SalesPrice', 'salesprice', 'Product Sales Price', 33, 1, 1, 1),
  (133, 'product', 1, 'ItemStockUnit', 'itemstockunit', 'Product Item Stock Unit', 34, 1, 1, 1),
  (134, 'product', 1, 'AXstatus', 'axstatus', 'Product AX status', 35, 1, 1, 1),
  (135, 'product', 1, 'ItemName2', 'itemname2', 'Product Item Name 2', 36, 1, 1, 1),
  (136, 'product', 1, 'ItemName3', 'itemname3', 'Product Item Name 3', 37, 1, 1, 1),
  (137, 'product', 1, 'VVSno', 'vvsno', 'Product VVSno', 38, 1, 1, 1)
  ;
