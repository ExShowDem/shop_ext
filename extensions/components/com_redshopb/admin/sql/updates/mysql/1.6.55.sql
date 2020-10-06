SET FOREIGN_KEY_CHECKS = 0;

-- -----------------------------------------------------
-- Table `#__redshopb_user`
-- -----------------------------------------------------
ALTER TABLE `#__redshopb_user`
  ADD COLUMN `send_email` TINYINT(4) NOT NULL DEFAULT '1' COMMENT 'Allows disabling B2B-related emails to this user' AFTER `use_company_email`;

UPDATE
	`#__redshopb_user`, `#__redshopb_sync`
SET
	`#__redshopb_user`.`employee_number` = `#__redshopb_sync`.`remote_key`
WHERE
	`#__redshopb_sync`.`reference` = 'ws.user'
	AND `#__redshopb_sync`.`local_id` = `#__redshopb_user`.`id`;

INSERT INTO `#__redshopb_sync` (`reference`, `local_id`, `remote_key`)
	SELECT
		'ws.user', `c`.`id`, `c`.`employee_number`
	FROM
		`#__redshopb_user` AS `c`
	WHERE
		NOT EXISTS (
			SELECT
				1
			FROM
				`#__redshopb_sync` AS `s`
			WHERE
				`s`.`reference` = 'ws.user'
				AND `s`.`local_id` = `c`.`id`
		);

SET FOREIGN_KEY_CHECKS = 1;
