UPDATE `#__redshopb_cron` SET
 `next_start` = '0000-00-00 00:00:00', `checked_out` = '0', `execute_sync` = '0', `items_processed` = '0'
 WHERE `plugin` = 'webservice' AND `name` IN ('GetProducts', 'GetFielddata', 'GetProductImages', 'GetProductDescriptions')
;

UPDATE `#__redshopb_sync` SET
 `hash_key` = ''
 WHERE `reference` IN ('erp.webservice.products', 'erp.webservice.product_images', 'erp.webservice.field_data', 'erp.webservice.product_descriptions')
;
