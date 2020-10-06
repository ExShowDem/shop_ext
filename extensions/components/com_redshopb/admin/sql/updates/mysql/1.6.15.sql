SET FOREIGN_KEY_CHECKS = 0;
SET UNIQUE_CHECKS = 0;

UPDATE `#__redshopb_role_type`
SET
	`allowed_rules` = REPLACE(`allowed_rules`, 'redshopb.wardrobe', 'redshopb.collection'),
	`allowed_rules_main_company` = REPLACE(`allowed_rules_main_company`, 'redshopb.wardrobe', 'redshopb.collection'),
	`allowed_rules_customers` = REPLACE(`allowed_rules_customers`, 'redshopb.wardrobe', 'redshopb.collection'),
	`allowed_rules_company` = REPLACE(`allowed_rules_company`, 'redshopb.wardrobe', 'redshopb.collection'),
	`allowed_rules_own_company` = REPLACE(`allowed_rules_own_company`, 'redshopb.wardrobe', 'redshopb.collection'),
	`allowed_rules_department` = REPLACE(`allowed_rules_department`, 'redshopb.wardrobe', 'redshopb.collection');

UPDATE `#__redshopb_acl_access`
SET
	`name` = replace(`name`, 'redshopb.wardrobe', 'redshopb.collection'),
	`title` = replace(`title`, 'COM_REDSHOP_ACTION_WARDROBE', 'COM_REDSHOP_ACTION_COLLECTION'),
	`description` = replace(`description`, 'COM_REDSHOP_ACTION_WARDROBE', 'COM_REDSHOP_ACTION_COLLECTION')
WHERE
	`name` like 'redshopb.wardrobe%'
	OR `title` like 'COM_REDSHOP_ACTION_WARDROBE%'
	OR `description` like 'COM_REDSHOP_ACTION_WARDROBE%';

SET FOREIGN_KEY_CHECKS = 1;
SET UNIQUE_CHECKS = 1;
