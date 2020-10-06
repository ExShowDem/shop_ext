ALTER TABLE `#__redshopb_template`
ADD `description` VARCHAR(255) NOT NULL DEFAULT '' AFTER `params`;

UPDATE `#__redshopb_template` SET `description` = 'COM_REDSHOPB_TEMPLATE_GENERIC_MAIL_TEMPLATE' WHERE `#__redshopb_template`.`id` = 1;
UPDATE `#__redshopb_template` SET `description` = 'COM_REDSHOPB_TEMPLATE_PRODUCT' WHERE `#__redshopb_template`.`id` = 2;
UPDATE `#__redshopb_template` SET `description` = 'COM_REDSHOPB_TEMPLATE_CATEGORY' WHERE `#__redshopb_template`.`id` = 3;
UPDATE `#__redshopb_template` SET `description` = 'COM_REDSHOPB_TEMPLATE_SEND_OFFER' WHERE `#__redshopb_template`.`id` = 4;
UPDATE `#__redshopb_template` SET `description` = 'COM_REDSHOPB_TEMPLATE_ACTIVATION_EMAIL' WHERE `#__redshopb_template`.`id` = 5;
UPDATE `#__redshopb_template` SET `description` = 'COM_REDSHOPB_TEMPLATE_PRODUCT_LIST' WHERE `#__redshopb_template`.`id` = 6;
UPDATE `#__redshopb_template` SET `description` = 'COM_REDSHOPB_TEMPLATE_PRODUCT_LIST_COLLECTION' WHERE `#__redshopb_template`.`id` = 7;
UPDATE `#__redshopb_template` SET `description` = 'COM_REDSHOPB_TEMPLATE_GRID' WHERE `#__redshopb_template`.`id` = 8;
UPDATE `#__redshopb_template` SET `description` = 'COM_REDSHOPB_TEMPLATE_LIST' WHERE `#__redshopb_template`.`id` = 9;
UPDATE `#__redshopb_template` SET `description` = 'COM_REDSHOPB_TEMPLATE_SEND_TO_FRIEND' WHERE `#__redshopb_template`.`id` = 10;
UPDATE `#__redshopb_template` SET `description` = 'COM_REDSHOPB_TEMPLATE_LIST_ELEMENT' WHERE `#__redshopb_template`.`id` = 11;
UPDATE `#__redshopb_template` SET `description` = 'COM_REDSHOPB_TEMPLATE_GRID_ELEMENT' WHERE `#__redshopb_template`.`id` = 12;
UPDATE `#__redshopb_template` SET `description` = 'COM_REDSHOPB_TEMPLATE_PRODUCT_PRINT' WHERE `#__redshopb_template`.`id` = 13;
UPDATE `#__redshopb_template` SET `description` = 'COM_REDSHOPB_TEMPLATE_USER_ADDED' WHERE `#__redshopb_template`.`id` = 14;
UPDATE `#__redshopb_template` SET `description` = 'COM_REDSHOPB_TEMPLATE_PAYMENT_STATUS_CHANGED' WHERE `#__redshopb_template`.`id` = 15;
UPDATE `#__redshopb_template` SET `description` = 'COM_REDSHOPB_TEMPLATE_ADMIN_APPROVED' WHERE `#__redshopb_template`.`id` = 16;
UPDATE `#__redshopb_template` SET `description` = 'COM_REDSHOPB_TEMPLATE_USER_APPROVED' WHERE `#__redshopb_template`.`id` = 17;
