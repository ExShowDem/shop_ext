SET FOREIGN_KEY_CHECKS = 0;

ALTER TABLE `#__redshopb_field`
  DROP FOREIGN KEY `#__rs_field_fk3`;

ALTER TABLE `#__redshopb_field`
	ADD CONSTRAINT `#__rs_field_fk3`
  FOREIGN KEY (`field_value_xref_id`)
  REFERENCES `#__redshopb_field` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE;

SET FOREIGN_KEY_CHECKS = 1;
