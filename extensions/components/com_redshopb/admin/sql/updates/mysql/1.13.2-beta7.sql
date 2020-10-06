SET FOREIGN_KEY_CHECKS=0;

INSERT INTO `#__redshopb_acl_simple_access_xref` (`simple_access_id`, `access_id`, `role_type_id`, `scope`)
  SELECT
    `a`.`id`, `a`.`id`, `rt`.`id`, 'global'
  FROM
    `#__redshopb_role_type` AS `rt`,
    `#__redshopb_acl_access` AS `a`
  WHERE
    `rt`.`company_role` = 0
    AND `a`.`name` = 'redshopb.order.import'
    AND NOT EXISTS (
        SELECT
          1
        FROM
          `#__redshopb_acl_simple_access_xref` AS `sax`
        WHERE
          `sax`.`simple_access_id` = `a`.`id`
          AND `sax`.`access_id` = `a`.`id`
          AND `sax`.`role_type_id` = `rt`.`id`
    );

SET FOREIGN_KEY_CHECKS=1;
