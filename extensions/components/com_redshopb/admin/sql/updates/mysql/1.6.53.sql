SET FOREIGN_KEY_CHECKS = 0;

INSERT INTO `#__redshopb_sync` (`reference`, `local_id`, `remote_key`)
	SELECT
		'ws.product_discount_group', `pdg`.`id`, `pdg`.`code`
	FROM
		`#__redshopb_product_discount_group` AS `pdg`
	WHERE
		NOT EXISTS (
			SELECT
				1
			FROM
				`#__redshopb_sync` AS `s`
			WHERE
				`s`.`reference` = 'ws.product_discount_group'
				AND `s`.`local_id` = `pdg`.`id`
		);

SET FOREIGN_KEY_CHECKS = 1;
