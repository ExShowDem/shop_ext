SET FOREIGN_KEY_CHECKS = 0;

UPDATE
	`#__redshopb_department`, `#__redshopb_sync`
SET
	`#__redshopb_department`.`department_number` = `#__redshopb_sync`.`remote_key`
WHERE
	`#__redshopb_sync`.`reference` = 'ws.department'
	AND `#__redshopb_sync`.`local_id` = `#__redshopb_department`.`id`;

INSERT INTO `#__redshopb_sync` (`reference`, `local_id`, `remote_key`)
	SELECT
		'ws.department', `c`.`id`, `c`.`department_number`
	FROM
		`#__redshopb_department` AS `c`
	WHERE
		NOT EXISTS (
			SELECT
				1
			FROM
				`#__redshopb_sync` AS `s`
			WHERE
				`s`.`reference` = 'ws.department'
				AND `s`.`local_id` = `c`.`id`
		)
		AND `c`.`department_number` IS NOT NULL
		AND `c`.`department_number` <> '';

UPDATE
	`#__redshopb_department`
SET
	`image` = NULL;

SET FOREIGN_KEY_CHECKS = 1;
