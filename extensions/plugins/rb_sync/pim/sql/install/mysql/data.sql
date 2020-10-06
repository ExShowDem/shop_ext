SET FOREIGN_KEY_CHECKS = 0;

/* Insert "PIM GetFields" base on FTPSync or Root */
INSERT INTO `#__redshopb_cron` (`name`, `plugin`, `state`, `start_time`, `finish_time`, `next_start`, `alias`, `parent_alias`, `execute_sync`, `mask_time`, `offset_time`, `is_continuous`, `items_process_step`, `params`, `checked_out`, `checked_out_time`)
  SELECT 'GetField', 'pim', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'pim-getfield', 'ftpsync-ftpsync', 0, 'Y-m-d H:00:00', '+4 hour', 1, 0, '', 0, '0000-00-00 00:00:00'
    FROM DUAL
    WHERE EXISTS (SELECT * FROM `#__redshopb_cron` WHERE `alias` = 'ftpsync-ftpsync');

INSERT INTO `#__redshopb_cron` (`name`, `plugin`, `state`, `start_time`, `finish_time`, `next_start`, `alias`, `parent_alias`, `execute_sync`, `mask_time`, `offset_time`, `is_continuous`, `items_process_step`, `params`, `checked_out`, `checked_out_time`)
  SELECT 'GetField', 'pim', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'pim-getfield', 'root', 0, 'Y-m-d H:00:00', '+4 hour', 1, 0, '', 0, '0000-00-00 00:00:00'
    FROM DUAL
    WHERE NOT EXISTS (SELECT * FROM `#__redshopb_cron` WHERE `alias` = 'ftpsync-ftpsync');

/* Insert sub task of "PIM GetFields" */
INSERT INTO `#__redshopb_cron` (`name`, `plugin`, `state`, `start_time`, `finish_time`, `next_start`, `alias`, `parent_alias`, `execute_sync`, `mask_time`, `offset_time`, `is_continuous`, `items_process_step`, `params`, `checked_out`, `checked_out_time`) VALUES
  ('GetFieldValues', 'pim', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'pim-getfieldvalues', 'pim-getfield', 0, 'Y-m-d H:00:00', '+4 hour', 1, 0, '', 0, '0000-00-00 00:00:00'),
  ('GetFilterFieldset', 'pim', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'pim-getfilterfieldset', 'pim-getfieldvalues', 0, 'Y-m-d H:00:00', '+4 hour', 1, 0, '', 0, '0000-00-00 00:00:00'),
  ('GetDepartmentCode', 'pim', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'pim-getdepartmentcode', 'pim-getfilterfieldset', 0, 'Y-m-d H:00:00', '+4 hour', 1, 0, '', 0, '0000-00-00 00:00:00'),
  ('GetBrands', 'pim', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'pim-getbrands', 'pim-getdepartmentcode', 0, 'Y-m-d H:00:00', '+4 hour', 1, 0, '', 0, '0000-00-00 00:00:00'),
  ('GetStockUnits', 'pim', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'pim-getstockunits', 'pim-getbrands', 0, 'Y-m-d H:00:00', '+4 hour', 1, 0, '', 0, '0000-00-00 00:00:00'),
  ('GetProductType', 'pim', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'pim-getproducttype', 'pim-getstockunits', 0, 'Y-m-d H:00:00', '+4 hour', 1, 0, '', 0, '0000-00-00 00:00:00'),
  ('GetCategory', 'pim', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'pim-getcategory', 'pim-getproducttype', 0, 'Y-m-d H:00:00', '+4 hour', 1, 0, '', 0, '0000-00-00 00:00:00'),
  ('GetCategoryImage', 'pim', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'getcategoryimage', 'pim-getcategory', 0, 'Y-m-d H:00:00', '+4 hour', 0, 2000, '', 0, '0000-00-00 00:00:00'),
  ('GetProduct', 'pim', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'pim-getproduct', 'pim-getcategory', 0, 'Y-m-d H:00:00', '+4 hour', 0, 2000, '', 0, '0000-00-00 00:00:00')
	;

INSERT INTO `#__redshopb_field` (`scope`, `type_id`, `name`, `alias`, `title`, `description`, `ordering`, `state`, `searchable_frontend`, `searchable_backend`, `multiple_values`) VALUES
  ('product', 4, 'ProductDescr2s', 'product-productdescr2s', 'Second product description', 'Second product description', 1, 1, 1, 1, 0),
  ('product', 4, 'ProductDescr3s', 'product-productdescr3s', 'Third product description', 'Third product description', 2, 1, 1, 1, 0),
  ('product', 1, 'ProductName2', 'product-productname2', 'Second name for the product', 'Second name for the product', 3, 1, 1, 1, 0),
  ('product', 1, 'ProductName3', 'product-productname3', 'Third name for the product', 'Third name for the product', 4, 1, 1, 1, 0),
  ('product', 12, 'Instructions', 'product-instructions', 'Product Instructions', 'Product Instruction document files', 5, 1, 1, 1, 1),
  ('product', 12, 'Securitysheet', 'product-securitysheet', 'Product Security sheets', 'Product Security sheet document files', 6, 1, 1, 1, 1),
  ('product', 13, 'Video', 'product-video', 'Product videos', 'Product video files', 7, 1, 1, 1, 1)
;

INSERT INTO `#__redshopb_webservice_permission` (`scope`, `name`, `description`, `state`) VALUES
	('product', 'Plumbing (VVS)', 'Include all products that SKU begins with 1xxxxxx', 1),
	('product', 'Steel (Stål)', 'Include all products that SKU begins with 2xxxxxx and 102xxxx', 1),
	('product', 'Tools (Værktøj)', 'Include all products that SKU begins with 3xxxxxx', 1)
;

SET FOREIGN_KEY_CHECKS = 1;
