SET FOREIGN_KEY_CHECKS = 0;

UPDATE `#__redshopb_cron`
SET `parent_alias` = 'root'
WHERE `name` = 'GetManufacturers' AND `plugin` = 'webservice';

UPDATE `#__redshopb_cron`
SET `parent_alias` = 'webservice-getfilterfieldset'
WHERE `name` = 'GetCategories' AND `plugin` = 'webservice';

UPDATE `#__redshopb_cron`
SET `parent_alias` = 'webservice-getcategories'
WHERE `name` = 'GetTags' AND `plugin` = 'webservice';

SET FOREIGN_KEY_CHECKS = 1;