SET FOREIGN_KEY_CHECKS = 0;

INSERT INTO `#__redshopb_template` (`name`, `alias`, `template_group`, `scope`, `content`, `state`, `default`, `editable`, `checked_out`, `checked_out_time`, `created_by`, `created_date`, `modified_by`, `modified_date`, `params`) VALUES
  ('Admin approval email', 'admin-approval', 'email', 'admin-approval', '', 1, 1, 0, NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00', ''),
  ('User approved email', 'user-approved', 'email', 'user-approved', '', 1, 1, 0, NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00', '');

SET FOREIGN_KEY_CHECKS = 1;
