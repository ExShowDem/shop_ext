SET FOREIGN_KEY_CHECKS = 0;

ALTER TABLE `#__redshopb_field_value`
  DROP FOREIGN KEY `#__rs_fvalue_fk1`;

ALTER TABLE `#__redshopb_field_value`
  ADD CONSTRAINT `#__rs_fvalue_fk1`
    FOREIGN KEY (`field_id`)
    REFERENCES `#__redshopb_field` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE;

ALTER TABLE `#__redshopb_field_data`
  DROP FOREIGN KEY `#__rs_fdata_fk1`;

ALTER TABLE `#__redshopb_field_data`
	ADD CONSTRAINT `#__rs_fdata_fk1`
    FOREIGN KEY (`field_id`)
    REFERENCES `#__redshopb_field` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE;

SET FOREIGN_KEY_CHECKS = 1;
