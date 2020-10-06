SET FOREIGN_KEY_CHECKS = 0;

ALTER TABLE `#__redshopb_collection_product_xref`
  MODIFY `price` DECIMAL(10,2) NULL;

ALTER TABLE `#__redshopb_company`
  ADD `show_retail_price` TINYINT(1) NOT NULL DEFAULT '0' AFTER `send_mail_on_order`;

ALTER TABLE `#__redshopb_order`
  ADD `delivery_address_code` VARCHAR(255) NOT NULL AFTER `delivery_address_id`,
  ADD `delivery_address_type` ENUM('company','department','employee','') NOT NULL AFTER `delivery_address_code`;

ALTER TABLE `#__redshopb_product_price`
  ADD `retail_price` DECIMAL(10,2) NOT NULL AFTER `price`;

ALTER TABLE `#__redshopb_product_company_xref`
 DROP KEY `idx_common`,
 DROP KEY `idx_company_id`;

ALTER TABLE `#__redshopb_product_company_xref`
  ADD PRIMARY KEY (`product_id`, `company_id`),
  ADD INDEX `#__rs_prodcomp_fk2` (`company_id` ASC),
  ADD INDEX `#__rs_prodcomp_fk1` (`product_id` ASC),
  ADD CONSTRAINT `#__rs_prodcomp_fk1`
    FOREIGN KEY (`product_id`)
    REFERENCES `#__redshopb_product` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_prodcomp_fk2`
    FOREIGN KEY (`company_id`)
    REFERENCES `#__redshopb_company` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE;

SET FOREIGN_KEY_CHECKS = 1;
