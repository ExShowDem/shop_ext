SET FOREIGN_KEY_CHECKS = 0;

-- -----------------------------------------------------
-- Table `#__redshopb_company`
-- -----------------------------------------------------
ALTER TABLE `#__redshopb_company`
  CHANGE `send_mail_on_order` `send_mail_on_order` TINYINT(1) NOT NULL DEFAULT '1'
  COMMENT 'If enabled, this company admins will receive emails on order placing';

SET FOREIGN_KEY_CHECKS = 1;
