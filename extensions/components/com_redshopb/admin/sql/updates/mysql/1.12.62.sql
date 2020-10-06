SET FOREIGN_KEY_CHECKS=0;

DELETE FROM `#__redshopb_template`
  WHERE `template_group` = 'email'
    AND `scope` = 'payment-status-changed'
    AND `alias` = 'payment-status-changed';

SET FOREIGN_KEY_CHECKS=1;
