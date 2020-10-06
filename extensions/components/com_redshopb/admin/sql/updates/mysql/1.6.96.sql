ALTER TABLE `#__redshopb_company`
  ADD COLUMN `stockroom_verification` TINYINT(1) NOT NULL DEFAULT '1' AFTER `show_price`;
