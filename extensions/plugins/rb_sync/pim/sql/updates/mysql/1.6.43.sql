DELETE FROM `#__redshopb_cron` WHERE `plugin` = 'pim';

INSERT INTO `#__redshopb_cron` (`name`, `plugin`, `state`, `start_time`, `finish_time`, `next_start`, `alias`, `parent_alias`, `execute_sync`, `mask_time`, `offset_time`, `params`, `checked_out`, `checked_out_time`) VALUES
  ('GetField', 'pim', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'pim-getfield', 'ftpsync-ftpsync', 0, 'Y-m-d H:00:00', '+1 hour', '', 0, '0000-00-00 00:00:00'),
  ('GetFieldValues', 'pim', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'pim-getfieldvalues', 'pim-getfield', 0, 'Y-m-d H:00:00', '+1 hour', '', 0, '0000-00-00 00:00:00'),
  ('GetFilterFieldset', 'pim', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'pim-getfilterfieldset', 'pim-getfieldvalues', 0, 'Y-m-d H:00:00', '+1 hour', '', 0, '0000-00-00 00:00:00'),
  ('GetDepartmentCode', 'pim', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'pim-getdepartmentcode', 'pim-getfilterfieldset', 0, 'Y-m-d H:00:00', '+1 hour', '', 0, '0000-00-00 00:00:00'),
  ('GetBrands', 'pim', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'pim-getbrands', 'pim-getdepartmentcode', 0, 'Y-m-d H:00:00', '+1 hour', '', 0, '0000-00-00 00:00:00'),
  ('GetStockUnits', 'pim', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'pim-getstockunits', 'pim-getbrands', 0, 'Y-m-d H:00:00', '+1 hour', '', 0, '0000-00-00 00:00:00'),
  ('GetProductType', 'pim', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'pim-getproducttype', 'pim-getstockunits', 0, 'Y-m-d H:00:00', '+1 hour', '', 0, '0000-00-00 00:00:00'),
  ('GetCategory', 'pim', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'pim-getcategory', 'pim-getproducttype', 0, 'Y-m-d H:00:00', '+1 hour', '', 0, '0000-00-00 00:00:00'),
  ('GetProduct', 'pim', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'pim-getproduct', 'pim-getcategory', 0, 'Y-m-d H:00:00', '+1 hour', '', 0, '0000-00-00 00:00:00'),
  ('GetCategoryImage', 'pim', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'getcategoryimage', 'pim-getcategory', 0, 'Y-m-d H:00:00', '+1 hour', '', 0, '0000-00-00 00:00:00')
	;

DELETE FROM `#__redshopb_field_data`;
DELETE FROM `#__redshopb_field_value`;
DELETE FROM `#__redshopb_filter_fieldset_xref`;

DELETE FROM `#__redshopb_field` WHERE `scope` = 'product';

INSERT INTO `#__redshopb_field` (`scope`, `type_id`, `name`, `alias`, `title`, `description`, `ordering`, `state`, `searchable_frontend`, `searchable_backend`) VALUES
  ('product', 4, 'ProductDescr2s', 'productdescr2s', 'Second product description', 'Second product description', 1, 1, 1, 1),
  ('product', 4, 'ProductDescr3s', 'productdescr3s', 'Third product description', 'Third product description', 2, 1, 1, 1),
  ('product', 1, 'ProductName2', 'productname2', 'Second name for the product', 'Second name for the product', 3, 1, 1, 1),
  ('product', 1, 'ProductName3', 'productname3', 'Third name for the product', 'Third name for the product', 4, 1, 1, 1)
;

DELETE FROM `#__redshopb_sync` WHERE `reference` IN ('erp.pim.color', 'erp.pim.diameterinch', 'erp.pim.size', 'erp.pim.filterField');
