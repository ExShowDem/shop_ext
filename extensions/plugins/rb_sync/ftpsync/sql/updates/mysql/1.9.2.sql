UPDATE `#__redshopb_cron` SET
 `items_process_step` = 5000,
 `is_continuous` = 0
 WHERE `name` = 'FTPSync' AND `plugin` = 'ftpsync'
;
