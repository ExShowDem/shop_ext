ALTER TABLE `#__redshopb_order`
ADD `delivery_address_code` VARCHAR(255) NOT NULL AFTER `delivery_address_id`,
ADD `delivery_address_type` ENUM('employee','department','company','') NOT NULL DEFAULT '' AFTER `delivery_address_code`;