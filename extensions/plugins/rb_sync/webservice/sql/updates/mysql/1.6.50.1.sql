SET FOREIGN_KEY_CHECKS = 0;

INSERT INTO `#__redshopb_cron` (`name`, `plugin`, `state`, `start_time`, `finish_time`, `next_start`, `alias`, `parent_alias`, `execute_sync`, `mask_time`, `offset_time`, `params`, `checked_out`, `checked_out_time`) VALUES
	('GetProductImages', 'webservice', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'webservice-getproductimages', 'webservice-getproducts', 0, 'Y-m-d 00:00:00', '+1 day', '', 0, '0000-00-00 00:00:00');

SET FOREIGN_KEY_CHECKS = 1;