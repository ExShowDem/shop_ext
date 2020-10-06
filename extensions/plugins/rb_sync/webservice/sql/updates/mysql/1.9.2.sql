UPDATE `#__redshopb_cron` SET
 `items_process_step` = 200
 WHERE `name` IN ('GetFielddata', 'GetProductImages') AND `plugin` = 'webservice';

 UPDATE `#__redshopb_cron` SET
 `items_process_step` = 300,
 `is_continuous` = 1
 WHERE `name` IN ('GetProducts') AND `plugin` = 'webservice';

INSERT INTO `#__redshopb_cron` (`name`, `plugin`, `state`, `start_time`, `finish_time`, `next_start`, `alias`, `parent_alias`, `execute_sync`, `mask_time`, `offset_time`, `is_continuous`, `items_process_step`, `params`, `checked_out`, `checked_out_time`) VALUES
	('GetProductDescriptions', 'webservice', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'webservice-getproductdescriptions', 'webservice-getproducts', 0, 'Y-m-d H:i:00', '+ 15 minutes', 1, 200, '', 0, '0000-00-00 00:00:00');
