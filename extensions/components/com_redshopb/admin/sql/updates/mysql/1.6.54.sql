SET FOREIGN_KEY_CHECKS = 0;

ALTER TABLE `#__redshopb_offer`
	DROP FOREIGN KEY `#__rs_offer_fk7`;

ALTER TABLE `#__redshopb_offer`
	DROP INDEX `#__rs_offer_fk7`;

ALTER TABLE `#__redshopb_offer`
 	ADD CONSTRAINT `#__rs_offer_fk7` FOREIGN KEY (`vendor_id`) REFERENCES `#__redshopb_company` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE;

SET FOREIGN_KEY_CHECKS = 1;