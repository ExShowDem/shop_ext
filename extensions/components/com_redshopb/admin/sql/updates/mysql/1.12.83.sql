SET FOREIGN_KEY_CHECKS=0;

INSERT INTO `#__redshopb_acl_access` (`id`, `section_id`, `name`, `title`, `description`, `simple`) VALUES
  (null, 2, 'redshopb.order.import', 'COM_REDSHOP_ACTION_ORDER_IMPORT', 'COM_REDSHOP_ACTION_ORDER_IMPORT_DESC', 1);

INSERT INTO `#__redshopb_acl_simple_access_xref` (`simple_access_id`, `access_id`, `role_type_id`, `scope`)
	SELECT `sa`.`id`, `a`.`id`, `rt`.`id`, 'company'
	FROM `#__redshopb_acl_access` AS `sa`, `#__redshopb_acl_access` AS `a`, `#__redshopb_role_type` AS `rt`
	WHERE `sa`.`name` = 'redshopb.order.import' AND `a`.`name` = 'redshopb.order.import' AND `rt`.`type` IN ('admin', 'hod', 'sales', 'purchaser', 'employee');

SET FOREIGN_KEY_CHECKS=1;
