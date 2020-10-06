SET FOREIGN_KEY_CHECKS = 0;

INSERT INTO `#__redshopb_sync` (`reference`, `local_id`, `remote_key`)
	SELECT
		'ws.customer_discount_group', `cdg`.`id`, `cdg`.`code`
	FROM
		`#__redshopb_customer_discount_group` AS `cdg`
	WHERE
		NOT EXISTS (
			SELECT
				1
			FROM
				`#__redshopb_sync` AS `s`
			WHERE
				`s`.`reference` = 'ws.customer_discount_group'
				AND `s`.`local_id` = `cdg`.`id`
		);

SET FOREIGN_KEY_CHECKS = 1;
