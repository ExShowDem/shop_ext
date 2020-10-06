UPDATE
	`#__redshopb_cron`
SET
	`name` = MID(`name`,4)
WHERE
	MID(`name`,1,3) = 'Pim'
	AND `plugin` = 'pim';

UPDATE
	`#__redshopb_cron`
SET
	`alias` = CONCAT(MID(`alias`,1,4), MID(`alias`,8))
WHERE
	MID(`alias`,5,3) = 'pim'
	AND `plugin` = 'pim';

UPDATE
	`#__redshopb_cron`
SET
	`parent_alias` = CONCAT(MID(`parent_alias`,1,4), MID(`parent_alias`,8))
WHERE
	MID(`parent_alias`,5,3) = 'pim'
	AND `plugin` = 'pim';
