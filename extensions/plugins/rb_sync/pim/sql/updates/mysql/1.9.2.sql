UPDATE `#__redshopb_cron` SET
 `items_process_step` = 2000,
 `is_continuous` = 0
 WHERE `name` IN ('GetProduct', 'GetCategoryImage') AND `plugin` = 'pim'
;
