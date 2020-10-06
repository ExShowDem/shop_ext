SET FOREIGN_KEY_CHECKS = 0;

UPDATE
	`#__redshopb_company`, `#__redshopb_sync`
SET
	`#__redshopb_company`.`customer_number` = `#__redshopb_sync`.`remote_key`
WHERE
	`#__redshopb_sync`.`reference` = 'ws.company'
	AND `#__redshopb_sync`.`local_id` = `#__redshopb_company`.`id`;

INSERT INTO `#__redshopb_sync` (`reference`, `local_id`, `remote_key`)
	SELECT
		'ws.company', `c`.`id`, `c`.`customer_number`
	FROM
		`#__redshopb_company` AS `c`
	WHERE
		NOT EXISTS (
			SELECT
				1
			FROM
				`#__redshopb_sync` AS `s`
			WHERE
				`s`.`reference` = 'ws.company'
				AND `s`.`local_id` = `c`.`id`
		)
		AND `c`.`level` > 0;

SET FOREIGN_KEY_CHECKS = 1;
