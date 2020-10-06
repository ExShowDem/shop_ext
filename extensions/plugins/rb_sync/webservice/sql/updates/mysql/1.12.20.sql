UPDATE `#__redshopb_cron` SET
 `start_time` = '0000-00-00 00:00:00',
 `finish_time` = '0000-00-00 00:00:00',
 `next_start` = '0000-00-00 00:00:00'
 WHERE `plugin` = 'webservice'
;
