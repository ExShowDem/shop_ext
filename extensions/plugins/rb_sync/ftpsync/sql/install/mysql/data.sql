SET FOREIGN_KEY_CHECKS = 0;

INSERT INTO `#__redshopb_cron` (`name`, `plugin`, `state`, `start_time`, `finish_time`, `next_start`, `alias`, `parent_alias`, `execute_sync`, `mask_time`, `offset_time`, `is_continuous`, `items_process_step`, `params`, `checked_out`, `checked_out_time`) VALUES
	('FTPSync', 'ftpsync', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'ftpsync-ftpsync', 'root', 0, 'Y-m-d 00:00:00', '+1 day', 0, 5000, '', 0, '0000-00-00 00:00:00')
	;

SET FOREIGN_KEY_CHECKS = 1;
