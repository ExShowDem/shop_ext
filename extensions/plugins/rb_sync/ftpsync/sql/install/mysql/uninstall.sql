SET FOREIGN_KEY_CHECKS = 0;

DELETE FROM `#__redshopb_cron` WHERE `plugin` = 'ftpsync' AND `name` IN (
'FTPSync'
);

SET FOREIGN_KEY_CHECKS = 1;
