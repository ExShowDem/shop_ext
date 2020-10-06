SET FOREIGN_KEY_CHECKS = 0;

UPDATE `#__redshopb_user` SET `use_company_email` = 2 WHERE `use_company_email` = 1;
UPDATE `#__redshopb_user` SET `use_company_email` = 1 WHERE `use_company_email` = 0;
UPDATE `#__redshopb_user` SET `use_company_email` = 0 WHERE `use_company_email` = 2;

SET FOREIGN_KEY_CHECKS = 1;
