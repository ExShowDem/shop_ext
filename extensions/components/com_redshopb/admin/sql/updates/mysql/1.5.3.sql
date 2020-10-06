UPDATE
	`#__redshopb_address` AS `a`
	INNER JOIN `#__redshopb_company` AS `c` ON `a`.`id` = `c`.`address_id`
SET
	`a`.`customer_type` = null,
	`a`.`customer_id` = null,
	`a`.`order` = 12
WHERE
	`a`.`order` = 13
	AND `c`.`type` = 'customer';
	
UPDATE
	`#__redshopb_address` AS `a`
	INNER JOIN `#__redshopb_company` AS `c` ON `a`.`id` = `c`.`address_id`
SET
	`a`.`customer_type` = null,
	`a`.`customer_id` = null,
	`a`.`order` = 9
WHERE
	`a`.`order` = 13
	AND `c`.`type` = 'end_customer';

UPDATE
	`#__redshopb_address` AS `a`
	INNER JOIN `#__redshopb_department` AS `d` ON `a`.`id` = `d`.`address_id`
SET
	`a`.`customer_type` = null,
	`a`.`customer_id` = null,
	`a`.`order` = 8
WHERE
	`a`.`order` = 13;

UPDATE
	`#__redshopb_address` AS `a`
	INNER JOIN `#__redshopb_user` AS `u` ON `a`.`id` = `u`.`address_id`
SET
	`a`.`customer_type` = null,
	`a`.`customer_id` = null,
	`a`.`order` = 7
WHERE
	`a`.`order` = 13;
