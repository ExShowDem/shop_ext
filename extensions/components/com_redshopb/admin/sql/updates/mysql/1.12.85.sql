SET FOREIGN_KEY_CHECKS=0;

UPDATE `#__redshopb_order` SET `delivery_address_id` = NULL WHERE `delivery_address_id` NOT IN (SELECT `id` FROM `#__redshopb_address`);

SET FOREIGN_KEY_CHECKS=1;
