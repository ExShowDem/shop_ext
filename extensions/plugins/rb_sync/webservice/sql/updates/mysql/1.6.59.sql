UPDATE `#__redshopb_cron`
SET `mask_time`='Y-m-d H:i:00',`offset_time`='+ 15 minutes'
WHERE `plugin`='webservice';