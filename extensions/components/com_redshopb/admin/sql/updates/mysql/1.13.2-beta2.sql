SET FOREIGN_KEY_CHECKS=0;

DELETE FROM `#__redshopb_type`
  WHERE `id` IN (19, 20)
;

INSERT INTO `#__redshopb_type` (`id`, `name`, `alias`, `value_type`, `field_name`, `multiple`) VALUES
  (19, 'Range', 'range', 'string_value', 'aesECRange', 0)
;

SET FOREIGN_KEY_CHECKS=1;
