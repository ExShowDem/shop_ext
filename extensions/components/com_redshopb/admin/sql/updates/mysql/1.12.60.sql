SET FOREIGN_KEY_CHECKS=0;

INSERT INTO `#__redshopb_template` (`name`, `alias`, `template_group`, `scope`, `content`, `state`, `default`, `editable`, `checked_out`, `checked_out_time`, `created_by`, `created_date`, `modified_by`, `modified_date`, `params`, `description`) VALUES
  ('User notify after register', 'user-approve-after-register', 'email', 'user-approve-after-register', '', 1, 1, 0, NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00', '', 'COM_REDSHOPB_TEMPLATE_USER_NOTIFY_AFTER_REGISTER');

SET FOREIGN_KEY_CHECKS=1;