SET FOREIGN_KEY_CHECKS = 0;

INSERT INTO `#__redshopb_acl_access` (`id`, `section_id`, `name`, `title`, `description`, `simple`) VALUES
	(null, 1, 'redshopb.shopprice.view', 'COM_REDSHOP_ACTION_SHOPPRICE_VIEW', 'COM_REDSHOP_ACTION_SHOPPRICE_VIEW_DESC', 1);

INSERT INTO `#__redshopb_acl_simple_access_xref` (`simple_access_id`, `access_id`, `role_type_id`, `scope`)
	SELECT `sa`.`id`, `a`.`id`, `rt`.`id`, 'global'
	FROM `#__redshopb_acl_access` AS `sa`, `#__redshopb_acl_access` AS `a`, `#__redshopb_role_type` AS `rt`
	WHERE `sa`.`name` = 'redshopb.shopprice.view' AND `a`.`name` = 'redshopb.shopprice.view' AND `rt`.`type` IN ('admin', 'sales');

INSERT INTO `#__redshopb_acl_simple_access_xref` (`simple_access_id`, `access_id`, `role_type_id`, `scope`)
	SELECT `sa`.`id`, `a`.`id`, `rt`.`id`, 'global'
	FROM `#__redshopb_acl_access` AS `sa`, `#__redshopb_acl_access` AS `a`, `#__redshopb_role_type` AS `rt`
	WHERE `sa`.`name` = 'redshopb.shopprice.view' AND `a`.`name` = 'redshopb.shopprice.view' AND `rt`.`type` IN ('hod', 'purchaser', 'employee');

SET FOREIGN_KEY_CHECKS = 1;
