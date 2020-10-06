SET FOREIGN_KEY_CHECKS = 0;

INSERT INTO `#__redshopb_sync` (`reference`, `local_id`, `remote_key`)
	SELECT
		'ws.customer_price_group', `cpg`.`id`, `cpg`.`code`
	FROM
		`#__redshopb_customer_price_group` AS `cpg`
	WHERE
		NOT EXISTS (
			SELECT
				1
			FROM
				`#__redshopb_sync` AS `s`
			WHERE
				`s`.`reference` = 'ws.customer_price_group'
				AND `s`.`local_id` = `cpg`.`id`
		);

SET FOREIGN_KEY_CHECKS = 1;
