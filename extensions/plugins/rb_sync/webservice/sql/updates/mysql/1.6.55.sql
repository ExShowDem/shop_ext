SET FOREIGN_KEY_CHECKS = 0;

INSERT INTO `#__redshopb_cron` (`name`, `plugin`, `state`, `start_time`, `finish_time`, `next_start`, `alias`, `parent_alias`, `execute_sync`, `mask_time`, `offset_time`, `params`, `checked_out`, `checked_out_time`) VALUES
	('GetManufacturers', 'webservice', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'webservice-getmanufacturers', 'webservice-getcategories', 0, 'Y-m-d 00:00:00', '+1 day', '', 0, '0000-00-00 00:00:00'),
	('GetUnitsmeasure', 'webservice', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'webservice-getunitsmeasure', 'webservice-getmanufacturers', 0, 'Y-m-d 00:00:00', '+1 day', '', 0, '0000-00-00 00:00:00'),
	('GetFields', 'webservice', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'webservice-getfields', 'webservice-getunitsmeasure', 0, 'Y-m-d 00:00:00', '+1 day', '', 0, '0000-00-00 00:00:00'),
	('GetFieldvalues', 'webservice', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'webservice-getfieldvalues', 'webservice-getfields', 0, 'Y-m-d 00:00:00', '+1 day', '', 0, '0000-00-00 00:00:00'),
	('GetFilterfieldset', 'webservice', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'webservice-getfilterfieldset', 'webservice-getfieldvalues', 0, 'Y-m-d 00:00:00', '+1 day', '', 0, '0000-00-00 00:00:00'),
	('GetFielddata', 'webservice', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'webservice-getfielddata', 'webservice-getproducts', 0, 'Y-m-d 00:00:00', '+1 day', '', 0, '0000-00-00 00:00:00');

UPDATE `#__redshopb_cron`
SET `parent_alias` = 'webservice-getfilterfieldset'
WHERE `name` = 'GetTags' AND `plugin` = 'webservice';

SET FOREIGN_KEY_CHECKS = 1;