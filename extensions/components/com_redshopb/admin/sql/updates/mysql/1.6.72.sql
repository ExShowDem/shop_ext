ALTER TABLE `#__redshopb_offer` CHANGE `status` `status`
ENUM('requested','sent','accepted','rejected','ordered','created','pending') NOT NULL;
