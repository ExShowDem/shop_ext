-- New defaults per role
UPDATE
	`#__redshopb_role_type`
SET
	`allowed_rules` = '["core.manage","core.create","core.edit","core.edit.state","core.edit.own","core.delete","redshopb.company.manage.own","redshopb.user.manage.own","redshopb.department.manage.own","redshopb.wardrobe.manage.own","redshopb.order.manage.own","redshopb.order.place","redshopb.user.points","redshopb.user.points.own","redshopb.address.manage.own"]',
    `allowed_rules_company` = '["redshopb.company.manage","redshopb.user.manage","redshopb.department.manage","redshopb.wardrobe.manage","redshopb.order.manage","redshopb.user.view","redshopb.user.points","redshopb.company.view","redshopb.department.view","redshopb.wardrobe.view","redshopb.order.view","redshopb.order.impersonate","redshopb.order.history","redshopb.order.comment","redshopb.order.addrequisition","redshopb.order.statusupdate","redshopb.layout.view","redshopb.layout.manage","redshopb.address.manage","redshopb.address.view","redshopb.user.negativewallet"]'
WHERE
	`id` = 2;

UPDATE
	`#__redshopb_role_type`
SET
	`allowed_rules` = '["core.manage","core.create","core.edit","core.edit.state","core.edit.own","redshopb.user.manage.own","redshopb.department.manage.own","redshopb.order.manage.own","redshopb.order.place","redshopb.address.manage.own"]',
    `allowed_rules_department` = '["redshopb.user.manage","redshopb.department.manage","redshopb.order.manage","redshopb.user.view","redshopb.user.points","redshopb.department.view","redshopb.order.view","redshopb.order.impersonate","redshopb.order.history","redshopb.order.comment","redshopb.order.addrequisition","redshopb.order.statusupdate","redshopb.address.manage","redshopb.address.view"]'
WHERE
	`id` = 3;

UPDATE
	`#__redshopb_role_type`
SET
	`allowed_rules` = '["core.manage","core.create","core.edit","core.edit.own","redshopb.order.place","redshopb.address.manage.own"]',
    `allowed_rules_company` = '["redshopb.company.view","redshopb.order.impersonate","redshopb.order.history","redshopb.order.view","redshopb.department.view","redshopb.order.statusupdate"]'
WHERE
	`id` = 4;

UPDATE
	`#__redshopb_role_type`
SET
	`allowed_rules` = '["core.manage","core.create","core.edit","core.edit.own","redshopb.order.place","redshopb.address.manage.own"]',
    `allowed_rules_own_company` = '["redshopb.company.view","redshopb.order.impersonate","redshopb.order.history","redshopb.order.view"]'
WHERE
	`id` = 5;

UPDATE
	`#__redshopb_role_type`
SET
	`allowed_rules` = '["core.manage","core.create","core.edit","core.edit.own","redshopb.address.manage.own","redshopb.order.manage.own","redshopb.order.place"]'
WHERE
	`id` = 6;

-- Deleting old defaults for simple rules, for order.manage, address.manage, order.place and order.impersonate
DELETE FROM
	`#__redshopb_acl_simple_access_xref`
WHERE
	`simple_access_id` IN
	(
		SELECT
			`id`
		FROM
			`#__redshopb_acl_access`
		WHERE
			`name` IN ('redshopb.order.manage','redshopb.address.manage','redshopb.order.place','redshopb.order.impersonate')
	);

-- Swaps order.place and order.impersonate grants
DELETE FROM
	`#__redshopb_acl_rule`
WHERE
	`access_id` IN
	(
		SELECT
			`id`
		FROM
			`#__redshopb_acl_access`
		WHERE
			`name` IN ('redshopb.order.impersonate')
	);

UPDATE `#__redshopb_acl_rule`
SET `access_id` =
	(
		SELECT
			`id`
		FROM
			`#__redshopb_acl_access`
		WHERE
			`name` IN ('redshopb.order.impersonate')
	)
WHERE
`access_id` =
	(
		SELECT
			`id`
		FROM
			`#__redshopb_acl_access`
		WHERE
			`name` IN ('redshopb.order.place')
	);

INSERT INTO `#__redshopb_acl_rule` (`access_id`, `role_id`, `joomla_asset_id`, `granted`)
SELECT
	`a`.`id` AS `access_id`, `r`.`id` AS `role_id`, `asset`.`id` AS `joomla_asset_id`, 1 AS `granted`
FROM
	`#__redshopb_acl_access` AS `a`,
	`#__redshopb_role` AS `r`,
	`#__assets` AS `asset`
WHERE
	`a`.`name` = 'redshopb.order.place'
	AND `asset`.`name` = 'com_redshopb';

-- Removes old order and address grants from employees
DELETE
FROM
	`#__redshopb_acl_rule`
WHERE
	EXISTS (
		SELECT
			1
		FROM
			`#__redshopb_role` AS `r`
			INNER JOIN `#__redshopb_role_type` AS `rt` ON `rt`.`id` = `r`.`role_type_id`,
			`#__redshopb_acl_access` AS `a`
		WHERE
			`rt`.`type` = 'employee'
			AND `a`.`name` IN ('redshopb.order.manage', 'redshopb.order.view', 'redshopb.address.manage', 'redshopb.address.view')
			AND `#__redshopb_acl_rule`.`role_id` = `r`.`id`
			AND `#__redshopb_acl_rule`.`access_id` = `a`.`id`
 	);

-- New order grants for employees
INSERT INTO `#__redshopb_acl_rule` (`access_id`, `role_id`, `joomla_asset_id`, `granted`)
SELECT
	`a`.`id` AS `access_id`, `r`.`id` AS `role_id`, `asset`.`id` AS `joomla_asset_id`, 1 AS `granted`
FROM
	`#__redshopb_acl_access` AS `a`,
	`#__assets` AS `asset`,
	`#__redshopb_role` AS `r`
		INNER JOIN `#__redshopb_role_type` AS `rt` ON `rt`.`id` = `r`.`role_type_id`
WHERE
	`a`.`name` = 'redshopb.order.manage.own'
	AND `asset`.`name` = 'com_redshopb'
	AND `rt`.`type` = 'employee';

-- New default simple access for order.place, order.manage, address.manage and order.impersonate
INSERT INTO `#__redshopb_acl_simple_access_xref` (`simple_access_id`, `access_id`, `role_type_id`, `scope`)
	SELECT `sa`.`id`, `a`.`id`, `rt`.`id`, 'company'
	FROM `#__redshopb_acl_access` AS `sa`, `#__redshopb_acl_access` AS `a`, `#__redshopb_role_type` AS `rt`
	WHERE `sa`.`name` = 'redshopb.order.manage' AND `a`.`name` = 'redshopb.order.manage' AND `rt`.`type` IN ('admin', 'sales');
INSERT INTO `#__redshopb_acl_simple_access_xref` (`simple_access_id`, `access_id`, `role_type_id`, `scope`)
	SELECT `sa`.`id`, `a`.`id`, `rt`.`id`, 'global'
	FROM `#__redshopb_acl_access` AS `sa`, `#__redshopb_acl_access` AS `a`, `#__redshopb_role_type` AS `rt`
	WHERE `sa`.`name` = 'redshopb.order.manage' AND `a`.`name` = 'redshopb.order.manage.own' AND `rt`.`type` IN ('admin', 'sales');
INSERT INTO `#__redshopb_acl_simple_access_xref` (`simple_access_id`, `access_id`, `role_type_id`, `scope`)
	SELECT `sa`.`id`, `a`.`id`, `rt`.`id`, 'company'
	FROM `#__redshopb_acl_access` AS `sa`, `#__redshopb_acl_access` AS `a`, `#__redshopb_role_type` AS `rt`
	WHERE `sa`.`name` = 'redshopb.order.manage' AND `a`.`name` = 'redshopb.order.view' AND `rt`.`type` IN ('admin', 'sales', 'purchaser');

INSERT INTO `#__redshopb_acl_simple_access_xref` (`simple_access_id`, `access_id`, `role_type_id`, `scope`)
	SELECT `sa`.`id`, `a`.`id`, `rt`.`id`, 'department'
	FROM `#__redshopb_acl_access` AS `sa`, `#__redshopb_acl_access` AS `a`, `#__redshopb_role_type` AS `rt`
	WHERE `sa`.`name` = 'redshopb.order.manage' AND `a`.`name` = 'redshopb.order.manage' AND `rt`.`type` IN ('hod', 'purchaser');
INSERT INTO `#__redshopb_acl_simple_access_xref` (`simple_access_id`, `access_id`, `role_type_id`, `scope`)
	SELECT `sa`.`id`, `a`.`id`, `rt`.`id`, 'global'
	FROM `#__redshopb_acl_access` AS `sa`, `#__redshopb_acl_access` AS `a`, `#__redshopb_role_type` AS `rt`
	WHERE `sa`.`name` = 'redshopb.order.manage' AND `a`.`name` = 'redshopb.order.manage.own' AND `rt`.`type` IN ('hod', 'purchaser', 'employee');
INSERT INTO `#__redshopb_acl_simple_access_xref` (`simple_access_id`, `access_id`, `role_type_id`, `scope`)
	SELECT `sa`.`id`, `a`.`id`, `rt`.`id`, 'department'
	FROM `#__redshopb_acl_access` AS `sa`, `#__redshopb_acl_access` AS `a`, `#__redshopb_role_type` AS `rt`
	WHERE `sa`.`name` = 'redshopb.order.manage' AND `a`.`name` = 'redshopb.order.view' AND `rt`.`type` IN ('hod');


INSERT INTO `#__redshopb_acl_simple_access_xref` (`simple_access_id`, `access_id`, `role_type_id`, `scope`)
	SELECT `sa`.`id`, `a`.`id`, `rt`.`id`, 'company'
	FROM `#__redshopb_acl_access` AS `sa`, `#__redshopb_acl_access` AS `a`, `#__redshopb_role_type` AS `rt`
	WHERE `sa`.`name` = 'redshopb.address.manage' AND `a`.`name` = 'redshopb.address.manage' AND `rt`.`type` IN ('admin', 'sales');
INSERT INTO `#__redshopb_acl_simple_access_xref` (`simple_access_id`, `access_id`, `role_type_id`, `scope`)
	SELECT `sa`.`id`, `a`.`id`, `rt`.`id`, 'global'
	FROM `#__redshopb_acl_access` AS `sa`, `#__redshopb_acl_access` AS `a`, `#__redshopb_role_type` AS `rt`
	WHERE `sa`.`name` = 'redshopb.address.manage' AND `a`.`name` = 'redshopb.address.manage.own' AND `rt`.`type` IN ('admin', 'sales');
INSERT INTO `#__redshopb_acl_simple_access_xref` (`simple_access_id`, `access_id`, `role_type_id`, `scope`)
	SELECT `sa`.`id`, `a`.`id`, `rt`.`id`, 'company'
	FROM `#__redshopb_acl_access` AS `sa`, `#__redshopb_acl_access` AS `a`, `#__redshopb_role_type` AS `rt`
	WHERE `sa`.`name` = 'redshopb.address.manage' AND `a`.`name` = 'redshopb.address.view' AND `rt`.`type` IN ('admin', 'sales');

INSERT INTO `#__redshopb_acl_simple_access_xref` (`simple_access_id`, `access_id`, `role_type_id`, `scope`)
	SELECT `sa`.`id`, `a`.`id`, `rt`.`id`, 'department'
	FROM `#__redshopb_acl_access` AS `sa`, `#__redshopb_acl_access` AS `a`, `#__redshopb_role_type` AS `rt`
	WHERE `sa`.`name` = 'redshopb.address.manage' AND `a`.`name` = 'redshopb.address.manage' AND `rt`.`type` IN ('hod', 'purchaser');
INSERT INTO `#__redshopb_acl_simple_access_xref` (`simple_access_id`, `access_id`, `role_type_id`, `scope`)
	SELECT `sa`.`id`, `a`.`id`, `rt`.`id`, 'global'
	FROM `#__redshopb_acl_access` AS `sa`, `#__redshopb_acl_access` AS `a`, `#__redshopb_role_type` AS `rt`
	WHERE `sa`.`name` = 'redshopb.address.manage' AND `a`.`name` = 'redshopb.address.manage.own' AND `rt`.`type` IN ('hod', 'purchaser', 'employee');
INSERT INTO `#__redshopb_acl_simple_access_xref` (`simple_access_id`, `access_id`, `role_type_id`, `scope`)
	SELECT `sa`.`id`, `a`.`id`, `rt`.`id`, 'department'
	FROM `#__redshopb_acl_access` AS `sa`, `#__redshopb_acl_access` AS `a`, `#__redshopb_role_type` AS `rt`
	WHERE `sa`.`name` = 'redshopb.address.manage' AND `a`.`name` = 'redshopb.address.view' AND `rt`.`type` IN ('hod', 'purchaser');


INSERT INTO `#__redshopb_acl_simple_access_xref` (`simple_access_id`, `access_id`, `role_type_id`, `scope`)
	SELECT `sa`.`id`, `a`.`id`, `rt`.`id`, 'company'
	FROM `#__redshopb_acl_access` AS `sa`, `#__redshopb_acl_access` AS `a`, `#__redshopb_role_type` AS `rt`
	WHERE `sa`.`name` = 'redshopb.order.impersonate' AND `a`.`name` = 'redshopb.order.impersonate' AND `rt`.`type` IN ('admin', 'sales', 'purchaser');

INSERT INTO `#__redshopb_acl_simple_access_xref` (`simple_access_id`, `access_id`, `role_type_id`, `scope`)
	SELECT `sa`.`id`, `a`.`id`, `rt`.`id`, 'department'
	FROM `#__redshopb_acl_access` AS `sa`, `#__redshopb_acl_access` AS `a`, `#__redshopb_role_type` AS `rt`
	WHERE `sa`.`name` = 'redshopb.order.impersonate' AND `a`.`name` = 'redshopb.order.impersonate' AND `rt`.`type` IN ('hod', 'employee');

INSERT INTO `#__redshopb_acl_simple_access_xref` (`simple_access_id`, `access_id`, `role_type_id`, `scope`)
	SELECT `sa`.`id`, `a`.`id`, `rt`.`id`, 'global'
	FROM `#__redshopb_acl_access` AS `sa`, `#__redshopb_acl_access` AS `a`, `#__redshopb_role_type` AS `rt`
	WHERE `sa`.`name` = 'redshopb.order.place' AND `a`.`name` = 'redshopb.order.place' AND `rt`.`type` IN ('admin', 'sales');

INSERT INTO `#__redshopb_acl_simple_access_xref` (`simple_access_id`, `access_id`, `role_type_id`, `scope`)
	SELECT `sa`.`id`, `a`.`id`, `rt`.`id`, 'global'
	FROM `#__redshopb_acl_access` AS `sa`, `#__redshopb_acl_access` AS `a`, `#__redshopb_role_type` AS `rt`
	WHERE `sa`.`name` = 'redshopb.order.place' AND `a`.`name` = 'redshopb.order.place' AND `rt`.`type` IN ('hod', 'purchaser', 'employee');
