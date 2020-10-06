ALTER TABLE `#__redshopb_product_price`
  ADD COLUMN `product_id` INT(11) UNSIGNED NOT NULL AFTER `id`,
  ADD COLUMN `product_item_id` INT(11) UNSIGNED NULL AFTER `product_id`;

UPDATE
  `#__redshopb_product_price`
SET
  `product_id` = `type_id`
WHERE
  `type` = 'product';

UPDATE
  `#__redshopb_product_price` AS `a`
SET
  `product_id` = (
    SELECT
      pi.`product_id`
    FROM
      `#__redshopb_product_item` AS pi
    WHERE
      `pi`.`id` = `a`.`type_id`),
  `product_item_id` = `type_id`
WHERE
  `a`.`type` ='product_item';
