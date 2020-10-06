-- -----------------------------------------------------
-- Table `#__redshopb_upgrade_1_0_to_1_6_12`
-- -----------------------------------------------------

CREATE TABLE `#__redshopb_upgrade_1_0_to_1_6_12` (
  `table` VARCHAR(255) NOT NULL,
  `table_id` INT(10) UNSIGNED NOT NULL,
  `field_name` VARCHAR(255) NOT NULL,
  `field_value_string` VARCHAR(255) NULL,
  `field_value_int` INT(11) NULL,
  `field_value_float` INT(11) NULL,
  `field_value_text` VARCHAR(255) NULL,
  PRIMARY KEY (`table`, `table_id`, `field_name`)
 )
   ENGINE = InnoDB
   DEFAULT CHARSET = utf8;

-- -----------------------------------------------------
-- Table `#__redshopb_acl_access`
-- -----------------------------------------------------
CALL `#__redshopb_acl_access_1_5_0`();

DROP PROCEDURE `#__redshopb_acl_access_1_5_0`;

ALTER TABLE `#__redshopb_acl_access`
  ADD CONSTRAINT `#__racl_a_fk1` FOREIGN KEY (`section_id`) REFERENCES `#__redshopb_acl_section` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE;

-- -----------------------------------------------------
-- Table `#__redshopb_acl_rule`
-- -----------------------------------------------------
CALL `#__redshopb_acl_rule_1_5_0`();

DROP PROCEDURE `#__redshopb_acl_rule_1_5_0`;

ALTER TABLE `#__redshopb_acl_rule`
  ADD KEY `idx_redshopb_acl_rule_effective` (`access_id`, `role_id`, `joomla_asset_id`),
  ADD CONSTRAINT `#__racl_r_fk1` FOREIGN KEY (`access_id`) REFERENCES `#__redshopb_acl_access` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__racl_r_fk2` FOREIGN KEY (`role_id`) REFERENCES `#__redshopb_role` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__racl_r_fk3` FOREIGN KEY (`joomla_asset_id`) REFERENCES `#__assets` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE;


-- -----------------------------------------------------
-- Table `#__redshopb_address`
-- -----------------------------------------------------

CALL `#__redshopb_address_1_5_0`();

DROP PROCEDURE `#__redshopb_address_1_5_0`;

ALTER TABLE `#__redshopb_address`
  ADD CONSTRAINT `#__raddress_fk1` FOREIGN KEY (`country_id`) REFERENCES `#__redshopb_country` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE;



-- -----------------------------------------------------
-- Tables `#__redshopb_category` and `#__redshopb_tag`
-- -----------------------------------------------------

RENAME TABLE `#__redshopb_category` TO `#__redshopb_category_tags`;

RENAME TABLE `#__redshopb_tag` TO `#__redshopb_category`;

RENAME TABLE `#__redshopb_category_tags` TO `#__redshopb_tag`;

INSERT INTO `#__redshopb_upgrade_1_0_to_1_6_12` (`table`, `table_id`, `field_name`, `field_value_string`)
SELECT
  'tag', `id`, 'type', `type`
FROM
  `#__redshopb_tag`
WHERE
  `type` IS NOT NULL;

INSERT INTO `#__redshopb_upgrade_1_0_to_1_6_12` (`table`, `table_id`, `field_name`, `field_value_int`)
SELECT
  'tag', `id`, 'created_by', `created_by`
FROM
  `#__redshopb_tag`
WHERE
  `created_by` IS NOT NULL;

INSERT INTO `#__redshopb_upgrade_1_0_to_1_6_12` (`table`, `table_id`, `field_name`, `field_value_string`)
SELECT
  'tag', `id`, 'created_date', `created_date`
FROM
  `#__redshopb_tag`
WHERE
  `created_date` IS NOT NULL;

INSERT INTO `#__redshopb_upgrade_1_0_to_1_6_12` (`table`, `table_id`, `field_name`, `field_value_int`)
SELECT
  'tag', `id`, 'modified_by', `modified_by`
FROM
  `#__redshopb_tag`
WHERE
  `modified_by` IS NOT NULL;

INSERT INTO `#__redshopb_upgrade_1_0_to_1_6_12` (`table`, `table_id`, `field_name`, `field_value_string`)
SELECT
  'tag', `id`, 'modified_date', `modified_date`
FROM
  `#__redshopb_tag`
WHERE
  `modified_date` IS NOT NULL;

CALL `#__redshopb_tag_1_5_0`();

DROP PROCEDURE `#__redshopb_tag_1_5_0`;

UPDATE `#__redshopb_tag`
SET
  `checked_out` = NULL
WHERE
  `checked_out` = 0;

ALTER TABLE `#__redshopb_tag`
  DROP COLUMN `type`,
  DROP COLUMN `created_by`,
  DROP COLUMN `created_date`,
  DROP COLUMN `modified_by`,
  DROP COLUMN `modified_date`,
  MODIFY COLUMN `checked_out` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  ADD CONSTRAINT `#__rtag_fk1` FOREIGN KEY (`company_id`) REFERENCES `#__redshopb_company` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE;

CALL `#__redshopb_category_1_5_0`();

DROP PROCEDURE `#__redshopb_category_1_5_0`;

ALTER TABLE `#__redshopb_category`
  MODIFY COLUMN `parent_id` INT(10) UNSIGNED NULL DEFAULT NULL,
  ADD COLUMN `image` VARCHAR(255) NOT NULL DEFAULT '' AFTER `path`,
  MODIFY COLUMN `checked_out` INT(11) DEFAULT NULL,
  ADD COLUMN `created_by` INT(11) DEFAULT NULL AFTER `checked_out_time`,
  ADD COLUMN `created_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `created_by`,
  ADD COLUMN `modified_by` INT(11) DEFAULT NULL AFTER `created_date`,
  ADD COLUMN `modified_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `modified_by`;

UPDATE `#__redshopb_category`
SET
  `checked_out` = NULL
WHERE
  `checked_out` = 0;

UPDATE `#__redshopb_category`
SET
  `parent_id` = NULL
WHERE
  `parent_id` = 0;

ALTER TABLE `#__redshopb_category`
  ADD KEY `idx_state` (`state`),
  ADD CONSTRAINT `#__rcategory_fk1` FOREIGN KEY (`checked_out`) REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rcategory_fk2` FOREIGN KEY (`created_by`) REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rcategory_fk3` FOREIGN KEY (`modified_by`) REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rcategory_fk4` FOREIGN KEY (`company_id`) REFERENCES `#__redshopb_company` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rcategory_fk5` FOREIGN KEY (`parent_id`) REFERENCES `#__redshopb_category` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE;

RENAME TABLE `#__redshopb_category_rctranslations` TO `#__redshopb_category_tags_rctranslations`;

RENAME TABLE `#__redshopb_tag_rctranslations` TO `#__redshopb_category_rctranslations`;

RENAME TABLE `#__redshopb_category_tags_rctranslations` TO `#__redshopb_tag_rctranslations`;

CALL `#__redshopb_tag_rctranslations_1_5_0`();
DROP PROCEDURE `#__redshopb_tag_rctranslations_1_5_0`;

CALL `#__redshopb_category_rctranslations_1_5_0`();
DROP PROCEDURE `#__redshopb_category_rctranslations_1_5_0`;


-- -----------------------------------------------------
-- Table `#__redshopb_company`
-- -----------------------------------------------------

CALL `#__redshopb_company_1_5_0`();

DROP PROCEDURE `#__redshopb_company_1_5_0`;

INSERT INTO `#__redshopb_upgrade_1_0_to_1_6_12` (`table`, `table_id`, `field_name`, `field_value_int`)
SELECT
  'company', `id`, 'show_retail_price', `show_retail_price`
FROM
  `#__redshopb_company`
WHERE
  `show_retail_price` IS NOT NULL;

UPDATE `#__redshopb_company`
SET
  `parent_id` = NULL
WHERE
  `parent_id` = 0;

ALTER TABLE `#__redshopb_company`
  DROP COLUMN `show_retail_price`,
  ADD CONSTRAINT `#__rcompany_fk1` FOREIGN KEY (`address_id`) REFERENCES `#__redshopb_address` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rcompany_fk2` FOREIGN KEY (`asset_id`) REFERENCES `#__assets` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rcompany_fk3` FOREIGN KEY (`parent_id`) REFERENCES `#__redshopb_company` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rcompany_fk4` FOREIGN KEY (`created_by`) REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rcompany_fk5` FOREIGN KEY (`modified_by`) REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rcompany_fk6` FOREIGN KEY (`layout_id`) REFERENCES `#__redshopb_layout` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rcompany_fk7` FOREIGN KEY (`currency_id`) REFERENCES `#__redshopb_currency` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE;


-- -----------------------------------------------------
-- Table `#__redshopb_company_sales_person_xref`
-- -----------------------------------------------------

CALL `#__redshopb_company_sales_person_xref_1_5_0`();

DROP PROCEDURE `#__redshopb_company_sales_person_xref_1_5_0`;

ALTER TABLE `#__redshopb_company_sales_person_xref`
  ADD PRIMARY KEY (`user_id`, `company_id`),
  ADD CONSTRAINT `#__rcompany_spx_fk1` FOREIGN KEY (`company_id`) REFERENCES `#__redshopb_company` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rcompany_spx_fk2` FOREIGN KEY (`user_id`) REFERENCES `#__redshopb_user` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE;


-- -----------------------------------------------------
-- Table `#__redshopb_currency`
-- -----------------------------------------------------

CALL `#__redshopb_currency_1_5_0`();

DROP PROCEDURE `#__redshopb_currency_1_5_0`;

ALTER TABLE `#__redshopb_currency`
  ADD CONSTRAINT `#__rcurrency_fk1` FOREIGN KEY (`checked_out`) REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rcurrency_fk2` FOREIGN KEY (`created_by`) REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rcurrency_fk3` FOREIGN KEY (`modified_by`) REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE;


-- -----------------------------------------------------
-- Table `#__redshopb_customer_discount_group`
-- -----------------------------------------------------

CALL `#__redshopb_customer_discount_group_1_5_0`();

DROP PROCEDURE `#__redshopb_customer_discount_group_1_5_0`;

ALTER TABLE `#__redshopb_customer_discount_group`
  ADD CONSTRAINT `#__rcustomer_dg_fk1` FOREIGN KEY (`company_id`) REFERENCES `#__redshopb_company` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rcustomer_dg_fk2` FOREIGN KEY (`created_by`) REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rcustomer_dg_fk3` FOREIGN KEY (`checked_out`) REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rcustomer_dg_fk4` FOREIGN KEY (`modified_by`) REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE;


-- -----------------------------------------------------
-- Table `#__redshopb_customer_discount_group_xref`
-- -----------------------------------------------------

CALL `#__redshopb_customer_discount_group_xref_1_5_0`();

DROP PROCEDURE `#__redshopb_customer_discount_group_xref_1_5_0`;

ALTER TABLE `#__redshopb_customer_discount_group_xref`
  ADD CONSTRAINT `#__rcustomer_dgx_fk_1` FOREIGN KEY (`discount_group_id`) REFERENCES `#__redshopb_customer_discount_group` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rcustomer_dgx_fk_2` FOREIGN KEY (`customer_id`) REFERENCES `#__redshopb_company` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE;


-- -----------------------------------------------------
-- Table `#__redshopb_customer_price_group`
-- -----------------------------------------------------

CALL `#__redshopb_customer_price_group_1_5_0`();

DROP PROCEDURE `#__redshopb_customer_price_group_1_5_0`;

ALTER TABLE `#__redshopb_customer_price_group`
  ADD CONSTRAINT `#__rcustomer_pg_fk1` FOREIGN KEY (`company_id`) REFERENCES `#__redshopb_company` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rcustomer_pg_fk2` FOREIGN KEY (`created_by`) REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rcustomer_pg_fk3` FOREIGN KEY (`checked_out`) REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rcustomer_pg_fk4` FOREIGN KEY (`modified_by`) REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE;


-- -----------------------------------------------------
-- Table `#__redshopb_customer_price_group_xref`
-- -----------------------------------------------------

CALL `#__redshopb_customer_price_group_xref_1_5_0`();

DROP PROCEDURE `#__redshopb_customer_price_group_xref_1_5_0`;

ALTER TABLE `#__redshopb_customer_price_group_xref`
  ADD CONSTRAINT `#__rcustomer_pgx_1` FOREIGN KEY (`price_group_id`) REFERENCES `#__redshopb_customer_price_group` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rcustomer_pgx_2` FOREIGN KEY (`customer_id`) REFERENCES `#__redshopb_company` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE;


-- -----------------------------------------------------
-- Table `#__redshopb_department`
-- -----------------------------------------------------

CALL `#__redshopb_department_1_5_0`();

DROP PROCEDURE `#__redshopb_department_1_5_0`;

UPDATE `#__redshopb_department`
SET
  `address_id` = NULL
WHERE
  `address_id` = 0;

ALTER TABLE `#__redshopb_department`
  MODIFY COLUMN `parent_id` INT(10) UNSIGNED NULL DEFAULT NULL;

UPDATE `#__redshopb_department`
SET
  `parent_id` = NULL
WHERE
  `parent_id` = 0;

ALTER TABLE `#__redshopb_department`
  ADD CONSTRAINT `#__rdepartment_fk1` FOREIGN KEY (`address_id`) REFERENCES `#__redshopb_address` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rdepartment_fk2` FOREIGN KEY (`asset_id`) REFERENCES `#__assets` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rdepartment_fk3` FOREIGN KEY (`company_id`) REFERENCES `#__redshopb_company` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rdepartment_fk4` FOREIGN KEY (`checked_out`) REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rdepartment_fk5` FOREIGN KEY (`created_by`) REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rdepartment_fk6` FOREIGN KEY (`modified_by`) REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rdeparmtnet_fk7` FOREIGN KEY (`parent_id`) REFERENCES `#__redshopb_department` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE;


-- -----------------------------------------------------
-- Table `#__redshopb_fee`
-- -----------------------------------------------------

CALL `#__redshopb_fee_1_5_0`();

DROP PROCEDURE `#__redshopb_fee_1_5_0`;

ALTER TABLE `#__redshopb_fee`
  ADD CONSTRAINT `#__rfee_fk1` FOREIGN KEY (`currency_id`) REFERENCES `#__redshopb_currency` (`id`)
    ON DELETE SET NULL
    ON UPDATE SET NULL,
  ADD CONSTRAINT `#__rfee_fk2` FOREIGN KEY (`product_id`) REFERENCES `#__redshopb_product` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE;


-- -----------------------------------------------------
-- Table `#__redshopb_layout`
-- -----------------------------------------------------

CALL `#__redshopb_layout_1_5_0`();

DROP PROCEDURE `#__redshopb_layout_1_5_0`;

ALTER TABLE `#__redshopb_layout`
  ADD CONSTRAINT `#__rlayout_fk1` FOREIGN KEY (`company_id`) REFERENCES `#__redshopb_company` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rlayout_fk2` FOREIGN KEY (`created_by`) REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rlayout_fk3` FOREIGN KEY (`modified_by`) REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE;


-- -----------------------------------------------------
-- Table `#__redshopb_logos`
-- -----------------------------------------------------

CALL `#__redshopb_logos_1_5_0`();

DROP PROCEDURE `#__redshopb_logos_1_5_0`;

ALTER TABLE `#__redshopb_logos`
  ADD CONSTRAINT `#__rlogos_fk1` FOREIGN KEY (`brand_id`) REFERENCES `#__redshopb_category` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE;


-- -----------------------------------------------------
-- Table `#__redshopb_media`
-- -----------------------------------------------------

CALL `#__redshopb_media_1_5_0`();

DROP PROCEDURE `#__redshopb_media_1_5_0`;

ALTER TABLE `#__redshopb_media`
  ADD KEY `idx_product_id` (`product_id`),
  ADD CONSTRAINT `#__rmedia_fk1` FOREIGN KEY (`product_id`) REFERENCES `#__redshopb_product` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rmedia_fk2` FOREIGN KEY (`created_by`) REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rmedia_fk3` FOREIGN KEY (`created_by`) REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rmedia_fk4` FOREIGN KEY (`modified_by`) REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE;


-- -----------------------------------------------------
-- Table `#__redshopb_order`
-- -----------------------------------------------------

INSERT INTO `#__redshopb_upgrade_1_0_to_1_6_12` (`table`, `table_id`, `field_name`, `field_value_string`)
SELECT
  'order', `id`, 'delivery_address_code', `delivery_address_code`
FROM
  `#__redshopb_order`
WHERE
  `delivery_address_code` IS NOT NULL;

INSERT INTO `#__redshopb_upgrade_1_0_to_1_6_12` (`table`, `table_id`, `field_name`, `field_value_string`)
SELECT
  'order', `id`, 'delivery_address_type', `delivery_address_type`
FROM
  `#__redshopb_order`
WHERE
  `delivery_address_type` IS NOT NULL;

CALL `#__redshopb_order_1_5_0`();

DROP PROCEDURE `#__redshopb_order_1_5_0`;

ALTER TABLE `#__redshopb_order`
  DROP COLUMN `delivery_address_code`,
  DROP COLUMN `delivery_address_type`,
  ADD CONSTRAINT `#__rorder_fk4` FOREIGN KEY (`customer_company`) REFERENCES `#__redshopb_company` (`id`)
    ON DELETE SET NULL
    ON UPDATE NO ACTION,
  ADD CONSTRAINT `#__rorder_fk5` FOREIGN KEY (`customer_department`) REFERENCES `#__redshopb_department` (`id`)
    ON DELETE SET NULL
    ON UPDATE NO ACTION;


-- -----------------------------------------------------
-- Table `#__redshopb_order_item`
-- -----------------------------------------------------

CALL `#__redshopb_order_item_1_5_0`();

DROP PROCEDURE `#__redshopb_order_item_1_5_0`;

ALTER TABLE `#__redshopb_order_item`
  ADD CONSTRAINT `#__rorder_i_fk1` FOREIGN KEY (`order_id`) REFERENCES `#__redshopb_order` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rorder_i_fk2` FOREIGN KEY (`parent_id`) REFERENCES `#__redshopb_order_item` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE;


-- -----------------------------------------------------
-- Table `#__redshopb_order_logs`
-- -----------------------------------------------------

CALL `#__redshopb_order_logs_1_5_0`();

DROP PROCEDURE `#__redshopb_order_logs_1_5_0`;

ALTER TABLE `#__redshopb_order_logs`
  ADD CONSTRAINT `#__rorder_l_fk1` FOREIGN KEY (`new_order_id`) REFERENCES `#__redshopb_order` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rorder_l_fk2` FOREIGN KEY (`order_id`) REFERENCES `#__redshopb_order` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE;


-- -----------------------------------------------------
-- Table `#__redshopb_product`
-- -----------------------------------------------------

CALL `#__redshopb_product_1_5_0`();

DROP PROCEDURE `#__redshopb_product_1_5_0`;

ALTER TABLE `#__redshopb_product`
  ADD CONSTRAINT `#__rproduct_fk1` FOREIGN KEY (`checked_out`) REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rproduct_fk2` FOREIGN KEY (`created_by`) REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rproduct_fk3` FOREIGN KEY (`modified_by`) REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rproduct_fk4` FOREIGN KEY (`company_id`) REFERENCES `#__redshopb_company` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE;


-- -----------------------------------------------------
-- Table `#__redshopb_product_attribute`
-- -----------------------------------------------------

CALL `#__redshopb_product_attribute_1_5_0`();

DROP PROCEDURE `#__redshopb_product_attribute_1_5_0`;

ALTER TABLE `#__redshopb_product_attribute`
  ADD CONSTRAINT `#__rproduct_a_fk1` FOREIGN KEY (`product_id`) REFERENCES `#__redshopb_product` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rproduct_a_fk2` FOREIGN KEY (`checked_out`) REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rproduct_a_fk3` FOREIGN KEY (`created_by`) REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rproduct_a_fk4` FOREIGN KEY (`modified_by`) REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE;


-- -----------------------------------------------------
-- Table `#__redshopb_product_attribute_value`
-- -----------------------------------------------------

CALL `#__redshopb_product_attribute_value_1_5_0`();

DROP PROCEDURE `#__redshopb_product_attribute_value_1_5_0`;

ALTER TABLE `#__redshopb_product_attribute_value`
  ADD CONSTRAINT `#__rproduct_av_fk1` FOREIGN KEY (`product_attribute_id`) REFERENCES `#__redshopb_product_attribute` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE;


-- -----------------------------------------------------
-- Tables `#__redshopb_product_category_xref` and `#__redshopb_product_tag_xref`
-- -----------------------------------------------------

RENAME TABLE `#__redshopb_product_category_xref` TO `#__redshopb_product_category_tag_xref`;

RENAME TABLE `#__redshopb_product_tag_xref` TO `#__redshopb_product_category_xref`;

RENAME TABLE `#__redshopb_product_category_tag_xref` TO `#__redshopb_product_tag_xref`;

CALL `#__redshopb_product_category_xref_1_5_0`();

DROP PROCEDURE `#__redshopb_product_category_xref_1_5_0`;

ALTER TABLE `#__redshopb_product_category_xref`
  CHANGE COLUMN `tag_id` `category_id` INT(10) UNSIGNED NOT NULL;

ALTER TABLE `#__redshopb_product_category_xref`
  ADD CONSTRAINT `#__rproduct_cx_fk1` FOREIGN KEY (`product_id`) REFERENCES `#__redshopb_product` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rproduct_cx_fk2` FOREIGN KEY (`category_id`) REFERENCES `#__redshopb_category` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE;

CALL `#__redshopb_product_tag_xref_1_5_0`();

DROP PROCEDURE `#__redshopb_product_tag_xref_1_5_0`;

ALTER TABLE `#__redshopb_product_tag_xref`
  CHANGE COLUMN `category_id` `tag_id` INT(10) UNSIGNED NOT NULL;

ALTER TABLE `#__redshopb_product_tag_xref`
  ADD CONSTRAINT `#__rproduct_tx_fk1` FOREIGN KEY (`tag_id`) REFERENCES `#__redshopb_tag` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rproduct_tx_fk2` FOREIGN KEY (`product_id`) REFERENCES `#__redshopb_product` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE;


-- -----------------------------------------------------
-- Table `#__redshopb_product_composition`
-- -----------------------------------------------------

CALL `#__redshopb_product_composition_1_5_0`();

DROP PROCEDURE `#__redshopb_product_composition_1_5_0`;

ALTER TABLE `#__redshopb_product_composition`
  ADD CONSTRAINT `#__redshopb_product_composition_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `#__redshopb_product` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__redshopb_product_composition_ibfk_2` FOREIGN KEY (`flat_attribute_value_id`) REFERENCES `#__redshopb_product_attribute_value` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE;


-- -----------------------------------------------------
-- Table `#__redshopb_product_descriptions`
-- -----------------------------------------------------

CALL `#__redshopb_product_descriptions_1_5_0`();

DROP PROCEDURE `#__redshopb_product_descriptions_1_5_0`;

ALTER TABLE `#__redshopb_product_descriptions`
  ADD CONSTRAINT `#__rproduct_d_fk2` FOREIGN KEY (`product_id`) REFERENCES `#__redshopb_product` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rproduct_d_fk3` FOREIGN KEY (`flat_attribute_value_id`) REFERENCES `#__redshopb_product_attribute_value` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE;


-- -----------------------------------------------------
-- Table `#__redshopb_product_discount`
-- -----------------------------------------------------

CALL `#__redshopb_product_discount_1_5_0`();

DROP PROCEDURE `#__redshopb_product_discount_1_5_0`;

ALTER TABLE `#__redshopb_product_discount`
  ADD CONSTRAINT `#__rproduct_d_fk1` FOREIGN KEY (`currency_id`) REFERENCES `#__redshopb_currency` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE;


-- -----------------------------------------------------
-- Table `#__redshopb_product_discount_group`
-- -----------------------------------------------------

CALL `#__redshopb_product_discount_group_1_5_0`();

DROP PROCEDURE `#__redshopb_product_discount_group_1_5_0`;

ALTER TABLE `#__redshopb_product_discount_group`
  ADD CONSTRAINT `#__rproduct_dg_fk2` FOREIGN KEY (`created_by`) REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rproduct_dg_fk3` FOREIGN KEY (`checked_out`) REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rproduct_dg_fk4` FOREIGN KEY (`modified_by`) REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE;


-- -----------------------------------------------------
-- Table `#__redshopb_product_discount_group_xref`
-- -----------------------------------------------------

CALL `#__redshopb_product_discount_group_xref_1_5_0`();

DROP PROCEDURE `#__redshopb_product_discount_group_xref_1_5_0`;

ALTER TABLE `#__redshopb_product_discount_group_xref`
  ADD CONSTRAINT `#__rproduct_dgx_fk1` FOREIGN KEY (`discount_group_id`) REFERENCES `#__redshopb_product_discount_group` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rproduct_dgx_fk2` FOREIGN KEY (`product_id`) REFERENCES `#__redshopb_product` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE;


-- -----------------------------------------------------
-- Table `#__redshopb_product_item`
-- -----------------------------------------------------

CALL `#__redshopb_product_item_1_5_0`();

DROP PROCEDURE `#__redshopb_product_item_1_5_0`;

ALTER TABLE `#__redshopb_product_item`
  ADD CONSTRAINT `#__rproduct_i_fk1` FOREIGN KEY (`product_id`) REFERENCES `#__redshopb_product` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE;


-- -----------------------------------------------------
-- Table `#__redshopb_product_item_accessory`
-- -----------------------------------------------------

CALL `#__redshopb_product_item_accessory_1_5_0`();

DROP PROCEDURE `#__redshopb_product_item_accessory_1_5_0`;

ALTER TABLE `#__redshopb_product_item_accessory`
  ADD CONSTRAINT `#__rproduct_ia_fk1` FOREIGN KEY (`accessory_product_id`) REFERENCES `#__redshopb_product` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rproduct_ia_fk2` FOREIGN KEY (`attribute_value_id`) REFERENCES `#__redshopb_product_attribute_value` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rproduct_ia_fk3` FOREIGN KEY (`wardrobe_id`) REFERENCES `#__redshopb_wardrobe` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE;


-- -----------------------------------------------------
-- Table `#__redshopb_product_item_attribute_value_xref`
-- -----------------------------------------------------

CALL `#__redshopb_product_item_attribute_value_xref_1_5_0`();

DROP PROCEDURE `#__redshopb_product_item_attribute_value_xref_1_5_0`;

ALTER TABLE `#__redshopb_product_item_attribute_value_xref`
  ADD CONSTRAINT `#__rproduct_iavx_fk1` FOREIGN KEY (`product_item_id`) REFERENCES `#__redshopb_product_item` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rproduct_iavx_fk2` FOREIGN KEY (`product_attribute_value_id`) REFERENCES `#__redshopb_product_attribute_value` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE;


-- -----------------------------------------------------
-- Table `#__redshopb_product_price`
-- -----------------------------------------------------

CALL `#__redshopb_product_price_1_5_0`();

DROP PROCEDURE `#__redshopb_product_price_1_5_0`;

INSERT INTO `#__redshopb_upgrade_1_0_to_1_6_12` (`table`, `table_id`, `field_name`, `field_value_float`)
SELECT
  'product_price', `id`, 'retail_price', `retail_price`
FROM
  `#__redshopb_product_price`
WHERE
  `retail_price` IS NOT NULL;

ALTER TABLE `#__redshopb_product_price`
  DROP COLUMN `retail_price`;

ALTER TABLE `#__redshopb_product_price`
  ADD CONSTRAINT `#__rproduct_p_fk1` FOREIGN KEY (`country_id`) REFERENCES `#__redshopb_country` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rproduct_p_fk2` FOREIGN KEY (`currency_id`) REFERENCES `#__redshopb_currency` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE;


-- -----------------------------------------------------
-- Table `#__redshopb_product_wash_care_spec_xref`
-- -----------------------------------------------------

CALL `#__redshopb_product_wash_care_spec_xref_1_5_0`();

DROP PROCEDURE `#__redshopb_product_wash_care_spec_xref_1_5_0`;

ALTER TABLE `#__redshopb_product_wash_care_spec_xref`
  ADD CONSTRAINT `#__rproduct_wcsx_fk1` FOREIGN KEY (`wash_care_spec_id`) REFERENCES `#__redshopb_wash_care_spec` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rproduct_wcsx_fk2` FOREIGN KEY (`product_id`) REFERENCES `#__redshopb_product` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE;


-- -----------------------------------------------------
-- Table `#__redshopb_role`
-- -----------------------------------------------------

CALL `#__redshopb_role_1_5_0`();

DROP PROCEDURE `#__redshopb_role_1_5_0`;

ALTER TABLE `#__redshopb_role`
  ADD CONSTRAINT `#__rrole_fk1` FOREIGN KEY (`role_type_id`) REFERENCES `#__redshopb_role_type` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rrole_fk2` FOREIGN KEY (`company_id`) REFERENCES `#__redshopb_company` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rrole_fk3` FOREIGN KEY (`joomla_group_id`) REFERENCES `#__usergroups` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rrole_fk4` FOREIGN KEY (`checked_out`) REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rrole_fk5` FOREIGN KEY (`created_by`) REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rrole_fk6` FOREIGN KEY (`modified_by`) REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE;


-- -----------------------------------------------------
-- Table `#__redshopb_role_type`
-- -----------------------------------------------------

CALL `#__redshopb_role_type_1_5_0`();

DROP PROCEDURE `#__redshopb_role_type_1_5_0`;

ALTER TABLE `#__redshopb_role_type`
  ADD CONSTRAINT `#__rrole_t_fk1` FOREIGN KEY (`checked_out`) REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rrole_t_fk2` FOREIGN KEY (`created_by`) REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rrole_t_fk3` FOREIGN KEY (`modified_by`) REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE;


-- -----------------------------------------------------
-- Table `#__redshopb_user`
-- -----------------------------------------------------

CALL `#__redshopb_user_1_5_0`();

DROP PROCEDURE `#__redshopb_user_1_5_0`;

ALTER TABLE `#__redshopb_user`
  ADD CONSTRAINT `#__ruser_fk1` FOREIGN KEY (`joomla_user_id`) REFERENCES `#__users` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__ruser_fk2` FOREIGN KEY (`company_id`) REFERENCES `#__redshopb_company` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__ruser_fk3` FOREIGN KEY (`department_id`) REFERENCES `#__redshopb_department` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__ruser_fk4` FOREIGN KEY (`address_id`) REFERENCES `#__redshopb_address` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__ruser_fk5` FOREIGN KEY (`checked_out`) REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__ruser_fk6` FOREIGN KEY (`created_by`) REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__ruser_fk7` FOREIGN KEY (`modified_by`) REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__ruser_fk8` FOREIGN KEY (`wallet_id`) REFERENCES `#__redshopb_wallet` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE;


-- -----------------------------------------------------
-- Table `#__redshopb_usergroup_sales_person_xref`
-- -----------------------------------------------------

CALL `#__redshopb_usergroup_sales_person_xref_1_5_0`();

DROP PROCEDURE `#__redshopb_usergroup_sales_person_xref_1_5_0`;

ALTER TABLE `#__redshopb_usergroup_sales_person_xref`
  ADD PRIMARY KEY (`user_id`, `joomla_group_id`),
  ADD CONSTRAINT `#__rusergroup_spx_fk1` FOREIGN KEY (`user_id`) REFERENCES `#__redshopb_user` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rusergroup_spx_fk2` FOREIGN KEY (`joomla_group_id`) REFERENCES `#__usergroups` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE;


-- -----------------------------------------------------
-- Table `#__redshopb_wallet`
-- -----------------------------------------------------

CALL `#__redshopb_wallet_1_5_0`();

DROP PROCEDURE `#__redshopb_wallet_1_5_0`;

ALTER TABLE `#__redshopb_wallet`
  ADD CONSTRAINT `#__rwallet_fk1` FOREIGN KEY (`checked_out`) REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rwallet_fk2` FOREIGN KEY (`created_by`) REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rwallet_fk3` FOREIGN KEY (`modified_by`) REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE;


-- -----------------------------------------------------
-- Table `#__redshopb_wallet_money`
-- -----------------------------------------------------

CALL `#__redshopb_wallet_money_1_5_0`();

DROP PROCEDURE `#__redshopb_wallet_money_1_5_0`;

ALTER TABLE `#__redshopb_wallet_money`
  ADD CONSTRAINT `#__rwallet_m_fk1` FOREIGN KEY (`wallet_id`) REFERENCES `#__redshopb_wallet` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rwallet_m_fk2` FOREIGN KEY (`currency_id`) REFERENCES `#__redshopb_currency` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rwallet_m_fk3` FOREIGN KEY (`checked_out`) REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rwallet_m_fk4` FOREIGN KEY (`created_by`) REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rwallet_m_fk5` FOREIGN KEY (`modified_by`) REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE;


-- -----------------------------------------------------
-- Table `#__redshopb_wardrobe`
-- -----------------------------------------------------

CALL `#__redshopb_wardrobe_1_5_0`();

DROP PROCEDURE `#__redshopb_wardrobe_1_5_0`;

ALTER TABLE `#__redshopb_wardrobe`
  ADD CONSTRAINT `#__rwardrobe_fk1` FOREIGN KEY (`company_id`) REFERENCES `#__redshopb_company` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rwardrobe_fk2` FOREIGN KEY (`checked_out`) REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rwardrobe_fk3` FOREIGN KEY (`created_by`) REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rwardrobe_fk4` FOREIGN KEY (`modified_by`) REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rwardrobe_fk5` FOREIGN KEY (`currency_id`) REFERENCES `#__redshopb_currency` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE;


-- -----------------------------------------------------
-- Table `#__redshopb_wardrobe_department_xref`
-- -----------------------------------------------------

CALL `#__redshopb_wardrobe_department_xref_1_5_0`();

DROP PROCEDURE `#__redshopb_wardrobe_department_xref_1_5_0`;

ALTER TABLE `#__redshopb_wardrobe_department_xref`
  ADD CONSTRAINT `#__rwardrobe_dx_fk1` FOREIGN KEY (`wardrobe_id`) REFERENCES `#__redshopb_wardrobe` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rwardrobe_dx_fk2` FOREIGN KEY (`department_id`) REFERENCES `#__redshopb_department` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE;


-- -----------------------------------------------------
-- Table `#__redshopb_wardrobe_product_item_xref`
-- -----------------------------------------------------

CALL `#__redshopb_wardrobe_product_item_xref_1_5_0`();

DROP PROCEDURE `#__redshopb_wardrobe_product_item_xref_1_5_0`;

ALTER TABLE `#__redshopb_wardrobe_product_item_xref`
  ADD CONSTRAINT `#__rwardrobe_pix_fk1` FOREIGN KEY (`wardrobe_id`) REFERENCES `#__redshopb_wardrobe` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rwardrobe_pix_fk2` FOREIGN KEY (`product_item_id`) REFERENCES `#__redshopb_product_item` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE;


-- -----------------------------------------------------
-- Table `#__redshopb_wardrobe_product_xref`
-- -----------------------------------------------------

CALL `#__redshopb_wardrobe_product_xref_1_5_0`();

DROP PROCEDURE `#__redshopb_wardrobe_product_xref_1_5_0`;

ALTER TABLE `#__redshopb_wardrobe_product_xref`
  ADD CONSTRAINT `#__rwardrobe_px_fk1` FOREIGN KEY (`wardrobe_id`) REFERENCES `#__redshopb_wardrobe` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rwardrobe_px_fk2` FOREIGN KEY (`product_id`) REFERENCES `#__redshopb_product` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE;

