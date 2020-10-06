SET FOREIGN_KEY_CHECKS = 0;

INSERT INTO `#__redshopb_cron` (`name`, `plugin`, `state`, `start_time`, `finish_time`, `next_start`, `alias`, `parent_alias`, `execute_sync`, `mask_time`, `offset_time`, `is_continuous`, `items_process_step`, `params`, `checked_out`, `checked_out_time`) VALUES
	('GetManufacturers', 'webservice', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'webservice-getmanufacturers', 'root', 0, 'Y-m-d H:i:00', '+ 15 minutes', 1, 0, '', 0, '0000-00-00 00:00:00'),
	('GetUnitsmeasure', 'webservice', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'webservice-getunitsmeasure', 'webservice-getmanufacturers', 0, 'Y-m-d H:i:00', '+ 15 minutes', 1, 0, '', 0, '0000-00-00 00:00:00'),
	('GetFields', 'webservice', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'webservice-getfields', 'webservice-getunitsmeasure', 0, 'Y-m-d H:i:00', '+ 15 minutes', 1, 0, '', 0, '0000-00-00 00:00:00'),
	('GetFieldvalues', 'webservice', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'webservice-getfieldvalues', 'webservice-getfields', 0, 'Y-m-d H:i:00', '+ 15 minutes', 1, 0, '', 0, '0000-00-00 00:00:00'),
	('GetFilterfieldset', 'webservice', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'webservice-getfilterfieldset', 'webservice-getfieldvalues', 0, 'Y-m-d H:i:00', '+ 15 minutes', 1, 0, '', 0, '0000-00-00 00:00:00'),
	('GetCategories', 'webservice', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'webservice-getcategories', 'webservice-getfilterfieldset', 0, 'Y-m-d H:i:00', '+ 15 minutes', 1, 0, '', 0, '0000-00-00 00:00:00'),
	('GetTags', 'webservice', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'webservice-gettags', 'webservice-getcategories', 0, 'Y-m-d H:i:00', '+ 15 minutes', 1, 0, '', 0, '0000-00-00 00:00:00'),
	('GetProducts', 'webservice', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'webservice-getproducts', 'webservice-gettags', 0, 'Y-m-d H:i:00', '+ 15 minutes', 1, 300, '', 0, '0000-00-00 00:00:00'),
	('GetFielddata', 'webservice', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'webservice-getfielddata', 'webservice-getproducts', 0, 'Y-m-d H:i:00', '+ 15 minutes', 1, 100, '', 0, '0000-00-00 00:00:00'),
	('GetProductImages', 'webservice', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'webservice-getproductimages', 'webservice-getproducts', 0, 'Y-m-d H:i:00', '+ 15 minutes', 1, 100, '', 0, '0000-00-00 00:00:00'),
	('GetProductDescriptions', 'webservice', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'webservice-getproductdescriptions', 'webservice-getproducts', 0, 'Y-m-d H:i:00', '+ 15 minutes', 1, 100, '', 0, '0000-00-00 00:00:00'),
	('GetWords', 'webservice', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'webservice-getwords', 'root', 0, 'Y-m-d H:i:00', '+ 15 minutes', 1, 0, '', 0, '0000-00-00 00:00:00'),
	('SetWords', 'webservice', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'webservice-setwords', 'webservice-getwords', 0, 'Y-m-d H:i:00', '+ 15 minutes', 1, 0, '', 0, '0000-00-00 00:00:00')
;

SET FOREIGN_KEY_CHECKS = 1;