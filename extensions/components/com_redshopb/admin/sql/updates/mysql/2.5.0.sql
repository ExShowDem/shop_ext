ALTER TABLE `#__redshopb_order_item`
   ADD COLUMN `offer_id` INT(10) UNSIGNED NULL DEFAULT NULL AFTER `stockroom_name`;

ALTER TABLE `#__redshopb_table_lock`
	ADD INDEX `#__rs_datalock_fk1_idx` (`locked_by` ASC);