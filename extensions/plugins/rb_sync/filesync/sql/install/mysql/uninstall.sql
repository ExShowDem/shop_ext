SET FOREIGN_KEY_CHECKS = 0;

DELETE FROM `#__redshopb_cron` WHERE `plugin` = 'filesync';

SET FOREIGN_KEY_CHECKS = 1;
