SET FOREIGN_KEY_CHECKS = 0;

ALTER TABLE `#__redshopb_sync` CHANGE `serialize` `serialize` TEXT NOT NULL;

SET FOREIGN_KEY_CHECKS = 1;
