SET FOREIGN_KEY_CHECKS = 0;

-- -----------------------------------------------------
-- Table `#__redshopb_acl_simple_access_xref`
-- -----------------------------------------------------
CALL `#__redshopb_acl_simple_access_xref_1_6_0`();

DROP PROCEDURE `#__redshopb_acl_simple_access_xref_1_6_0`;

ALTER TABLE `#__redshopb_acl_simple_access_xref`
  ADD INDEX `#__rs_acl_sax_fk1` (`simple_access_id` ASC),
  ADD INDEX `#__rs_acl_sax_fk2` (`access_id` ASC),
  ADD INDEX `#__rs_acl_sax_fk3` (`role_type_id` ASC),
  ADD CONSTRAINT `#__rs_acl_sax_fk1`
  FOREIGN KEY (`simple_access_id`)
  REFERENCES `#__redshopb_acl_access` (`id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_acl_sax_fk2`
  FOREIGN KEY (`access_id`)
  REFERENCES `#__redshopb_acl_access` (`id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_acl_sax_fk3`
  FOREIGN KEY (`role_type_id`)
  REFERENCES `#__redshopb_role_type` (`id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE;


-- -----------------------------------------------------
-- Table `#__redshopb_acl_access`
-- -----------------------------------------------------
CALL `#__redshopb_acl_access_1_6_0`();

DROP PROCEDURE `#__redshopb_acl_access_1_6_0`;

ALTER TABLE `#__redshopb_acl_access`
  ADD CONSTRAINT `#__rs_acl_access_fk1`
  FOREIGN KEY (`section_id`)
  REFERENCES `#__redshopb_acl_section` (`id`)
  ON DELETE SET NULL
  ON UPDATE CASCADE;


-- -----------------------------------------------------
-- Table `#__redshopb_acl_section`
-- -----------------------------------------------------

-- No changes needed

-- -----------------------------------------------------
-- Table `#__redshopb_acl_rule`
-- -----------------------------------------------------
CALL `#__redshopb_acl_rule_1_6_0`();

DROP PROCEDURE `#__redshopb_acl_rule_1_6_0`;

ALTER TABLE `#__redshopb_acl_rule`
  ADD INDEX `#__rs_acl_rule_fk2` (`role_id` ASC),
  ADD INDEX `#__rs_acl_rule_fk3` (`joomla_asset_id` ASC),
  ADD INDEX `#__rs_acl_rule_fk1` (`access_id` ASC),
  ADD CONSTRAINT `#__rs_acl_rule_fk1`
  FOREIGN KEY (`access_id`)
  REFERENCES `#__redshopb_acl_access` (`id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_acl_rule_fk2`
  FOREIGN KEY (`role_id`)
  REFERENCES `#__redshopb_role` (`id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_acl_rule_fk3`
  FOREIGN KEY (`joomla_asset_id`)
  REFERENCES `#__assets` (`id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE;


-- -----------------------------------------------------
-- Table `#__redshopb_role_type`
-- -----------------------------------------------------
CALL `#__redshopb_role_type_1_6_0`();

DROP PROCEDURE `#__redshopb_role_type_1_6_0`;

ALTER TABLE `#__redshopb_role_type`
  ADD CONSTRAINT `#__rs_roletype_fk1`
  FOREIGN KEY (`checked_out`)
  REFERENCES `#__users` (`id`)
  ON DELETE SET NULL
  ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_roletype_fk2`
  FOREIGN KEY (`created_by`)
  REFERENCES `#__users` (`id`)
  ON DELETE SET NULL
  ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_roletype_fk3`
  FOREIGN KEY (`modified_by`)
  REFERENCES `#__users` (`id`)
  ON DELETE SET NULL
  ON UPDATE CASCADE;


-- -----------------------------------------------------
-- Table `#__redshopb_type`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `#__redshopb_type` (
  `id` INT(11) NOT NULL,
  `name` VARCHAR(255) NULL,
  `alias` VARCHAR(255) NULL,
  `value_type` ENUM('string','float','int','text') NULL DEFAULT 'string' COMMENT 'Value field to use in the destination value table',
  `field_name` VARCHAR(50) NULL COMMENT 'PHP form field class',
  `params` VARCHAR(2048) NULL COMMENT 'JSON formatted',
  PRIMARY KEY (`id`),
  UNIQUE INDEX `#__rs_type_uq1` (`alias` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `#__redshopb_field`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `#__redshopb_field` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `scope` ENUM('product','order','category','company','department') NOT NULL DEFAULT 'product',
  `type_id` INT(11) NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `alias` VARCHAR(255) NOT NULL,
  `description` VARCHAR(255) NULL,
  `ordering` INT(11) UNSIGNED NOT NULL,
  `state` TINYINT(4) NOT NULL DEFAULT '1',
  `searchable_frontend` TINYINT(4) NOT NULL DEFAULT '1',
  `searchable_backend` TINYINT(4) NOT NULL DEFAULT '1',
  `params` VARCHAR(2048) NULL COMMENT 'JSON formatted',
  PRIMARY KEY (`id`),
  INDEX `#__rs_field_fk1` (`type_id` ASC),
  UNIQUE INDEX `#__rs_field_uq1` (`alias` ASC),
  CONSTRAINT `#__rs_field_fk1`
    FOREIGN KEY (`type_id`)
    REFERENCES `#__redshopb_type` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `#__redshopb_conversion`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `#__redshopb_conversion` (
  `id` INT(10) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NULL,
  `alias` VARCHAR(45) NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;

-- Move language of sizes ('Str.') to conversion sets
INSERT INTO `#__redshopb_conversion` (`name`)
SELECT DISTINCT
  `pavt`.`rctranslations_language`
FROM
  `#__redshopb_product_attribute`  AS `pa`
    INNER JOIN `#__redshopb_product_attribute_value` AS `pav` ON `pa`.`id` = `pav`.`product_attribute_id`
    INNER JOIN `#__redshopb_product_attribute_value_rctranslations` AS `pavt` ON `pav`.`id` = `pavt`.`id`
WHERE
  `pa`.`name` = 'Str.'
  AND NOT EXISTS (
    SELECT
      1
    FROM
      `#__languages` AS `l`
    WHERE
      `l`.`lang_code` =  `pavt`.`rctranslations_language`
  );

DELETE FROM
  `#__redshopb_product_attribute_value_rctranslations`
WHERE
  `id` IN (
    SELECT
      `pav`.`id`
    FROM
      `#__redshopb_product_attribute`  AS `pa`
        INNER JOIN `#__redshopb_product_attribute_value` AS `pav` ON `pa`.`id` = `pav`.`product_attribute_id`
    WHERE
      `pa`.`name` = 'Str.'
      )
  AND `rctranslations_language` NOT IN (
      SELECT
        `l`.`lang_code`
      FROM
        `#__languages` AS `l`
    )
  AND `rctranslations_language` IN (
      SELECT
        `c`.`name`
      FROM
        `#__redshopb_conversion` AS `c`
    );

-- -----------------------------------------------------
-- Table `#__redshopb_template`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `#__redshopb_template` (
  `id` INT(10) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NULL,
  `scope` ENUM('product','category','collection','email') NULL,
  `content` LONGTEXT NULL,
  `state` TINYINT(4) NOT NULL DEFAULT '1',
  `checked_out` INT(11) NULL DEFAULT NULL,
  `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` INT(11) NULL DEFAULT NULL,
  `created_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` INT(11) NULL DEFAULT NULL,
  `modified_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  INDEX `#__rs_template_fk1` (`checked_out` ASC),
  INDEX `#__rs_template_fk2` (`created_by` ASC),
  INDEX `#__rs_template_fk3` (`modified_by` ASC),
  CONSTRAINT `#__rs_template_fk1`
    FOREIGN KEY (`checked_out`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_template_fk2`
    FOREIGN KEY (`created_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_template_fk3`
    FOREIGN KEY (`modified_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `#__redshopb_country`
-- -----------------------------------------------------

-- No changes needed

-- -----------------------------------------------------
-- Table `#__redshopb_currency`
-- -----------------------------------------------------
CALL `#__redshopb_currency_1_6_0`();

DROP PROCEDURE `#__redshopb_currency_1_6_0`;

ALTER TABLE `#__redshopb_currency`
  ADD INDEX `#__rs_currency_fk1` (`checked_out` ASC),
  ADD INDEX `#__rs_currency_fk2` (`created_by` ASC),
  ADD INDEX `#__rs_currency_fk3` (`modified_by` ASC),
  ADD CONSTRAINT `#__rs_currency_fk1`
    FOREIGN KEY (`checked_out`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_currency_fk2`
    FOREIGN KEY (`created_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_currency_fk3`
    FOREIGN KEY (`modified_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE;


-- -----------------------------------------------------
-- Table `#__redshopb_tag`
-- -----------------------------------------------------
CALL `#__redshopb_tag_1_6_0`();

DROP PROCEDURE `#__redshopb_tag_1_6_0`;

ALTER TABLE `#__redshopb_tag`
  CHANGE COLUMN `title` `name` VARCHAR(255) NOT NULL,
  ADD COLUMN `alias` VARCHAR(255) NOT NULL AFTER `name`,
  ADD COLUMN `type` VARCHAR(255) NULL AFTER `alias`,
  MODIFY COLUMN `company_id` INT(10) UNSIGNED NULL DEFAULT NULL AFTER `type`,
  MODIFY COLUMN `state` TINYINT(4) NOT NULL DEFAULT '0' AFTER `company_id`,
  MODIFY COLUMN `checked_out` INT(11) NULL DEFAULT NULL AFTER `state`,
  MODIFY COLUMN `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `checked_out`,
  ADD COLUMN `created_by` INT(11) NULL DEFAULT NULL AFTER `checked_out_time`,
  ADD COLUMN `created_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `created_by`,
  ADD COLUMN `modified_by` INT(11) NULL DEFAULT NULL AFTER `created_date`,
  ADD COLUMN `modified_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `modified_by`,
  ADD INDEX `#__rs_tag_fk2` (`checked_out` ASC),
  ADD INDEX `#__rs_tag_fk1` (`company_id` ASC),
  ADD INDEX `#__rs_tag_fk3` (`created_by` ASC),
  ADD INDEX `#__rs_tag_fk4` (`modified_by` ASC),
  ADD CONSTRAINT `#__rs_tag_fk1`
    FOREIGN KEY (`company_id`)
    REFERENCES `#__redshopb_company` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_tag_fk2`
    FOREIGN KEY (`checked_out`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_tag_fk3`
    FOREIGN KEY (`created_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_tag_fk4`
    FOREIGN KEY (`modified_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE;


-- -----------------------------------------------------
-- Table `#__redshopb_tag_rctranslations`
-- -----------------------------------------------------
ALTER TABLE `#__redshopb_tag_rctranslations`
  CHANGE COLUMN `title` `name` VARCHAR(255);


-- -----------------------------------------------------
-- Table `#__redshopb_product`
-- -----------------------------------------------------
CALL `#__redshopb_product_1_6_0`();

DROP PROCEDURE `#__redshopb_product_1_6_0`;

ALTER TABLE `#__redshopb_product`
  ADD COLUMN `alias` VARCHAR(255) NULL AFTER `name`,
  DROP COLUMN `private_id`,
  DROP COLUMN `private_name`,
  DROP COLUMN `unit_of_measure`,
  DROP COLUMN `season_code`,
  MODIFY COLUMN `service` TINYINT(4) NOT NULL DEFAULT '0' COMMENT '1 - product use as service, 0 - normal product' AFTER `date_new`,
  ADD COLUMN `featured` TINYINT(4) NOT NULL DEFAULT '0' AFTER `service`,
  ADD COLUMN `stock_upper_level` INT(11) NULL COMMENT 'Above this limit, the stock is considered enough, below it, it presents a first level warning' AFTER `featured`,
  ADD COLUMN `stock_lower_level` INT(11) NULL COMMENT 'Below this level, it presents an alarm' AFTER `stock_upper_level`,
  ADD COLUMN `hits` INT(11) NULL AFTER `stock_lower_level`,
  ADD COLUMN `template_id` INT(10) NULL AFTER `hits`,
  ADD INDEX `#__rs_prod_fk1` (`checked_out` ASC),
  ADD INDEX `#__rs_prod_fk2` (`created_by` ASC),
  ADD INDEX `#__rs_prod_fk3` (`modified_by` ASC),
  ADD INDEX `#__rs_prod_fk4` (`company_id` ASC),
  ADD INDEX `#__rs_prod_fk5` (`template_id` ASC),
  ADD CONSTRAINT `#__rs_prod_fk1`
    FOREIGN KEY (`checked_out`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_prod_fk2`
    FOREIGN KEY (`created_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_prod_fk3`
    FOREIGN KEY (`modified_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_prod_fk4`
    FOREIGN KEY (`company_id`)
    REFERENCES `#__redshopb_company` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_prod_fk5`
    FOREIGN KEY (`template_id`)
    REFERENCES `#__redshopb_template` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE;


-- -----------------------------------------------------
-- Table `#__redshopb_product_tag_xref`
-- -----------------------------------------------------
CALL `#__redshopb_product_tag_xref_1_6_0`();

DROP PROCEDURE `#__redshopb_product_tag_xref_1_6_0`;

ALTER TABLE `#__redshopb_product_tag_xref`
  ADD INDEX `#__rs_prod_tag_fk1` (`tag_id` ASC),
  ADD INDEX `#__rs_prod_tag_fk2` (`product_id` ASC),
  ADD CONSTRAINT `#__rs_prod_tag_fk1`
    FOREIGN KEY (`tag_id`)
    REFERENCES `#__redshopb_tag` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_prod_tag_fk2`
    FOREIGN KEY (`product_id`)
    REFERENCES `#__redshopb_product` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE;


-- -----------------------------------------------------
-- Table `#__redshopb_product_category_xref`
-- -----------------------------------------------------
CALL `#__redshopb_product_category_xref_1_6_0`();

DROP PROCEDURE `#__redshopb_product_category_xref_1_6_0`;

ALTER TABLE `#__redshopb_product_category_xref`
  DROP FOREIGN KEY `#__rproduct_cx_fk2`,
  DROP INDEX `#__rproduct_cx_fk2`;

ALTER TABLE `#__redshopb_product_category_xref`
  ADD COLUMN `product_main` TINYINT(4) NOT NULL DEFAULT '0' COMMENT 'Defines the main category for a product' AFTER `category_id`,
  ADD INDEX `#__rs_prod_cat_fk2` (`category_id` ASC),
  ADD INDEX `#__rs_prod_cat_fk1` (`product_id` ASC),
  ADD CONSTRAINT `#__rs_prod_cat_fk1`
    FOREIGN KEY (`product_id`)
    REFERENCES `#__redshopb_product` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_prod_cat_fk2`
    FOREIGN KEY (`category_id`)
    REFERENCES `#__redshopb_category` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE;


-- Sets main category for single-category products
UPDATE `#__redshopb_product_category_xref` AS `pcx`,
  (
    SELECT
      `pcx2`.`product_id` AS `product_id`, `pcx2`.`category_id` AS `category_id`
    FROM
      `#__redshopb_product_category_xref` AS `pcx2`
    GROUP BY
      `pcx2`.`product_id`
    HAVING
       COUNT(*) = 1
  ) AS `pcxt`
SET
  `pcx`.`product_main` = 1
WHERE
  `pcx`.`product_id` = `pcxt`.`product_id`
  AND `pcx`.`category_id` = `pcxt`.`category_id`;


-- -----------------------------------------------------
-- Table `#__redshopb_product_attribute`
-- -----------------------------------------------------
CALL `#__redshopb_product_attribute_1_6_0`();

DROP PROCEDURE `#__redshopb_product_attribute_1_6_0`;

ALTER TABLE `#__redshopb_product_attribute`
  ADD COLUMN `alias` VARCHAR(255) NOT NULL AFTER `name`,
  MODIFY COLUMN `product_id` INT(10) UNSIGNED NOT NULL AFTER `alias`,
  ADD COLUMN `type_id` INT(11) NOT NULL AFTER `product_id`,
  MODIFY COLUMN `ordering` INT(11) NOT NULL DEFAULT '0' AFTER `type_id`,
  CHANGE COLUMN `enable_flat_display` `main_attribute` TINYINT(4) NOT NULL DEFAULT '0',
  ADD COLUMN `conversion_sets` TINYINT(4) NOT NULL DEFAULT '0' AFTER `state`,
  MODIFY COLUMN `type` TINYINT(1) NOT NULL DEFAULT '0' AFTER `modified_date`,
  ADD INDEX `#__rs_prod_at_fk1` (`product_id` ASC),
  ADD INDEX `#__rs_prod_at_fk2` (`checked_out` ASC),
  ADD INDEX `#__rs_prod_at_fk3` (`created_by` ASC),
  ADD INDEX `#__rs_prod_at_fk4` (`modified_by` ASC),
  ADD INDEX `#__rs_prod_at_fk5` (`type_id` ASC),
  ADD CONSTRAINT `#__rs_prod_at_fk1`
    FOREIGN KEY (`product_id`)
    REFERENCES `#__redshopb_product` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_prod_at_fk2`
    FOREIGN KEY (`checked_out`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_prod_at_fk3`
    FOREIGN KEY (`created_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_prod_at_fk4`
    FOREIGN KEY (`modified_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_prod_at_fk5`
    FOREIGN KEY (`type_id`)
    REFERENCES `#__redshopb_type` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE;

-- Enable conversion sets for all the sizes (Str.) fields

UPDATE `#__redshopb_product_attribute`
  SET `conversion_sets` = 1
  WHERE `name` = 'Str.';

-- -----------------------------------------------------
-- Table `#__redshopb_product_attribute_value`
-- -----------------------------------------------------
CALL `#__redshopb_product_attribute_value_1_6_0`();

DROP PROCEDURE `#__redshopb_product_attribute_value_1_6_0`;

ALTER TABLE `#__redshopb_product_attribute_value`
  MODIFY COLUMN `string_value` VARCHAR(2048) NULL DEFAULT NULL,
  MODIFY COLUMN `int_value` INT(11) NULL DEFAULT NULL,
  ADD COLUMN `text_value` TEXT NULL,
  ADD COLUMN `image` VARCHAR(255) NULL,
  ADD INDEX `I` (`product_attribute_id` ASC),
  ADD CONSTRAINT `#__rs_prod_av_fk1`
    FOREIGN KEY (`product_attribute_id`)
    REFERENCES `#__redshopb_product_attribute` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE;


-- -----------------------------------------------------
-- Table `#__redshopb_product_attribute_value_rctranslations`
-- -----------------------------------------------------
ALTER TABLE `#__redshopb_product_attribute_value_rctranslations`
  MODIFY COLUMN `string_value` VARCHAR(2048);


-- -----------------------------------------------------
-- Table `#__redshopb_product_descriptions`
-- -----------------------------------------------------
CALL `#__redshopb_product_descriptions_1_6_0`();

DROP PROCEDURE `#__redshopb_product_descriptions_1_6_0`;

ALTER TABLE `#__redshopb_product_descriptions`
  DROP COLUMN `sku`,
  CHANGE COLUMN `flat_attribute_value_id` `main_attribute_value_id` INT(10) UNSIGNED NULL DEFAULT NULL AFTER `product_id`,
  ADD COLUMN `description_intro` TEXT NULL AFTER `main_attribute_value_id`,
  DROP COLUMN `copyright`,
  ADD INDEX `#__rs_prod_des_fk1` (`product_id` ASC),
  ADD INDEX `#__rs_prod_des_fk2` (`main_attribute_value_id` ASC),
  ADD CONSTRAINT `#__rs_prod_des_fk1`
    FOREIGN KEY (`product_id`)
    REFERENCES `#__redshopb_product` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_prod_des_fk2`
    FOREIGN KEY (`main_attribute_value_id`)
    REFERENCES `#__redshopb_product_attribute_value` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE;


-- -----------------------------------------------------
-- Table `#__redshopb_field_data`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `#__redshopb_field_data` (
  `id` INT(10) NOT NULL AUTO_INCREMENT,
  `field_id` INT(10) UNSIGNED NOT NULL,
  `item_id` INT(10) UNSIGNED NOT NULL COMMENT 'Variable FK depending on the field scope',
  `state` TINYINT(4) NOT NULL DEFAULT '1',
  `string_value` VARCHAR(2048) NULL,
  `int_value` INT(11) NULL,
  `float_value` FLOAT NULL,
  `text_value` TEXT NULL,
  PRIMARY KEY (`id`),
  INDEX `#__rs_fdata_fk1` (`field_id` ASC),
  INDEX `idx_item` (`item_id` ASC),
  CONSTRAINT `#__rs_fdata_fk1`
    FOREIGN KEY (`field_id`)
    REFERENCES `#__redshopb_field` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `#__redshopb_field_data_value`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `#__redshopb_field_data_value` (
  `id` INT(10) NOT NULL AUTO_INCREMENT,
  `field_data_id` INT(10) NOT NULL,
  `name` VARCHAR(255) NULL,
  `value` VARCHAR(255) NULL,
  `default` TINYINT(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  INDEX `#__rs_fdval_fk1` (`field_data_id` ASC),
  CONSTRAINT `#__rs_fdval_fk1`
    FOREIGN KEY (`field_data_id`)
    REFERENCES `#__redshopb_field_data` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `#__redshopb_product_attribute_value_conversion_xref`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `#__redshopb_product_attribute_value_conversion_xref` (
  `value_id` INT(10) UNSIGNED NOT NULL,
  `conversion_set_id` INT(10) NOT NULL,
  `value` VARCHAR(255) NULL,
  `image` VARCHAR(255) NULL,
  PRIMARY KEY (`value_id`, `conversion_set_id`),
  INDEX `#__rs_prod_av_cx_fk2` (`conversion_set_id` ASC),
  INDEX `#__rs_prod_av_cx_fk1` (`value_id` ASC),
  CONSTRAINT `#__rs_prod_av_cx_fk1`
    FOREIGN KEY (`value_id`)
    REFERENCES `#__redshopb_product_attribute_value` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_prod_av_cx_fk2`
    FOREIGN KEY (`conversion_set_id`)
    REFERENCES `#__redshopb_conversion` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- Inserts conversion values for sizes (Str.)
INSERT INTO `#__redshopb_product_attribute_value_conversion_xref` (`value_id`, `conversion_set_id`, `value`)
SELECT
  `pav`.`id`, `c`.`id`, `pavt`.`string_value`
FROM
  `#__redshopb_product_attribute`  AS `pa`
    INNER JOIN `#__redshopb_product_attribute_value` AS `pav` ON `pa`.`id` = `pav`.`product_attribute_id`
    INNER JOIN `#__redshopb_product_attribute_value_rctranslations` AS `pavt` ON `pav`.`id` = `pavt`.`id`
    INNER JOIN `#__redshopb_conversion` AS `c` ON `c`.`name` = `pavt`.`rctranslations_language`
WHERE
  `pa`.`name` = 'Str.'
  AND NOT EXISTS (
    SELECT
      1
    FROM
      `#__languages` AS `l`
    WHERE
      `l`.`lang_code` =  `pavt`.`rctranslations_language`
  );




-- -----------------------------------------------------
-- Table `#__redshopb_product_item`
-- -----------------------------------------------------
CALL `#__redshopb_product_item_1_6_0`();

DROP PROCEDURE `#__redshopb_product_item_1_6_0`;

ALTER TABLE `#__redshopb_product_item`
  CHANGE COLUMN `upper_level` `stock_upper_level` INT(11) NULL COMMENT 'Above this limit, the stock is considered enough, below it, it presents a first level warning' AFTER `discontinued`,
  CHANGE COLUMN `lower_level` `stock_lower_level` INT(11) NULL COMMENT 'Below this level, it presents an alarm',
  MODIFY COLUMN `amount` INT(10) NOT NULL DEFAULT '0' AFTER `stock_lower_level`,
  ADD INDEX `#__rs_prod_item_fk1` (`product_id` ASC),
  ADD CONSTRAINT `#__rs_prod_item_fk1`
    FOREIGN KEY (`product_id`)
    REFERENCES `#__redshopb_product` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE;


-- -----------------------------------------------------
-- Table `#__redshopb_product_item_attribute_value_xref`
-- -----------------------------------------------------
CALL `#__redshopb_product_item_attribute_value_xref_1_6_0`();

DROP PROCEDURE `#__redshopb_product_item_attribute_value_xref_1_6_0`;

ALTER TABLE `#__redshopb_product_item_attribute_value_xref`
  ADD INDEX `#__rs_prod_iav_fk2` (`product_attribute_value_id` ASC),
  ADD INDEX `#__rs_prod_iav_fk1` (`product_item_id` ASC),
  ADD CONSTRAINT `#__rs_prod_iav_fk1`
    FOREIGN KEY (`product_item_id`)
    REFERENCES `#__redshopb_product_item` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_prod_iav_fk2`
    FOREIGN KEY (`product_attribute_value_id`)
    REFERENCES `#__redshopb_product_attribute_value` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE;


-- -----------------------------------------------------
-- Table `#__redshopb_product_accessory`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `#__redshopb_product_accessory` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` INT(10) UNSIGNED NOT NULL,
  `accessory_product_id` INT(10) UNSIGNED NOT NULL,
  `description` VARCHAR(255) NOT NULL,
  `collection_id` INT(10) UNSIGNED NULL,
  `hide_on_collection` TINYINT(4) NOT NULL DEFAULT '0',
  `price` DECIMAL(10,2) NULL,
  `selection` ENUM('require','proposed','optional') NOT NULL,
  `state` TINYINT(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  INDEX `#__rs_prod_ac_fk2` (`accessory_product_id` ASC),
  INDEX `#__rs_prod_ac_fk1` (`product_id` ASC),
  INDEX `#__rs_prod_ac_fk3` (`collection_id` ASC),
  CONSTRAINT `#__rs_prod_ac_fk1`
    FOREIGN KEY (`product_id`)
    REFERENCES `#__redshopb_product` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_prod_ac_fk2`
    FOREIGN KEY (`accessory_product_id`)
    REFERENCES `#__redshopb_product` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_prod_ac_fk3`
    FOREIGN KEY (`collection_id`)
    REFERENCES `#__redshopb_collection` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `#__redshopb_product_item_accessory`
-- -----------------------------------------------------
CALL `#__redshopb_product_item_accessory_1_6_0`();

DROP PROCEDURE `#__redshopb_product_item_accessory_1_6_0`;

ALTER TABLE `#__redshopb_product_item_accessory`
  MODIFY COLUMN `attribute_value_id` INT(10) UNSIGNED NOT NULL,
  MODIFY COLUMN `accessory_product_id` INT(10) UNSIGNED NOT NULL AFTER `attribute_value_id`,
  CHANGE COLUMN `wardrobe_id` `collection_id` INT(10) UNSIGNED NULL DEFAULT NULL AFTER `description`,
  CHANGE COLUMN `hide_on_wardrobe` `hide_on_collection` TINYINT(4) NOT NULL DEFAULT '0',
  MODIFY COLUMN `price` DECIMAL(10,2) NULL COMMENT 'If price is not present, it uses regular price logic',
  MODIFY COLUMN `selection` ENUM('require','proposed','optional') NOT NULL,
  ADD COLUMN `state` TINYINT(4) NOT NULL DEFAULT '1',
  ADD INDEX `#__rs_prod_iac_fk1` (`accessory_product_id` ASC),
  ADD INDEX `#__rs_prod_iac_fk3` (`collection_id` ASC),
  ADD INDEX `#__rs_prod_iac_fk2` (`attribute_value_id` ASC),
  ADD CONSTRAINT `#__rs_prod_iac_fk1`
    FOREIGN KEY (`accessory_product_id`)
    REFERENCES `#__redshopb_product` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_prod_iac_fk2`
    FOREIGN KEY (`attribute_value_id`)
    REFERENCES `#__redshopb_product_attribute_value` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_prod_iac_fk3`
    FOREIGN KEY (`collection_id`)
    REFERENCES `#__redshopb_collection` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE;


-- -----------------------------------------------------
-- Table `#__redshopb_collection_product_xref`
-- -----------------------------------------------------
RENAME TABLE `#__redshopb_wardrobe_product_xref` TO `#__redshopb_collection_product_xref`;

CALL `#__redshopb_collection_product_xref_1_6_0`();

DROP PROCEDURE `#__redshopb_collection_product_xref_1_6_0`;

ALTER TABLE `#__redshopb_collection_product_xref`
  CHANGE COLUMN `wardrobe_id` `collection_id` INT(10) UNSIGNED NOT NULL,
  ADD COLUMN `price` DECIMAL(10,2) NULL COMMENT '  ',
  ADD COLUMN `state` TINYINT(4) NOT NULL DEFAULT '0',
  ADD PRIMARY KEY (`collection_id`, `product_id`),
  ADD INDEX `#__rs_collprodx_fk2` (`product_id` ASC),
  ADD INDEX `#__rs_collprodx_fk1` (`collection_id` ASC),
  ADD CONSTRAINT `#__rs_collprodx_fk1`
    FOREIGN KEY (`collection_id`)
    REFERENCES `#__redshopb_collection` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_collprodx_fk2`
    FOREIGN KEY (`product_id`)
    REFERENCES `#__redshopb_product` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE;


-- -----------------------------------------------------
-- Table `#__redshopb_collection_product_item_xref`
-- -----------------------------------------------------
RENAME TABLE `#__redshopb_wardrobe_product_item_xref` TO `#__redshopb_collection_product_item_xref`;

CALL `#__redshopb_collection_product_item_1_6_0`();

DROP PROCEDURE `#__redshopb_collection_product_item_1_6_0`;

ALTER TABLE `#__redshopb_collection_product_item_xref`
  CHANGE COLUMN `wardrobe_id` `collection_id` INT(10) UNSIGNED NOT NULL,
  MODIFY COLUMN `price` DECIMAL(10,2) NULL COMMENT 'If price is not present, it uses regular price logic',
  ADD PRIMARY KEY (`collection_id`, `product_item_id`),
  ADD INDEX `#__rs_collpix_fk2` (`product_item_id` ASC),
  ADD INDEX `#__rs_collpix_fk1` (`collection_id` ASC),
  ADD CONSTRAINT `#__rs_collpix_fk1`
    FOREIGN KEY (`collection_id`)
    REFERENCES `#__redshopb_collection` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_collpix_fk2`
    FOREIGN KEY (`product_item_id`)
    REFERENCES `#__redshopb_product_item` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE;


-- -----------------------------------------------------
-- Table `#__redshopb_media`
-- -----------------------------------------------------
CALL `#__redshopb_media_1_6_0`();

DROP PROCEDURE `#__redshopb_media_1_6_0`;

ALTER TABLE `#__redshopb_media`
  MODIFY COLUMN `view` TINYINT(4) NOT NULL COMMENT '1 = Front, 2 = Back, 0 = Other',
  CHANGE COLUMN `color_id` `attribute_value_id` INT(10) UNSIGNED NULL,
  ADD INDEX `#__rs_media_fk1` (`product_id` ASC),
  ADD INDEX `idx_common` (`product_id` ASC, `state` ASC),
  ADD INDEX `#__rs_media_fk2` (`checked_out` ASC),
  ADD INDEX `#__rs_media_fk4` (`modified_by` ASC),
  ADD INDEX `#__rs_media_fk5` (`attribute_value_id` ASC),
  ADD INDEX `#__rs_media_fk3` (`created_by` ASC),
  ADD CONSTRAINT `#__rs_media_fk1`
    FOREIGN KEY (`product_id`)
    REFERENCES `#__redshopb_product` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_media_fk2`
    FOREIGN KEY (`checked_out`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_media_fk3`
    FOREIGN KEY (`created_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_media_fk4`
    FOREIGN KEY (`modified_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_media_fk5`
    FOREIGN KEY (`attribute_value_id`)
    REFERENCES `#__redshopb_product_attribute_value` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE;


-- -----------------------------------------------------
-- Table `#__redshopb_category`
-- -----------------------------------------------------
CALL `#__redshopb_category_1_6_0`();

DROP PROCEDURE `#__redshopb_category_1_6_0`;

ALTER TABLE `#__redshopb_category`
  CHANGE COLUMN `title` `name` VARCHAR(255) NOT NULL AFTER `id`,
  MODIFY COLUMN `alias` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_bin' NOT NULL DEFAULT '' AFTER `name`,
  MODIFY COLUMN `company_id` INT(10) UNSIGNED NULL DEFAULT NULL AFTER `alias`,
  ADD COLUMN `template_id` INT(10) NULL AFTER `state`,
  ADD INDEX `#__rs_categ_fk1` (`checked_out` ASC),
  ADD INDEX `#__rs_categ_fk2` (`created_by` ASC),
  ADD INDEX `#__rs_categ_fk3` (`modified_by` ASC),
  ADD INDEX `#__rs_categ_fk4` (`company_id` ASC),
  ADD INDEX `#__rs_categ_fk5` (`parent_id` ASC),
  ADD INDEX `#__rs_categ_fk6` (`template_id` ASC),
  ADD CONSTRAINT `#__rs_categ_fk1`
    FOREIGN KEY (`checked_out`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_categ_fk2`
    FOREIGN KEY (`created_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_categ_fk3`
    FOREIGN KEY (`modified_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_categ_fk4`
    FOREIGN KEY (`company_id`)
    REFERENCES `#__redshopb_company` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_categ_fk5`
    FOREIGN KEY (`parent_id`)
    REFERENCES `#__redshopb_category` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_categ_fk6`
    FOREIGN KEY (`template_id`)
    REFERENCES `#__redshopb_template` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE;


-- -----------------------------------------------------
-- Table `#__redshopb_category_rctranslations`
-- -----------------------------------------------------
ALTER TABLE `#__redshopb_category_rctranslations`
  CHANGE COLUMN `title` `name` VARCHAR(255);


-- -----------------------------------------------------
-- Table `#__redshopb_product_price`
-- -----------------------------------------------------
CALL `#__redshopb_product_price_1_6_0`();

DROP PROCEDURE `#__redshopb_product_price_1_6_0`;

ALTER TABLE `#__redshopb_product_price`
  MODIFY COLUMN `sales_type` ENUM('all_customers','customer_price_group','customer','campaign') NOT NULL DEFAULT 'all_customers',
  ADD `checked_out` INT(11) NULL,
  ADD `checked_out_time` DATETIME NULL,
  ADD `created_by` INT(11) NULL,
  ADD `created_date` DATETIME NULL,
  ADD `modified_by` INT(11) NULL,
  ADD `modified_date` DATETIME NULL,
  CHANGE COLUMN `retail_price` `temp_retail_price` DECIMAL(10,2) NOT NULL AFTER `modified_date`,
  ADD INDEX `#__rs_pprice_fk1` (`country_id` ASC),
  ADD INDEX `#__rs_pprice_fk2` (`currency_id` ASC),
  ADD INDEX `#__rs_pprice_fk3` (`checked_out` ASC),
  ADD INDEX `#__rs_pprice_fk4` (`created_by` ASC),
  ADD INDEX `#__rs_pprice_fk5` (`modified_by` ASC),
  ADD CONSTRAINT `#__rs_pprice_fk1`
    FOREIGN KEY (`country_id`)
    REFERENCES `#__redshopb_country` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_pprice_fk2`
    FOREIGN KEY (`currency_id`)
    REFERENCES `#__redshopb_currency` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_pprice_fk3`
    FOREIGN KEY (`checked_out`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_pprice_fk4`
    FOREIGN KEY (`created_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_pprice_fk5`
    FOREIGN KEY (`modified_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE;


-- -----------------------------------------------------
-- Table `#__redshopb_customer_price_group`
-- -----------------------------------------------------
CALL `#__redshopb_customer_price_group_1_6_0`();

DROP PROCEDURE `#__redshopb_customer_price_group_1_6_0`;

ALTER TABLE `#__redshopb_customer_price_group`
  ADD INDEX `#__rs_custpg_fk1` (`company_id` ASC),
  ADD INDEX `#__rs_custpg_fk4` (`modified_by` ASC),
  ADD INDEX `#__rs_custpg_fk3` (`created_by` ASC),
  ADD INDEX `#__rs_custpg_fk2` (`checked_out` ASC),
  ADD CONSTRAINT `#__rs_custpg_fk1`
    FOREIGN KEY (`company_id`)
    REFERENCES `#__redshopb_company` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_custpg_fk2`
    FOREIGN KEY (`checked_out`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_custpg_fk3`
    FOREIGN KEY (`created_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_custpg_fk4`
    FOREIGN KEY (`modified_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE;


-- -----------------------------------------------------
-- Table `#__redshopb_customer_price_group_xref`
-- -----------------------------------------------------
CALL `#__redshopb_customer_price_group_xref_1_6_0`();

DROP PROCEDURE `#__redshopb_customer_price_group_xref_1_6_0`;

ALTER TABLE `#__redshopb_customer_price_group_xref`
  ADD INDEX `#__rs_custpgx_pk1` (`price_group_id` ASC),
  ADD INDEX `#__rs_custpgx_pk2` (`customer_id` ASC),
  ADD CONSTRAINT `#__rs_custpgx_pk1`
    FOREIGN KEY (`price_group_id`)
    REFERENCES `#__redshopb_customer_price_group` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_custpgx_pk2`
    FOREIGN KEY (`customer_id`)
    REFERENCES `#__redshopb_company` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE;


-- -----------------------------------------------------
-- Table `#__redshopb_product_discount`
-- -----------------------------------------------------
CALL `#__redshopb_product_discount_1_6_0`();

DROP PROCEDURE `#__redshopb_product_discount_1_6_0`;

ALTER TABLE `#__redshopb_product_discount`
  ADD INDEX `#__rs_pdisc_fk1` (`currency_id` ASC),
  ADD INDEX `#__rs_pdisc_fk2` (`checked_out` ASC),
  ADD INDEX `#__rs_pdisc_fk3` (`created_by` ASC),
  ADD INDEX `#__rs_pdisc_fk4` (`modified_by` ASC),
  ADD CONSTRAINT `#__rs_pdisc_fk1`
    FOREIGN KEY (`currency_id`)
    REFERENCES `#__redshopb_currency` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_pdisc_fk2`
    FOREIGN KEY (`checked_out`)
    REFERENCES `#__users` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  ADD CONSTRAINT `#__rs_pdisc_fk3`
    FOREIGN KEY (`created_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  ADD CONSTRAINT `#__rs_pdisc_fk4`
    FOREIGN KEY (`modified_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION;


-- -----------------------------------------------------
-- Table `#__redshopb_product_discount_group`
-- -----------------------------------------------------
CALL `#__redshopb_product_discount_group_1_6_0`();

DROP PROCEDURE `#__redshopb_product_discount_group_1_6_0`;

ALTER TABLE `#__redshopb_product_discount_group`
  ADD INDEX `#__rs_proddg_fk1` (`company_id` ASC),
  ADD INDEX `#__rs_proddg_fk3` (`created_by` ASC),
  ADD INDEX `#__rs_proddg_fk2` (`checked_out` ASC),
  ADD INDEX `#__rs_proddg_fk4` (`modified_by` ASC),
  ADD CONSTRAINT `#__rs_proddg_fk1`
    FOREIGN KEY (`company_id`)
    REFERENCES `#__redshopb_company` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_proddg_fk3`
    FOREIGN KEY (`created_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_proddg_fk2`
    FOREIGN KEY (`checked_out`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_proddg_fk4`
    FOREIGN KEY (`modified_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE;


-- -----------------------------------------------------
-- Table `#__redshopb_product_discount_group_xref`
-- -----------------------------------------------------
CALL `#__redshopb_product_discount_group_xref_1_6_0`();

DROP PROCEDURE `#__redshopb_product_discount_group_xref_1_6_0`;

ALTER TABLE `#__redshopb_product_discount_group_xref`
  ADD INDEX `#__rs_prod_dgx_fk1` (`discount_group_id` ASC),
  ADD INDEX `#__rs_prod_dgx_fk2` (`product_id` ASC),
  ADD CONSTRAINT `#__rs_prod_dgx_fk1`
    FOREIGN KEY (`discount_group_id`)
    REFERENCES `#__redshopb_product_discount_group` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_prod_dgx_fk2`
    FOREIGN KEY (`product_id`)
    REFERENCES `#__redshopb_product` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE;


-- -----------------------------------------------------
-- Table `#__redshopb_customer_discount_group`
-- -----------------------------------------------------
CALL `#__redshopb_customer_discount_group_1_6_0`();

DROP PROCEDURE `#__redshopb_customer_discount_group_1_6_0`;

ALTER TABLE `#__redshopb_customer_discount_group`
  ADD INDEX `#__rs_custdg_fk1` (`company_id` ASC),
  ADD INDEX `#__rs_custdg_fk3` (`created_by` ASC),
  ADD INDEX `#__rs_custdg_fk2` (`checked_out` ASC),
  ADD INDEX `#__rs_custdg_fk4` (`modified_by` ASC),
  ADD CONSTRAINT `#__rs_custdg_fk1`
    FOREIGN KEY (`company_id`)
    REFERENCES `#__redshopb_company` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_custdg_fk3`
    FOREIGN KEY (`created_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_custdg_fk2`
    FOREIGN KEY (`checked_out`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_custdg_fk4`
    FOREIGN KEY (`modified_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE;


-- -----------------------------------------------------
-- Table `#__redshopb_customer_discount_group_xref`
-- -----------------------------------------------------
CALL `#__redshopb_customer_discount_group_xref_1_6_0`();

DROP PROCEDURE `#__redshopb_customer_discount_group_xref_1_6_0`;

ALTER TABLE `#__redshopb_customer_discount_group_xref`
  ADD INDEX `#__rs_custdgx_fk1` (`discount_group_id` ASC),
  ADD INDEX `#__rs_custdgx_fk2` (`customer_id` ASC),
  ADD CONSTRAINT `#__rs_custdgx_fk1`
    FOREIGN KEY (`discount_group_id`)
    REFERENCES `#__redshopb_customer_discount_group` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_custdgx_fk2`
    FOREIGN KEY (`customer_id`)
    REFERENCES `#__redshopb_company` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE;


-- -----------------------------------------------------
-- Table `#__redshopb_stockroom`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_stockroom`;
DROP TABLE IF EXISTS `#__redshopb_company_stockroom_xref`;

CREATE TABLE IF NOT EXISTS `#__redshopb_stockroom` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `alias` VARCHAR(255) NOT NULL,
  `description` VARCHAR(2048) NULL,
  `company_id` INT(10) UNSIGNED NULL,
  `address_id` INT(10) UNSIGNED NULL,
  `min_delivery_time` INT(11) NULL,
  `max_delivery_time` INT(11) NULL,
  `stock_upper_level` INT(11) NULL COMMENT 'Above this limit, the stock is considered enough, below it, it presents a first level warning',
  `stock_lower_level` INT(11) NULL COMMENT 'Below this level, it presents an alarm',
  `order` INT NULL COMMENT 'Decides the order selection of stockrooms of the company',
  `checked_out` INT(11) NULL,
  `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` INT(11) NULL,
  `created_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` INT(11) NULL,
  `modified_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  INDEX `#__rs_stockroom_fk1` (`address_id` ASC),
  INDEX `#__rs_stockroom_fk2` (`company_id` ASC),
  INDEX `#__rs_stockroom_fk3` (`checked_out` ASC),
  INDEX `#__rs_stockroom_fk4` (`created_by` ASC),
  INDEX `#__rs_stockroom_fk5` (`modified_by` ASC),
  CONSTRAINT `#__rs_stockroom_fk1`
    FOREIGN KEY (`address_id`)
    REFERENCES `#__redshopb_address` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_stockroom_fk2`
    FOREIGN KEY (`company_id`)
    REFERENCES `#__redshopb_company` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_stockroom_fk3`
    FOREIGN KEY (`checked_out`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_stockroom_fk4`
    FOREIGN KEY (`created_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_stockroom_fk5`
    FOREIGN KEY (`modified_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `#__redshopb_stockroom_product_xref`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `#__redshopb_stockroom_product_xref` (
  `stockroom_id` INT(10) UNSIGNED NOT NULL,
  `product_id` INT(10) UNSIGNED NOT NULL,
  `amount` INT(11) NOT NULL DEFAULT 0,
  `unlimited` TINYINT(4) NOT NULL DEFAULT '0',
  `stock_upper_level` INT(11) NULL,
  `stock_lower_level` INT(11) NULL,
  PRIMARY KEY (`stockroom_id`, `product_id`),
  INDEX `#__rs_stockroom_prod_fk2` (`product_id` ASC),
  INDEX `#__rs_stockroom_prod_fk1` (`stockroom_id` ASC),
  CONSTRAINT `#__rs_stockroom_prod_fk1`
    FOREIGN KEY (`stockroom_id`)
    REFERENCES `#__redshopb_stockroom` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_stockroom_prod_fk2`
    FOREIGN KEY (`product_id`)
    REFERENCES `#__redshopb_product` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `#__redshopb_stockroom_product_item_xref`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `#__redshopb_stockroom_product_item_xref` (
  `stockroom_id` INT(10) UNSIGNED NOT NULL,
  `product_item_id` INT(10) UNSIGNED NOT NULL,
  `amount` INT(11) NOT NULL DEFAULT 0,
  `unlimited` TINYINT(4) NOT NULL DEFAULT '0',
  `stock_upper_level` INT(11) NULL,
  `stock_lower_level` INT(11) NULL,
  PRIMARY KEY (`stockroom_id`, `product_item_id`),
  INDEX `#__rs_stockroom_pi_fk2` (`product_item_id` ASC),
  INDEX `#__rs_stockroom_pi_fk1` (`stockroom_id` ASC),
  CONSTRAINT `#__rs_stockroom_pi_fk1`
    FOREIGN KEY (`stockroom_id`)
    REFERENCES `#__redshopb_stockroom` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_stockroom_pi_fk2`
    FOREIGN KEY (`product_item_id`)
    REFERENCES `#__redshopb_product_item` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- Move product item amounts into stockroom (when stock is present)
CALL `#__redshopb_stockroom_1_6_0`();

DROP PROCEDURE `#__redshopb_stockroom_1_6_0`;

ALTER TABLE `#__redshopb_product_item`
  DROP COLUMN `amount`;


-- -----------------------------------------------------
-- Table `#__redshopb_address`
-- -----------------------------------------------------
CALL `#__redshopb_address_1_6_0`();

DROP PROCEDURE `#__redshopb_address_1_6_0`;

ALTER TABLE `#__redshopb_address`
  DROP COLUMN `active_from_order`,
  MODIFY COLUMN `country_id` SMALLINT(5) UNSIGNED NULL DEFAULT NULL AFTER `city`,
  MODIFY COLUMN `code` VARCHAR(255) NOT NULL AFTER `country_id`,
  ADD INDEX `#__rs_address_fk1` (`country_id` ASC),
  ADD INDEX `#__rs_address_fk2` (`checked_out` ASC),
  ADD INDEX `#__rs_address_fk3` (`created_by` ASC),
  ADD INDEX `#__rs_address_fk4` (`modified_by` ASC),
  ADD CONSTRAINT `#__rs_address_fk1`
    FOREIGN KEY (`country_id`)
    REFERENCES `#__redshopb_country` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_address_fk2`
    FOREIGN KEY (`checked_out`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_address_fk3`
    FOREIGN KEY (`created_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_address_fk4`
    FOREIGN KEY (`modified_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE;


-- -----------------------------------------------------
-- Table `#__redshopb_company`
-- -----------------------------------------------------
CALL `#__redshopb_company_1_6_0`();

DROP PROCEDURE `#__redshopb_company_1_6_0`;

ALTER TABLE `#__redshopb_company`
  MODIFY COLUMN `name` VARCHAR(255) NOT NULL AFTER `id`,
  MODIFY COLUMN `name2` VARCHAR(255) NOT NULL AFTER `name`,
  MODIFY COLUMN `customer_number` VARCHAR(255) NOT NULL DEFAULT '' AFTER `name2`,
  MODIFY COLUMN `type` ENUM('','main','customer','end_customer') NOT NULL DEFAULT '' AFTER `customer_number`,
  MODIFY COLUMN `state` TINYINT(4) NOT NULL DEFAULT '1',
  MODIFY COLUMN `layout_id` INT(10) UNSIGNED NULL DEFAULT NULL COMMENT 'Default layout to load when this company uses the system',
  MODIFY COLUMN `show_stock_as` ENUM('actual_stock','color_codes','hide') NOT NULL DEFAULT 'actual_stock',
  MODIFY COLUMN `currency_id` SMALLINT(5) UNSIGNED NULL DEFAULT NULL AFTER `contact_info`,
  MODIFY COLUMN `phone` VARCHAR(255) NOT NULL AFTER `use_wallets`,
  ADD COLUMN `conversion_id` INT(10) NULL AFTER `site_language`,
  MODIFY COLUMN `freight_amount_limit` DECIMAL(10,2) NULL DEFAULT 0 COMMENT 'Below this purchase limit, freight will be added',
  MODIFY COLUMN `freight_amount` DECIMAL(10,2) NULL DEFAULT 0 COMMENT 'Freight amount to add if the purchase limit is not reached',
  CHANGE COLUMN `product_id` `freight_product_id` INT(10) UNSIGNED NULL DEFAULT NULL COMMENT 'Freight product to be added',
  MODIFY COLUMN `send_mail_on_order` TINYINT(1) NOT NULL COMMENT 'If enabled, this company admins will receive emails on order placing',
  ADD COLUMN `b2c` TINYINT(4) NOT NULL DEFAULT '0' COMMENT 'B2C company for end customers' AFTER `send_mail_on_order`,
  ADD COLUMN `use_collections` TINYINT(4) NOT NULL DEFAULT '0' COMMENT 'Defines if collections are enabled for this company, otherwise category shopping is displayed' AFTER `b2c`,
  ADD COLUMN `checked_out` INT(11) NULL AFTER `use_collections`,
  ADD COLUMN `checked_out_time` DATETIME NULL AFTER  `checked_out`,
  MODIFY COLUMN `size_language` VARCHAR(7) NOT NULL AFTER `deleted`,
  CHANGE COLUMN `show_retail_price` `temp_show_retail_price` TINYINT(1) NOT NULL DEFAULT '0' AFTER `size_language`,
  ADD INDEX `#__rs_company_fk1` (`address_id` ASC),
  ADD INDEX `#__rs_company_fk2` (`asset_id` ASC),
  ADD INDEX `#__rs_company_fk3` (`parent_id` ASC),
  ADD INDEX `#__rs_company_fk5` (`created_by` ASC),
  ADD INDEX `#__rs_company_fk6` (`modified_by` ASC),
  ADD INDEX `#__rs_company_fk7` (`layout_id` ASC),
  ADD INDEX `#__rs_company_fk8` (`currency_id` ASC),
  ADD INDEX `#__rs_company_fk9` (`conversion_id` ASC),
  ADD INDEX `#__rs_company_fk4` (`checked_out` ASC),
  ADD CONSTRAINT `#__rs_company_fk1`
    FOREIGN KEY (`address_id`)
    REFERENCES `#__redshopb_address` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_company_fk2`
    FOREIGN KEY (`asset_id`)
    REFERENCES `#__assets` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_company_fk3`
    FOREIGN KEY (`parent_id`)
    REFERENCES `#__redshopb_company` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_company_fk5`
    FOREIGN KEY (`created_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_company_fk6`
    FOREIGN KEY (`modified_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_company_fk7`
    FOREIGN KEY (`layout_id`)
    REFERENCES `#__redshopb_layout` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_company_fk8`
    FOREIGN KEY (`currency_id`)
    REFERENCES `#__redshopb_currency` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_company_fk9`
    FOREIGN KEY (`conversion_id`)
    REFERENCES `#__redshopb_conversion` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_company_fk4`
    FOREIGN KEY (`checked_out`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE;


-- Moving size_language to conversion_id using the inserted conversion sets
UPDATE
  `#__redshopb_company` AS `comp`, `#__redshopb_conversion` AS `c`
SET
  `comp`.`conversion_id` = `c`.`id`
WHERE
  `comp`.`size_language` = `c`.`name`;

ALTER TABLE `#__redshopb_company`
  DROP COLUMN `size_language`;

-- -----------------------------------------------------
-- Table `#__redshopb_collection`
-- -----------------------------------------------------
RENAME TABLE `#__redshopb_wardrobe` TO `#__redshopb_collection`;

CALL `#__redshopb_collection_1_6_0`();

DROP PROCEDURE `#__redshopb_collection_1_6_0`;

ALTER TABLE `#__redshopb_collection`
  ADD INDEX `#__rs_collection_fk1` (`company_id` ASC),
  ADD INDEX `#__rs_collection_fk2` (`checked_out` ASC),
  ADD INDEX `#__rs_collection_fk3` (`created_by` ASC),
  ADD INDEX `#__rs_collection_fk4` (`modified_by` ASC),
  ADD INDEX `#__rs_collection_fk5` (`currency_id` ASC),
  ADD CONSTRAINT `#__rs_collection_fk1`
    FOREIGN KEY (`company_id`)
    REFERENCES `#__redshopb_company` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_collection_fk2`
    FOREIGN KEY (`checked_out`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_collection_fk3`
    FOREIGN KEY (`created_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_collection_fk4`
    FOREIGN KEY (`modified_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_collection_fk5`
    FOREIGN KEY (`currency_id`)
    REFERENCES `#__redshopb_currency` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE;


-- -----------------------------------------------------
-- Table `#__redshopb_department`
-- -----------------------------------------------------
CALL `#__redshopb_department_1_6_0`();

DROP PROCEDURE `#__redshopb_department_1_6_0`;

ALTER TABLE `#__redshopb_department`
  MODIFY COLUMN `name` VARCHAR(255) NOT NULL AFTER `id`,
  MODIFY COLUMN `name2` VARCHAR(255) NOT NULL AFTER `name`,
  MODIFY COLUMN `company_id` INT(10) UNSIGNED NOT NULL AFTER `name2`,
  MODIFY COLUMN `parent_id` INT(10) UNSIGNED NULL DEFAULT NULL AFTER `asset_id`,
  ADD COLUMN `state` TINYINT(4) NOT NULL DEFAULT '1' AFTER `address_id`,
  DROP COLUMN `send_mail_on_order`,
  ADD INDEX `#__rs_dept_fk1` (`address_id` ASC),
  ADD INDEX `#__rs_dept_fk2` (`asset_id` ASC),
  ADD INDEX `#__rs_dept_fk3` (`company_id` ASC),
  ADD INDEX `#__rs_dept_fk4` (`checked_out` ASC),
  ADD INDEX `#__rs_dept_fk5` (`created_by` ASC),
  ADD INDEX `#__rs_dept_fk6` (`modified_by` ASC),
  ADD INDEX `#__rs_dept_fk7` (`parent_id` ASC),
  ADD CONSTRAINT `#__rs_dept_fk1`
    FOREIGN KEY (`address_id`)
    REFERENCES `#__redshopb_address` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_dept_fk2`
    FOREIGN KEY (`asset_id`)
    REFERENCES `#__assets` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_dept_fk3`
    FOREIGN KEY (`company_id`)
    REFERENCES `#__redshopb_company` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_dept_fk4`
    FOREIGN KEY (`checked_out`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_dept_fk5`
    FOREIGN KEY (`created_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_dept_fk6`
    FOREIGN KEY (`modified_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_dept_fk7`
    FOREIGN KEY (`parent_id`)
    REFERENCES `#__redshopb_department` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE;


-- -----------------------------------------------------
-- Table `#__redshopb_collection_department_xref`
-- -----------------------------------------------------
RENAME TABLE `#__redshopb_wardrobe_department_xref` TO `#__redshopb_collection_department_xref`;

CALL `#__redshopb_collection_department_xref_1_6_0`();

DROP PROCEDURE `#__redshopb_collection_department_xref_1_6_0`;

ALTER TABLE `#__redshopb_collection_department_xref`
  CHANGE COLUMN `wardrobe_id` `collection_id` INT(10) UNSIGNED NOT NULL,
  ADD PRIMARY KEY (`collection_id`, `department_id`),
  ADD INDEX `#__rs_colldept_fk2` (`department_id` ASC),
  ADD INDEX `#__rs_colldept_fk1` (`collection_id` ASC),
  ADD CONSTRAINT `#__rs_colldept_fk1`
    FOREIGN KEY (`collection_id`)
    REFERENCES `#__redshopb_collection` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_colldept_fk2`
    FOREIGN KEY (`department_id`)
    REFERENCES `#__redshopb_department` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE;


-- -----------------------------------------------------
-- Table `#__redshopb_user`
-- -----------------------------------------------------
CALL `#__redshopb_user_1_6_0`();

DROP PROCEDURE `#__redshopb_user_1_6_0`;

ALTER TABLE `#__redshopb_user`
  MODIFY COLUMN `name1` VARCHAR(255) NOT NULL AFTER `id`,
  MODIFY COLUMN `name2` VARCHAR(255) NOT NULL AFTER `name1`,
  MODIFY COLUMN `printed_name` VARCHAR(255) NULL DEFAULT NULL AFTER `name2`,
  CHANGE COLUMN `useCompanyEmail` `use_company_email` TINYINT(4) NOT NULL DEFAULT '0' COMMENT 'If enabled, it will use a random email using the company alias',
  ADD INDEX `#__rs_user_fk1` (`joomla_user_id` ASC),
  ADD INDEX `#__rs_user_fk2` (`company_id` ASC),
  ADD INDEX `#__rs_user_fk3` (`department_id` ASC),
  ADD INDEX `#__rs_user_fk4` (`address_id` ASC),
  ADD INDEX `#__rs_user_fk5` (`checked_out` ASC),
  ADD INDEX `#__rs_user_fk6` (`created_by` ASC),
  ADD INDEX `#__rs_user_fk7` (`modified_by` ASC),
  ADD INDEX `#__rs_user_fk8` (`wallet_id` ASC),
  ADD CONSTRAINT `#__rs_user_fk1`
    FOREIGN KEY (`joomla_user_id`)
    REFERENCES `#__users` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_user_fk2`
    FOREIGN KEY (`company_id`)
    REFERENCES `#__redshopb_company` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_user_fk3`
    FOREIGN KEY (`department_id`)
    REFERENCES `#__redshopb_department` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_user_fk4`
    FOREIGN KEY (`address_id`)
    REFERENCES `#__redshopb_address` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_user_fk5`
    FOREIGN KEY (`checked_out`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_user_fk6`
    FOREIGN KEY (`created_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_user_fk7`
    FOREIGN KEY (`modified_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_user_fk8`
    FOREIGN KEY (`wallet_id`)
    REFERENCES `#__redshopb_wallet` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE;


-- -----------------------------------------------------
-- Table `#__redshopb_wallet`
-- -----------------------------------------------------
CALL `#__redshopb_wallet_1_6_0`();

DROP PROCEDURE `#__redshopb_wallet_1_6_0`;

ALTER TABLE `#__redshopb_wallet`
  ADD INDEX `#__rs_wallet_fk1` (`checked_out` ASC),
  ADD INDEX `#__rs_wallet_fk2` (`created_by` ASC),
  ADD INDEX `#__rs_wallet_fk3` (`modified_by` ASC),
  ADD CONSTRAINT `#__rs_wallet_fk1`
    FOREIGN KEY (`checked_out`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_wallet_fk2`
    FOREIGN KEY (`created_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_wallet_fk3`
    FOREIGN KEY (`modified_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE;


-- -----------------------------------------------------
-- Table `#__redshopb_wallet_money`
-- -----------------------------------------------------
CALL `#__redshopb_wallet_money_1_6_0`();

DROP PROCEDURE `#__redshopb_wallet_money_1_6_0`;

ALTER TABLE `#__redshopb_wallet_money`
  ADD INDEX `#__rs_walletm_fk2` (`currency_id` ASC),
  ADD INDEX `#__rs_walletm_fk3` (`checked_out` ASC),
  ADD INDEX `#__rs_walletm_fk4` (`created_by` ASC),
  ADD INDEX `#__rs_walletm_fk5` (`modified_by` ASC),
  ADD INDEX `#__rs_walletm_fk1` (`wallet_id` ASC),
  ADD CONSTRAINT `#__rs_walletm_fk1`
    FOREIGN KEY (`wallet_id`)
    REFERENCES `#__redshopb_wallet` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_walletm_fk2`
    FOREIGN KEY (`currency_id`)
    REFERENCES `#__redshopb_currency` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_walletm_fk3`
    FOREIGN KEY (`checked_out`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_walletm_fk4`
    FOREIGN KEY (`created_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_walletm_fk5`
    FOREIGN KEY (`modified_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE;


-- -----------------------------------------------------
-- Table `#__redshopb_company_sales_person_xref`
-- -----------------------------------------------------
CALL `#__redshopb_company_sales_person_xref_1_6_0`();

DROP PROCEDURE `#__redshopb_company_sales_person_xref_1_6_0`;

ALTER TABLE `#__redshopb_company_sales_person_xref`
  ADD INDEX `#__rs_companysp_fk1` (`company_id` ASC),
  ADD INDEX `#__rs_companysp_fk2` (`user_id` ASC),
  ADD CONSTRAINT `#__rs_companysp_fk1`
    FOREIGN KEY (`company_id`)
    REFERENCES `#__redshopb_company` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_companysp_fk2`
    FOREIGN KEY (`user_id`)
    REFERENCES `#__redshopb_user` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE;


-- -----------------------------------------------------
-- Table `#__redshopb_usergroup_sales_person_xref`
-- -----------------------------------------------------
CALL `#__redshopb_usergroup_sales_person_xref_1_6_0`();

DROP PROCEDURE `#__redshopb_usergroup_sales_person_xref_1_6_0`;

ALTER TABLE `#__redshopb_usergroup_sales_person_xref`
  ADD INDEX `#__rs_usergroupsp_fk2` (`joomla_group_id` ASC),
  ADD INDEX `#__rs_usergroupsp_fk1` (`user_id` ASC),
  ADD CONSTRAINT `#__rs_usergroupsp_fk1`
    FOREIGN KEY (`user_id`)
    REFERENCES `#__redshopb_user` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_usergroupsp_fk2`
    FOREIGN KEY (`joomla_group_id`)
    REFERENCES `#__usergroups` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE;


-- -----------------------------------------------------
-- Table `#__redshopb_role`
-- -----------------------------------------------------
CALL `#__redshopb_role_1_6_0`();

DROP PROCEDURE `#__redshopb_role_1_6_0`;

ALTER TABLE `#__redshopb_role`
  ADD INDEX `#__rs_role_fk2` (`company_id` ASC),
  ADD INDEX `#__rs_role_fk3` (`joomla_group_id` ASC),
  ADD INDEX `#__rs_role_fk4` (`checked_out` ASC),
  ADD INDEX `#__rs_role_fk5` (`created_by` ASC),
  ADD INDEX `#__rs_role_fk6` (`modified_by` ASC),
  ADD INDEX `#__rs_role_fk1` (`role_type_id` ASC),
  ADD CONSTRAINT `#__rs_role_fk1`
    FOREIGN KEY (`role_type_id`)
    REFERENCES `#__redshopb_role_type` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_role_fk2`
    FOREIGN KEY (`company_id`)
    REFERENCES `#__redshopb_company` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_role_fk3`
    FOREIGN KEY (`joomla_group_id`)
    REFERENCES `#__usergroups` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_role_fk4`
    FOREIGN KEY (`checked_out`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_role_fk5`
    FOREIGN KEY (`created_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_role_fk6`
    FOREIGN KEY (`modified_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE;


-- -----------------------------------------------------
-- Table `#__redshopb_config`
-- -----------------------------------------------------
ALTER TABLE `#__redshopb_config`
  MODIFY COLUMN `id` INT(10) UNSIGNED NOT NULL;


-- -----------------------------------------------------
-- Table `#__redshopb_layout`
-- -----------------------------------------------------
CALL `#__redshopb_layout_1_6_0`();

DROP PROCEDURE `#__redshopb_layout_1_6_0`;

ALTER TABLE `#__redshopb_layout`
  ADD COLUMN `checked_out` INT(11) NULL DEFAULT NULL AFTER `params`,
  ADD COLUMN `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `checked_out`,
  ADD INDEX `#__rs_layout_fk1` (`company_id` ASC),
  ADD INDEX `#__rs_layout_fk3` (`created_by` ASC),
  ADD INDEX `#__rs_layout_fk4` (`modified_by` ASC),
  ADD INDEX `#__rs_layout_fk2` (`checked_out` ASC),
  ADD CONSTRAINT `#__rs_layout_fk1`
    FOREIGN KEY (`company_id`)
    REFERENCES `#__redshopb_company` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_layout_fk3`
    FOREIGN KEY (`created_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_layout_fk4`
    FOREIGN KEY (`modified_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_layout_fk2`
    FOREIGN KEY (`checked_out`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE;


-- -----------------------------------------------------
-- Table `#__redshopb_cron`
-- -----------------------------------------------------

-- No changes needed


-- -----------------------------------------------------
-- Table `#__redshopb_logos`
-- -----------------------------------------------------
CALL `#__redshopb_logos_1_6_0`();

DROP PROCEDURE `#__redshopb_logos_1_6_0`;

ALTER TABLE `#__redshopb_logos`
  MODIFY COLUMN `brand_id` INT(10) UNSIGNED NULL COMMENT 'References a certain brand created as a tag',
  ADD INDEX `#__rs_logos_fk1` (`brand_id` ASC),
  ADD CONSTRAINT `#__rs_logos_fk1`
    FOREIGN KEY (`brand_id`)
    REFERENCES `#__redshopb_tag` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION;


-- -----------------------------------------------------
-- Table `#__redshopb_fee`
-- -----------------------------------------------------
CALL `#__redshopb_fee_1_6_0`();

DROP PROCEDURE `#__redshopb_fee_1_6_0`;

ALTER TABLE `#__redshopb_fee`
  ADD INDEX `#__rs_fee_fk1` (`currency_id` ASC),
  ADD INDEX `#__rs_fee_fk2` (`product_id` ASC),
  ADD CONSTRAINT `#__rs_fee_fk1`
    FOREIGN KEY (`currency_id`)
    REFERENCES `#__redshopb_currency` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_fee_fk2`
    FOREIGN KEY (`product_id`)
    REFERENCES `#__redshopb_product` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE;


-- -----------------------------------------------------
-- Table `#__redshopb_sync`
-- -----------------------------------------------------

-- No changes needed


-- -----------------------------------------------------
-- Table `#__redshopb_wash_care_spec`
-- -----------------------------------------------------

-- No changes needed


-- -----------------------------------------------------
-- Table `#__redshopb_order`
-- -----------------------------------------------------
CALL `#__redshopb_order_1_6_0`();

DROP PROCEDURE `#__redshopb_order_1_6_0`;

ALTER TABLE `#__redshopb_order`
  CHANGE COLUMN `delivery_address_code` `temp_delivery_address_code` VARCHAR(255) NOT NULL AFTER `modified_date`,
  CHANGE COLUMN `delivery_address_type` `temp_delivery_address_type` ENUM('employee','department','company','') NOT NULL DEFAULT '' AFTER `modified_date`,
  ADD INDEX `#__rs_order_fk7` (`delivery_address_id` ASC),
  ADD INDEX `#__rs_order_fk6` (`currency` ASC),
  ADD INDEX `#__rs_order_fk1` (`checked_out` ASC),
  ADD INDEX `#__rs_order_fk2` (`created_by` ASC),
  ADD INDEX `#__rs_order_fk3` (`modified_by` ASC),
  ADD INDEX `#__rs_order_fk4` (`customer_company` ASC),
  ADD INDEX `#__rs_order_fk5` (`customer_department` ASC),
  ADD CONSTRAINT `#__rs_order_fk1`
    FOREIGN KEY (`checked_out`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_order_fk2`
    FOREIGN KEY (`created_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_order_fk3`
    FOREIGN KEY (`modified_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_order_fk4`
    FOREIGN KEY (`customer_company`)
    REFERENCES `#__redshopb_company` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  ADD CONSTRAINT `#__rs_order_fk5`
    FOREIGN KEY (`customer_department`)
    REFERENCES `#__redshopb_department` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  ADD CONSTRAINT `#__rs_order_fk6`
    FOREIGN KEY (`currency_id`)
    REFERENCES `#__redshopb_currency` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  ADD CONSTRAINT `#__rs_order_fk7`
    FOREIGN KEY (`delivery_address_id`)
    REFERENCES `#__redshopb_address` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION;


-- -----------------------------------------------------
-- Table `#__redshopb_order_logs`
-- -----------------------------------------------------
CALL `#__redshopb_order_logs_1_6_0`();

DROP PROCEDURE `#__redshopb_order_logs_1_6_0`;

ALTER TABLE `#__redshopb_order_logs`
  ADD INDEX `#__rs_orderlogs_fk2` (`order_id` ASC),
  ADD INDEX `#__rs_orderlogs_fk1` (`new_order_id` ASC),
  ADD CONSTRAINT `#__rs_orderlogs_fk1`
    FOREIGN KEY (`new_order_id`)
    REFERENCES `#__redshopb_order` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_orderlogs_fk2`
    FOREIGN KEY (`order_id`)
    REFERENCES `#__redshopb_order` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE;


-- -----------------------------------------------------
-- Table `#__redshopb_order_item`
-- -----------------------------------------------------
CALL `#__redshopb_order_item_1_6_0`();

DROP PROCEDURE `#__redshopb_order_item_1_6_0`;

ALTER TABLE `#__redshopb_order_item`
  CHANGE COLUMN `wardrobe_id` `collection_id` INT(10) UNSIGNED NULL DEFAULT NULL,
  CHANGE COLUMN `wardrobe_name` `collection_name` VARCHAR(255) NULL DEFAULT NULL,
  CHANGE COLUMN `wardrobe_erp_id` `collection_erp_id` VARCHAR(255) NULL DEFAULT NULL,
  ADD INDEX `#__rs_orderitem_fk6` (`product_item_id` ASC),
  ADD INDEX `#__rs_orderitem_fk5` (`product_id` ASC),
  ADD INDEX `#__rs_orderitem_fk4` (`currency_id` ASC),
  ADD INDEX `#__rs_orderitem_fk1` (`order_id` ASC),
  ADD INDEX `#__rs_orderitem_fk2` (`parent_id` ASC),
  ADD INDEX `#__rs_orderitem_fk3` (`collection_id` ASC),
  ADD CONSTRAINT `#__rs_orderitem_fk1`
    FOREIGN KEY (`order_id`)
    REFERENCES `#__redshopb_order` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_orderitem_fk2`
    FOREIGN KEY (`parent_id`)
    REFERENCES `#__redshopb_order_item` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_orderitem_fk3`
    FOREIGN KEY (`collection_id`)
    REFERENCES `#__redshopb_collection` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  ADD CONSTRAINT `#__rs_orderitem_fk4`
    FOREIGN KEY (`currency_id`)
    REFERENCES `#__redshopb_currency` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  ADD CONSTRAINT `#__rs_orderitem_fk5`
    FOREIGN KEY (`product_id`)
    REFERENCES `#__redshopb_product` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  ADD CONSTRAINT `#__rs_orderitem_fk6`
    FOREIGN KEY (`product_item_id`)
    REFERENCES `#__redshopb_product_item` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION;


-- -----------------------------------------------------
-- Table `#__redshopb_order_item_attribute`
-- -----------------------------------------------------
CALL `#__redshopb_order_item_attribute_1_6_0`();

DROP PROCEDURE `#__redshopb_order_item_attribute_1_6_0`;

ALTER TABLE `#__redshopb_order_item_attribute`
  ADD COLUMN `text_value` TEXT NULL DEFAULT NULL,
  ADD INDEX `#__rs_order_ita_fk1` (`order_item_id` ASC),
  ADD CONSTRAINT `#__rs_order_ita_fk1`
    FOREIGN KEY (`order_item_id`)
    REFERENCES `#__redshopb_order_item` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE;


-- -----------------------------------------------------
-- Table `#__redshopb_product_wash_care_spec_xref`
-- -----------------------------------------------------
CALL `#__redshopb_product_wash_care_spec_xref_1_6_0`();

DROP PROCEDURE `#__redshopb_product_wash_care_spec_xref_1_6_0`;

ALTER TABLE `#__redshopb_product_wash_care_spec_xref`
  ADD INDEX `#__rs_prod_wnc_fk2` (`product_id` ASC),
  ADD INDEX `#__rs_prod_wnc_fk1` (`wash_care_spec_id` ASC),
  ADD CONSTRAINT `#__rs_prod_wnc_fk1`
    FOREIGN KEY (`wash_care_spec_id`)
    REFERENCES `#__redshopb_wash_care_spec` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_prod_wnc_fk2`
    FOREIGN KEY (`product_id`)
    REFERENCES `#__redshopb_product` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE;


-- -----------------------------------------------------
-- Table `#__redshopb_product_composition`
-- -----------------------------------------------------
CALL `#__redshopb_product_composition_1_6_0`();

DROP PROCEDURE `#__redshopb_product_composition_1_6_0`;

ALTER TABLE `#__redshopb_product_composition`
  ADD INDEX `#__rs_prod_comp_fk1` (`product_id` ASC),
  ADD INDEX `#__rs_prod_comp_fk2` (`flat_attribute_value_id` ASC),
  ADD CONSTRAINT `#__rs_prod_comp_fk1`
    FOREIGN KEY (`product_id`)
    REFERENCES `#__redshopb_product` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_prod_comp_fk2`
    FOREIGN KEY (`flat_attribute_value_id`)
    REFERENCES `#__redshopb_product_attribute_value` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE;


SET FOREIGN_KEY_CHECKS = 1;