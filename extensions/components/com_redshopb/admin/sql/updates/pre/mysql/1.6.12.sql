-- -----------------------------------------------------
-- Table `#__redshopb_upgrade_1_0_to_1_6_12`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_sp_upgrade_1_0_to_1_6_12`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_sp_upgrade_1_0_to_1_6_12`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`tables` WHERE `table_schema` = DATABASE() AND `table_name` = '#__redshopb_upgrade_1_0_to_1_6_12') THEN

	UPDATE
		`#__redshopb_upgrade_1_0_to_1_6_12` AS `u`
		INNER JOIN `#__redshopb_tag` AS `t` ON `t`.`id` = `u`.`table_id`
	SET
		`t`.`type` = `u`.`field_value_string`
	WHERE
		`u`.`table` = 'tag'
		AND `u`.`field_name` = 'type';

	UPDATE
		`#__redshopb_upgrade_1_0_to_1_6_12` AS `u`
		INNER JOIN `#__redshopb_tag` AS `t` ON `t`.`id` = `u`.`table_id`
	SET
		`t`.`created_by` = `u`.`field_value_int`
	WHERE
		`u`.`table` = 'tag'
		AND `u`.`field_name` = 'created_by';

	UPDATE
		`#__redshopb_upgrade_1_0_to_1_6_12` AS `u`
		INNER JOIN `#__redshopb_tag` AS `t` ON `t`.`id` = `u`.`table_id`
	SET
		`t`.`created_date` = `u`.`field_value_string`
	WHERE
		`u`.`table` = 'tag'
		AND `u`.`field_name` = 'created_date';

	UPDATE
		`#__redshopb_upgrade_1_0_to_1_6_12` AS `u`
		INNER JOIN `#__redshopb_tag` AS `t` ON `t`.`id` = `u`.`table_id`
	SET
		`t`.`modified_by` = `u`.`field_value_int`
	WHERE
		`u`.`table` = 'tag'
		AND `u`.`field_name` = 'modified_by';

	UPDATE
		`#__redshopb_upgrade_1_0_to_1_6_12` AS `u`
		INNER JOIN `#__redshopb_tag` AS `t` ON `t`.`id` = `u`.`table_id`
	SET
		`t`.`modified_date` = `u`.`field_value_string`
	WHERE
		`u`.`table` = 'tag'
		AND `u`.`field_name` = 'modified_date';

	UPDATE
		`#__redshopb_upgrade_1_0_to_1_6_12` AS `u`
		INNER JOIN `#__redshopb_company` AS `t` ON `t`.`id` = `u`.`table_id`
	SET
		`t`.`show_retail_price` = `u`.`field_value_int`
	WHERE
		`u`.`table` = 'company'
		AND `u`.`field_name` = 'show_retail_price';

	UPDATE
		`#__redshopb_upgrade_1_0_to_1_6_12` AS `u`
		INNER JOIN `#__redshopb_order` AS `t` ON `t`.`id` = `u`.`table_id`
	SET
		`t`.`delivery_address_code` = `u`.`field_value_string`
	WHERE
		`u`.`table` = 'order'
		AND `u`.`field_name` = 'delivery_address_code';

	UPDATE
		`#__redshopb_upgrade_1_0_to_1_6_12` AS `u`
		INNER JOIN `#__redshopb_order` AS `t` ON `t`.`id` = `u`.`table_id`
	SET
		`t`.`delivery_address_type` = `u`.`field_value_string`
	WHERE
		`u`.`table` = 'order'
		AND `u`.`field_name` = 'delivery_address_type';

	UPDATE
		`#__redshopb_upgrade_1_0_to_1_6_12` AS `u`
		INNER JOIN `#__redshopb_product_price` AS `t` ON `t`.`id` = `u`.`table_id`
	SET
		`t`.`retail_price` = `u`.`field_value_float`
	WHERE
		`u`.`table` = 'product_price'
		AND `u`.`field_name` = 'retail_price';

	DROP TABLE `#__redshopb_upgrade_1_0_to_1_6_12`;

  END IF;
END//