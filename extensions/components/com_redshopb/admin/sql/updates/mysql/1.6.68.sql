SET FOREIGN_KEY_CHECKS = 0;

ALTER TABLE `#__redshopb_offer` CHANGE `status` `status` ENUM('requested','sent','accepted','rejected','ordered','created') NOT NULL;

SET FOREIGN_KEY_CHECKS = 1;
