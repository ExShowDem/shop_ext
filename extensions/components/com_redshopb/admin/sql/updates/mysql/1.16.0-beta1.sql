SET FOREIGN_KEY_CHECKS=0;

UPDATE `#__redshopb_type` SET `field_name` = 'text' WHERE `id` IN (1,2,3);

SET FOREIGN_KEY_CHECKS=1;
