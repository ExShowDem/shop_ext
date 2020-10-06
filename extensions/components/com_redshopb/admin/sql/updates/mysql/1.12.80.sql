SET FOREIGN_KEY_CHECKS=0;

DELETE FROM `#__redshopb_sync`
WHERE
  `reference` IN ('erp.pim.media','ws.product_image','erp.webservice.product_images','fengel.media')
  AND `local_id` NOT IN (
    SELECT
      `id`
    FROM
      `#__redshopb_media`
  );

DELETE FROM `#__redshopb_sync`
WHERE
  `reference` IN ('ws.product_price','erp.pim.product_price','fengel.product_price_item')
  AND `local_id` NOT IN (
    SELECT
      `id`
    FROM
      `#__redshopb_product_price`
  );

SET FOREIGN_KEY_CHECKS=1;
