INSERT INTO `#__redshopb_cron` (`name`, `plugin`, `state`, `start_time`, `finish_time`, `next_start`, `alias`, `parent_alias`, `execute_sync`, `mask_time`, `offset_time`, `params`, `checked_out`, `checked_out_time`) VALUES
	('GetWords', 'webservice', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'webservice-getwords', 'root', 0, 'Y-m-d 00:00:00', '+1 day', '', 0, '0000-00-00 00:00:00'),
	('SetWords', 'webservice', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'webservice-setwords', 'webservice-getwords', 0, 'Y-m-d 00:00:00', '+1 day', '', 0, '0000-00-00 00:00:00');