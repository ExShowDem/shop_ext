SET FOREIGN_KEY_CHECKS = 0;

ALTER TABLE `#__redshopb_cron`
 ADD `last_status_messages` LONGTEXT NOT NULL AFTER `offset_time`,
 ADD `items_total` INT(11) NOT NULL DEFAULT '0' AFTER `offset_time`,
 ADD `items_processed` INT(11) NOT NULL DEFAULT '0' AFTER `offset_time`,
 ADD `items_process_step` INT(11) NOT NULL DEFAULT '0' AFTER `offset_time`,
 ADD `is_continuous` TINYINT(4) NOT NULL DEFAULT '1' AFTER `offset_time`;

ALTER TABLE `#__redshopb_sync`
 ADD `hash_key` VARCHAR(100) NOT NULL DEFAULT '';

ALTER TABLE `#__redshopb_product`
	DROP FOREIGN KEY `#__rs_prod_fk6`;

ALTER TABLE `#__redshopb_product`
 	ADD CONSTRAINT `#__rs_prod_fk6`
    FOREIGN KEY (`unit_measure_id`)
    REFERENCES `#__redshopb_unit_measure` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE;

UPDATE `#__redshopb_cron` SET `next_start` = '0000-00-00 00:00:00', `checked_out` = 0, `execute_sync` = 0;

SET FOREIGN_KEY_CHECKS = 1;