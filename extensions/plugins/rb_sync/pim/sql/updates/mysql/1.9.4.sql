UPDATE `#__redshopb_sync` SET
 `hash_key` = ''
 WHERE
 `reference` = 'erp.pim.media' OR `reference` = 'erp.pim.product'
;
