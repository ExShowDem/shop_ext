UPDATE `#__redshopb_template`
SET `scope` = 'activation-email', `editable` = 0, `default` = 1
WHERE `scope` = 'email' AND `template_group` = 'email' AND `alias` != 'generic-mail-template';

INSERT INTO `#__redshopb_template` (`name`, `alias`, `template_group`, `scope`, `content`, `state`, `default`, `editable`, `checked_out`, `checked_out_time`, `created_by`, `created_date`, `modified_by`, `modified_date`, `params`) VALUES
	('Product List', 'product-list', 'shop', 'product-list', '', 1, 1, 0, NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00', ''),
	('Product List Collection', 'product-list-collection', 'shop', 'product-list-collection', '', 1, 1, 0, NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00', ''),
	('Product List Style Grid', 'grid', 'shop', 'product-list-style', '', 1, 0, 0, NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00', ''),
	('Product List Style List', 'list', 'shop', 'product-list-style', '', 1, 1, 0, NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00', '');
