UPDATE `#__redshopb_cron` SET
 `items_process_step` = 100
 WHERE `name` IN ('GetFielddata', 'GetProductImages', 'GetProductDescriptions') AND `plugin` = 'webservice';
