SET FOREIGN_KEY_CHECKS=0;

ALTER TABLE `#__redshopb_offer_item_xref`
   ADD COLUMN `params` TEXT NULL AFTER `total`;

SET FOREIGN_KEY_CHECKS=1;
