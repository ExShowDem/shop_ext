UPDATE
	`#__redshopb_cron`
SET
	`name` = MID(`name`,11)
WHERE
	MID(`name`,1,10) = 'Webservice'
	AND `plugin` = 'webservice';

UPDATE
	`#__redshopb_cron`
SET
	`alias` = CONCAT(MID(`alias`,1,11), MID(`alias`,22))
WHERE
	MID(`alias`,12,10) = 'webservice'
	AND `plugin` = 'webservice';

UPDATE
	`#__redshopb_cron`
SET
	`parent_alias` = CONCAT(MID(`parent_alias`,1,11), MID(`parent_alias`,22))
WHERE
	MID(`parent_alias`,12,10) = 'webservice'
	AND `plugin` = 'webservice';
