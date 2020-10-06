SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;

-- -----------------------------------------------------
-- Table `#__redshopb_acl_section`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_acl_section` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_acl_section` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `#__redshopb_acl_access`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_acl_access` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_acl_access` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `section_id` INT(10) UNSIGNED NULL DEFAULT NULL,
  `name` VARCHAR(255) NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `description` VARCHAR(255) NOT NULL,
  `simple` TINYINT(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  INDEX `#__rs_acl_access_fk1` (`section_id` ASC),
  CONSTRAINT `#__rs_acl_access_fk1`
    FOREIGN KEY (`section_id`)
    REFERENCES `#__redshopb_acl_section` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `#__redshopb_role_type`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_role_type` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_role_type` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `company_role` TINYINT(1) NOT NULL DEFAULT '0',
  `allow_access` TINYINT(1) NOT NULL DEFAULT '0',
  `type` VARCHAR(255) NULL DEFAULT NULL,
  `limited` TINYINT(1) NOT NULL DEFAULT '0',
  `hidden` TINYINT(1) NOT NULL DEFAULT '0',
  `allowed_rules` MEDIUMTEXT NULL DEFAULT NULL,
  `allowed_rules_main_company` MEDIUMTEXT NULL DEFAULT NULL,
  `allowed_rules_customers` MEDIUMTEXT NULL DEFAULT NULL,
  `allowed_rules_company` MEDIUMTEXT NULL DEFAULT NULL,
  `allowed_rules_own_company` MEDIUMTEXT NULL DEFAULT NULL,
  `allowed_rules_department` MEDIUMTEXT NULL DEFAULT NULL,
  `checked_out` INT(11) NULL DEFAULT NULL,
  `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` INT(11) NULL DEFAULT NULL,
  `created_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` INT(11) NULL DEFAULT NULL,
  `modified_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  INDEX `idx_role_type` (`type` ASC),
  INDEX `#__rs_roletype_fk1` (`checked_out` ASC),
  INDEX `#__rs_roletype_fk2` (`created_by` ASC),
  INDEX `#__rs_roletype_fk3` (`modified_by` ASC),
  CONSTRAINT `#__rs_roletype_fk1`
    FOREIGN KEY (`checked_out`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_roletype_fk2`
    FOREIGN KEY (`created_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_roletype_fk3`
    FOREIGN KEY (`modified_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `#__redshopb_country`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_country` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_country` (
  `id` SMALLINT(5) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `alpha2` CHAR(2) NOT NULL,
  `alpha3` CHAR(3) NOT NULL,
  `numeric` SMALLINT(3) UNSIGNED NOT NULL,
  `eu_zone` SMALLINT(4) NOT NULL DEFAULT '0',
  `company_id` INT(10) UNSIGNED NULL DEFAULT NULL,
  `checked_out` INT(11) NULL DEFAULT NULL,
  `checked_out_time` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `idx_name` (`name` ASC, `company_id` ASC),
  UNIQUE INDEX `idx_alpha2` (`alpha2` ASC, `company_id` ASC),
  UNIQUE INDEX `idx_alpha3` (`alpha3` ASC, `company_id` ASC),
  UNIQUE INDEX `idx_numeric` (`numeric` ASC, `company_id` ASC),
  INDEX `#__rs_country_fk1` (`company_id` ASC),
  INDEX `#__rs_country_fk2` (`checked_out` ASC),
  CONSTRAINT `#__redshopb_country_ibfk_1`
    FOREIGN KEY (`company_id`)
    REFERENCES `#__redshopb_company` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `#__redshopb_country_ibfk_2`
    FOREIGN KEY (`checked_out`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `#__redshopb_state`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_state` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_state` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `country_id` SMALLINT(5) UNSIGNED NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `alpha2` VARCHAR(2) NOT NULL,
  `alpha3` VARCHAR(3) NOT NULL,
  `company_id` INT(10) UNSIGNED NULL DEFAULT NULL,
  `checked_out` INT(11) NULL DEFAULT NULL,
  `checked_out_time` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `#__rs_state_fk1` (`country_id` ASC),
  INDEX `#__rs_state_fk2` (`checked_out` ASC),
  INDEX `#__rs_state_fk3` (`company_id` ASC),
  UNIQUE INDEX `#__rs_state_fk4` (`alpha2` ASC, `country_id` ASC, `company_id` ASC),
  UNIQUE INDEX `#__rs_state_fk5` (`alpha3` ASC, `country_id` ASC, `company_id` ASC),
  CONSTRAINT `#__redshopb_state_ibfk_2`
    FOREIGN KEY (`checked_out`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__redshopb_state_ibfk_1`
    FOREIGN KEY (`country_id`)
    REFERENCES `#__redshopb_country` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `#__redshopb_state_ibfk_3`
    FOREIGN KEY (`company_id`)
    REFERENCES `#__redshopb_company` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `#__redshopb_address`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_address` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_address` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `customer_type` ENUM('employee', 'department', 'company', 'stockroom', '') NOT NULL DEFAULT '' COMMENT 'Can be employee, department, company, stockroom or empty',
  `customer_id` INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `type` TINYINT(4) NOT NULL DEFAULT '2' COMMENT '1 - shipping address : not default, 2 - customer address, 3 - default shipping address',
  `order` TINYINT(4) NOT NULL DEFAULT '0',
  `name` VARCHAR(255) NOT NULL DEFAULT '',
  `name2` VARCHAR(255) NULL DEFAULT '',
  `address` MEDIUMTEXT NOT NULL,
  `address2` MEDIUMTEXT NOT NULL,
  `zip` VARCHAR(255) NOT NULL,
  `city` VARCHAR(255) NOT NULL,
  `country_id` SMALLINT(5) UNSIGNED NULL DEFAULT NULL,
  `state_id` INT(10) UNSIGNED NULL DEFAULT NULL,
  `code` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(255) NULL DEFAULT NULL,
  `checked_out` INT(11) NULL DEFAULT NULL,
  `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` INT(11) NULL DEFAULT NULL,
  `created_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` INT(11) NULL DEFAULT NULL,
  `modified_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  INDEX `#__rs_address_fk1` (`country_id` ASC),
  INDEX `idx_customer_type` (`customer_type` ASC),
  INDEX `idx_customer_id` (`customer_id` ASC),
  INDEX `idx_type` (`type` ASC),
  INDEX `#__rs_address_fk2` (`checked_out` ASC),
  INDEX `#__rs_address_fk3` (`created_by` ASC),
  INDEX `#__rs_address_fk4` (`modified_by` ASC),
  INDEX `#__rs_address_fk5` (`state_id` ASC),
  CONSTRAINT `#__rs_address_fk1`
    FOREIGN KEY (`country_id`)
    REFERENCES `#__redshopb_country` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_address_fk2`
    FOREIGN KEY (`checked_out`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_address_fk3`
    FOREIGN KEY (`created_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_address_fk4`
    FOREIGN KEY (`modified_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__redshopb_address_ibfk_1`
    FOREIGN KEY (`state_id`)
    REFERENCES `#__redshopb_state` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `#__redshopb_layout`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_layout` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_layout` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL DEFAULT '',
  `company_id` INT(10) UNSIGNED NULL DEFAULT NULL,
  `style` VARCHAR(255) NOT NULL DEFAULT '',
  `home` INT(11) NOT NULL DEFAULT '0',
  `params` TEXT NOT NULL,
  `checked_out` INT(11) NULL DEFAULT NULL,
  `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` INT(11) NULL DEFAULT NULL,
  `created_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` INT(11) NULL DEFAULT NULL,
  `modified_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  INDEX `#__rs_layout_fk1` (`company_id` ASC),
  INDEX `#__rs_layout_fk3` (`created_by` ASC),
  INDEX `#__rs_layout_fk4` (`modified_by` ASC),
  INDEX `#__rs_layout_fk2` (`checked_out` ASC),
  CONSTRAINT `#__rs_layout_fk1`
    FOREIGN KEY (`company_id`)
    REFERENCES `#__redshopb_company` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_layout_fk3`
    FOREIGN KEY (`created_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_layout_fk4`
    FOREIGN KEY (`modified_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_layout_fk2`
    FOREIGN KEY (`checked_out`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `#__redshopb_currency`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_currency` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_currency` (
  `id` SMALLINT(5) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `alpha3` CHAR(3) NOT NULL,
  `numeric` SMALLINT(3) UNSIGNED NOT NULL,
  `symbol` VARCHAR(255) NOT NULL,
  `symbol_position` TINYINT(1) NOT NULL DEFAULT '1' COMMENT 'display currency symbol before (0) or after (1) price',
  `decimals` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'number of decimals to show in prices',
  `state` TINYINT(1) NOT NULL DEFAULT '1' COMMENT 'disabled(0) / enabled(1)',
  `blank_space` TINYINT(1) NOT NULL DEFAULT '1' COMMENT 'display a blank space between the currency symbol and the price',
  `decimal_separator` VARCHAR(1) NOT NULL DEFAULT ',',
  `thousands_separator` VARCHAR(1) NOT NULL DEFAULT '.',
  `checked_out` INT(11) NULL DEFAULT NULL,
  `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` INT(11) NULL DEFAULT NULL,
  `created_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` INT(11) NULL DEFAULT NULL,
  `modified_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  UNIQUE INDEX `idx_alpha3` (`alpha3` ASC),
  UNIQUE INDEX `idx_numeric` (`numeric` ASC),
  INDEX `idx_name` (`name` ASC),
  INDEX `#__rs_currency_fk1` (`checked_out` ASC),
  INDEX `#__rs_currency_fk2` (`created_by` ASC),
  INDEX `#__rs_currency_fk3` (`modified_by` ASC),
  CONSTRAINT `#__rs_currency_fk1`
    FOREIGN KEY (`checked_out`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_currency_fk2`
    FOREIGN KEY (`created_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_currency_fk3`
    FOREIGN KEY (`modified_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `#__redshopb_tax_group`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_tax_group` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_tax_group` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `state` TINYINT(4) NOT NULL,
  `company_id` INT(10) UNSIGNED NULL DEFAULT NULL,
  `checked_out` INT(11) NULL DEFAULT NULL,
  `checked_out_time` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `#__rs_taxgr_fk1` (`company_id` ASC),
  INDEX `#__rs_taxgr_fk2` (`checked_out` ASC),
  CONSTRAINT `#__redshopb_tax_group_ibfk_2`
    FOREIGN KEY (`checked_out`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__redshopb_tax_group_ibfk_1`
    FOREIGN KEY (`company_id`)
    REFERENCES `#__redshopb_company` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `#__redshopb_company`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_company` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_company` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `name2` VARCHAR(255) NOT NULL,
  `customer_number` VARCHAR(255) NOT NULL DEFAULT '',
  `type` ENUM('','main','customer','end_customer') NOT NULL DEFAULT '',
  `address_id` INT(10) UNSIGNED NULL DEFAULT NULL,
  `asset_id` INT(10) UNSIGNED NULL DEFAULT NULL COMMENT 'FK to the #__assets table.',
  `parent_id` INT(10) UNSIGNED NULL DEFAULT NULL,
  `lft` INT(11) NOT NULL DEFAULT '0',
  `rgt` INT(11) NOT NULL DEFAULT '0',
  `level` INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `path` VARCHAR(255) NOT NULL DEFAULT '',
  `alias` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_bin' NOT NULL DEFAULT '',
  `image` VARCHAR(255) NULL,
  `state` TINYINT(4) NOT NULL DEFAULT '1',
  `requisition` VARCHAR(50) NULL DEFAULT NULL,
  `employee_mandatory` TINYINT(1) NULL DEFAULT '0',
  `layout_id` INT(10) UNSIGNED NULL DEFAULT NULL COMMENT 'Default layout to load when this company uses the system',
  `show_stock_as` ENUM('actual_stock','color_codes','hide','not_set') NOT NULL DEFAULT 'not_set',
  `contact_info` TEXT NOT NULL,
  `currency_id` SMALLINT(5) UNSIGNED NULL DEFAULT NULL,
  `order_approval` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Can be (0) Manual (1) Automatic',
  `use_wallets` TINYINT(1) NOT NULL DEFAULT '1' COMMENT 'Can be (0) No wallets for company users (1) Wallets enabled for company users',
  `hide_company` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'If set to 1 this will result in all company informations will be hidden from the user.',
  `phone` VARCHAR(255) NOT NULL,
  `invoice_email` VARCHAR(255) NULL DEFAULT NULL,
  `site_language` VARCHAR(7) NOT NULL,
  `calculate_fee` TINYINT(1) NOT NULL DEFAULT '0',
  `freight_amount_limit` DECIMAL(10,2) NULL DEFAULT 0 COMMENT 'Below this purchase limit, freight will be added',
  `freight_amount` DECIMAL(10,2) NULL DEFAULT 0 COMMENT 'Freight amount to add if the purchase limit is not reached',
  `freight_product_id` INT(10) UNSIGNED NULL DEFAULT NULL COMMENT 'Freight product to be added',
  `send_mail_on_order` TINYINT(1) NOT NULL DEFAULT '1' COMMENT 'If enabled, this company admins will receive emails on order placing',
  `show_retail_price` TINYINT(1) NOT NULL DEFAULT '-1',
  `b2c` TINYINT(4) NOT NULL DEFAULT '0' COMMENT 'B2C company for end customers',
  `use_collections` TINYINT(4) NOT NULL DEFAULT '0' COMMENT 'Defines if collections are enabled for this company, otherwise category shopping is displayed',
  `show_price` TINYINT(4) NOT NULL DEFAULT '-1',
  `stockroom_verification` TINYINT(1) NOT NULL DEFAULT '1',
  `tax_group_id` INT(10) UNSIGNED NULL DEFAULT NULL,
  `tax_based_on` VARCHAR(100) NOT NULL,
  `calculate_vat_on` VARCHAR(100) NOT NULL,
  `tax_exempt` INT(4) NOT NULL DEFAULT '0',
  `customer_tax_exempt` TINYINT(4) NOT NULL DEFAULT '0',
  `vat_number` VARCHAR(255) NOT NULL,
  `checked_out` INT(11) NULL,
  `checked_out_time` DATETIME NULL,
  `created_by` INT(11) NULL DEFAULT NULL,
  `created_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` INT(11) NULL DEFAULT NULL,
  `modified_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted` TINYINT(1) NOT NULL DEFAULT '0',
  `wallet_product_id` INT(10) UNSIGNED NULL DEFAULT NULL COMMENT 'Wallet product to be added',
  `url` VARCHAR(255) NULL COMMENT 'Url config for B2C',
  PRIMARY KEY (`id`),
  INDEX `idx_name` (`name` ASC),
  INDEX `idx_state` (`state` ASC),
  INDEX `idx_path` (`path` ASC),
  INDEX `idx_left_right` (`lft` ASC, `rgt` ASC),
  UNIQUE INDEX `idx_alias` (`parent_id` ASC, `alias` ASC),
  INDEX `idx_customer_number` (`customer_number` ASC),
  INDEX `idx_type` (`type` ASC),
  INDEX `#__rs_company_fk1` (`address_id` ASC),
  INDEX `#__rs_company_fk2` (`asset_id` ASC),
  INDEX `#__rs_company_fk3` (`parent_id` ASC),
  INDEX `#__rs_company_fk5` (`created_by` ASC),
  INDEX `#__rs_company_fk6` (`modified_by` ASC),
  INDEX `#__rs_company_fk7` (`layout_id` ASC),
  INDEX `#__rs_company_fk8` (`currency_id` ASC),
  INDEX `#__rs_company_fk4` (`checked_out` ASC),
  INDEX `#__rs_company_fk9` (`tax_group_id` ASC),
  CONSTRAINT `#__rs_company_fk1`
    FOREIGN KEY (`address_id`)
    REFERENCES `#__redshopb_address` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_company_fk2`
    FOREIGN KEY (`asset_id`)
    REFERENCES `#__assets` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_company_fk3`
    FOREIGN KEY (`parent_id`)
    REFERENCES `#__redshopb_company` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_company_fk5`
    FOREIGN KEY (`created_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_company_fk6`
    FOREIGN KEY (`modified_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_company_fk7`
    FOREIGN KEY (`layout_id`)
    REFERENCES `#__redshopb_layout` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_company_fk8`
    FOREIGN KEY (`currency_id`)
    REFERENCES `#__redshopb_currency` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_company_fk4`
    FOREIGN KEY (`checked_out`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__redshopb_company_ibfk_1`
    FOREIGN KEY (`tax_group_id`)
    REFERENCES `#__redshopb_tax_group` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `#__redshopb_role`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_role` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_role` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `role_type_id` INT(10) UNSIGNED NOT NULL,
  `company_id` INT(10) UNSIGNED NULL DEFAULT NULL,
  `joomla_group_id` INT(10) UNSIGNED NULL COMMENT 'fk to a joomla group id',
  `checked_out` INT(11) NULL DEFAULT NULL,
  `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` INT(11) NULL DEFAULT NULL,
  `created_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` INT(11) NULL DEFAULT NULL,
  `modified_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  UNIQUE INDEX `role_type_id` (`role_type_id` ASC, `company_id` ASC),
  INDEX `#__rs_role_fk2` (`company_id` ASC),
  INDEX `#__rs_role_fk3` (`joomla_group_id` ASC),
  INDEX `#__rs_role_fk4` (`checked_out` ASC),
  INDEX `#__rs_role_fk5` (`created_by` ASC),
  INDEX `#__rs_role_fk6` (`modified_by` ASC),
  INDEX `#__rs_role_fk1` (`role_type_id` ASC),
  CONSTRAINT `#__rs_role_fk1`
    FOREIGN KEY (`role_type_id`)
    REFERENCES `#__redshopb_role_type` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_role_fk2`
    FOREIGN KEY (`company_id`)
    REFERENCES `#__redshopb_company` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_role_fk3`
    FOREIGN KEY (`joomla_group_id`)
    REFERENCES `#__usergroups` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_role_fk4`
    FOREIGN KEY (`checked_out`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_role_fk5`
    FOREIGN KEY (`created_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_role_fk6`
    FOREIGN KEY (`modified_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `#__redshopb_acl_rule`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_acl_rule` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_acl_rule` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `access_id` INT(10) UNSIGNED NOT NULL,
  `role_id` INT(10) UNSIGNED NOT NULL,
  `joomla_asset_id` INT(10) UNSIGNED NOT NULL COMMENT 'fk to a joomla asset id',
  `granted` TINYINT(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  INDEX `idx_redshopb_acl_rule_effective` (`access_id` ASC, `role_id` ASC, `joomla_asset_id` ASC),
  INDEX `#__rs_acl_rule_fk2` (`role_id` ASC),
  INDEX `#__rs_acl_rule_fk3` (`joomla_asset_id` ASC),
  INDEX `#__rs_acl_rule_fk1` (`access_id` ASC),
  CONSTRAINT `#__rs_acl_rule_fk1`
    FOREIGN KEY (`access_id`)
    REFERENCES `#__redshopb_acl_access` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_acl_rule_fk2`
    FOREIGN KEY (`role_id`)
    REFERENCES `#__redshopb_role` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_acl_rule_fk3`
    FOREIGN KEY (`joomla_asset_id`)
    REFERENCES `#__assets` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `#__redshopb_acl_simple_access_xref`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_acl_simple_access_xref` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_acl_simple_access_xref` (
  `simple_access_id` INT(10) UNSIGNED NOT NULL,
  `access_id` INT(10) UNSIGNED NOT NULL,
  `role_type_id` INT(10) UNSIGNED NOT NULL,
  `scope` ENUM('global','company','department') NOT NULL DEFAULT 'global',
  INDEX `#__rs_acl_sax_fk1` (`simple_access_id` ASC),
  INDEX `#__rs_acl_sax_fk2` (`access_id` ASC),
  INDEX `#__rs_acl_sax_fk3` (`role_type_id` ASC),
  CONSTRAINT `#__rs_acl_sax_fk1`
    FOREIGN KEY (`simple_access_id`)
    REFERENCES `#__redshopb_acl_access` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_acl_sax_fk2`
    FOREIGN KEY (`access_id`)
    REFERENCES `#__redshopb_acl_access` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_acl_sax_fk3`
    FOREIGN KEY (`role_type_id`)
    REFERENCES `#__redshopb_role_type` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `#__redshopb_template`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_template` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_template` (
  `id` INT(10) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `template_group` ENUM('email', 'shop', 'email_tag', 'shop_tag') NOT NULL DEFAULT 'shop',
  `scope` VARCHAR(255) NULL,
  `alias` VARCHAR(255) NOT NULL,
  `content` LONGTEXT NULL,
  `state` TINYINT(4) NOT NULL DEFAULT '1',
  `default` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Indicates if this item is the default template.',
  `editable` TINYINT(3) UNSIGNED NOT NULL DEFAULT '1' COMMENT 'Indicates if this template can be edited or not',
  `params` TEXT NOT NULL,
  `description` VARCHAR(255) NOT NULL DEFAULT '',
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
  UNIQUE INDEX `idx_alias` (`alias` ASC, `scope` ASC),
  INDEX `idx_common` (`scope` ASC, `default` ASC),
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
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `#__redshopb_filter_fieldset`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_filter_fieldset` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_filter_fieldset` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL DEFAULT '',
  `state` TINYINT(4) NOT NULL DEFAULT '1',
  `created_by` INT(11) NULL DEFAULT NULL,
  `created_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` INT(11) NULL DEFAULT NULL,
  `modified_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  INDEX `idx_name` (`name` ASC))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `#__redshopb_category`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_category` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_category` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `alias` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_bin' NOT NULL DEFAULT '',
  `company_id` INT(10) UNSIGNED NULL DEFAULT NULL,
  `parent_id` INT(10) UNSIGNED NULL DEFAULT NULL,
  `lft` INT(11) NOT NULL DEFAULT '0',
  `rgt` INT(11) NOT NULL DEFAULT '0',
  `level` INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `path` VARCHAR(255) NOT NULL DEFAULT '',
  `image` VARCHAR(255) NOT NULL DEFAULT '',
  `description` TEXT NOT NULL,
  `filter_fieldset_id` INT(11) UNSIGNED NULL DEFAULT NULL,
  `state` TINYINT(4) NOT NULL DEFAULT '1',
  `hide` TINYINT(4) NOT NULL DEFAULT '0' COMMENT 'Hide or show in menus & shop',
  `template_id` INT(10) NULL,
  `product_list_template_id` INT(10) NULL,
  `product_grid_template_id` INT(10) NULL,
  `checked_out` INT(11) NULL DEFAULT NULL,
  `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` INT(11) NULL DEFAULT NULL,
  `created_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` INT(11) NULL DEFAULT NULL,
  `modified_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `params` VARCHAR(2048) NULL COMMENT 'JSON formatted',
  PRIMARY KEY (`id`),
  INDEX `idx_state` (`state` ASC),
  INDEX `#__rs_categ_fk1` (`checked_out` ASC),
  INDEX `idx_path` (`path` ASC),
  INDEX `idx_left_right` (`lft` ASC, `rgt` ASC),
  UNIQUE INDEX `idx_alias` (`parent_id` ASC, `alias` ASC),
  INDEX `#__rs_categ_fk2` (`created_by` ASC),
  INDEX `#__rs_categ_fk3` (`modified_by` ASC),
  INDEX `#__rs_categ_fk4` (`company_id` ASC),
  INDEX `#__rs_categ_fk5` (`parent_id` ASC),
  INDEX `#__rs_categ_fk6` (`template_id` ASC),
  INDEX `#__rs_categ_fk7` (`filter_fieldset_id` ASC),
  INDEX `#__rs_categ_fk8` (`product_list_template_id` ASC),
  INDEX `#__rs_categ_fk9` (`product_grid_template_id` ASC),
  CONSTRAINT `#__rs_categ_fk1`
    FOREIGN KEY (`checked_out`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_categ_fk2`
    FOREIGN KEY (`created_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_categ_fk3`
    FOREIGN KEY (`modified_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_categ_fk4`
    FOREIGN KEY (`company_id`)
    REFERENCES `#__redshopb_company` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_categ_fk5`
    FOREIGN KEY (`parent_id`)
    REFERENCES `#__redshopb_category` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_categ_fk6`
    FOREIGN KEY (`template_id`)
    REFERENCES `#__redshopb_template` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_categ_fk7`
    FOREIGN KEY (`filter_fieldset_id`)
    REFERENCES `#__redshopb_filter_fieldset` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_categ_fk8`
    FOREIGN KEY (`product_list_template_id`)
    REFERENCES `#__redshopb_template` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_categ_fk9`
    FOREIGN KEY (`product_grid_template_id`)
    REFERENCES `#__redshopb_template` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `#__redshopb_department`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_department` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_department` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `name2` VARCHAR(255) NOT NULL,
  `company_id` INT(10) UNSIGNED NOT NULL,
  `department_number` VARCHAR(255) NOT NULL,
  `main_department` TINYINT(3) UNSIGNED NOT NULL,
  `asset_id` INT(10) UNSIGNED NULL DEFAULT NULL COMMENT 'FK to the #__assets table.',
  `parent_id` INT(10) UNSIGNED NULL DEFAULT NULL,
  `lft` INT(10) NOT NULL,
  `rgt` INT(10) NOT NULL,
  `level` INT(10) UNSIGNED NOT NULL,
  `path` VARCHAR(255) NOT NULL,
  `alias` VARCHAR(255) NOT NULL,
  `image` VARCHAR(255) NULL,
  `address_id` INT(10) UNSIGNED NULL DEFAULT NULL,
  `state` TINYINT(4) NOT NULL DEFAULT '1',
  `requisition` VARCHAR(50) NULL DEFAULT NULL,
  `checked_out` INT(11) NULL DEFAULT NULL,
  `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` INT(11) NULL DEFAULT NULL,
  `created_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` INT(11) NULL DEFAULT NULL,
  `modified_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted` TINYINT(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  INDEX `idx_name` (`name` ASC),
  INDEX `idx_left_right` (`lft` ASC, `rgt` ASC),
  INDEX `idx_path` (`path` ASC),
  INDEX `idx_main_department` (`main_department` ASC),
  INDEX `#__rs_dept_fk1` (`address_id` ASC),
  INDEX `#__rs_dept_fk2` (`asset_id` ASC),
  INDEX `#__rs_dept_fk3` (`company_id` ASC),
  INDEX `#__rs_dept_fk4` (`checked_out` ASC),
  INDEX `#__rs_dept_fk5` (`created_by` ASC),
  INDEX `#__rs_dept_fk6` (`modified_by` ASC),
  INDEX `#__rs_dept_fk7` (`parent_id` ASC),
  UNIQUE INDEX `idx_alias` (`parent_id` ASC, `alias` ASC),
  CONSTRAINT `#__rs_dept_fk1`
    FOREIGN KEY (`address_id`)
    REFERENCES `#__redshopb_address` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_dept_fk2`
    FOREIGN KEY (`asset_id`)
    REFERENCES `#__assets` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_dept_fk3`
    FOREIGN KEY (`company_id`)
    REFERENCES `#__redshopb_company` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_dept_fk4`
    FOREIGN KEY (`checked_out`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_dept_fk5`
    FOREIGN KEY (`created_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_dept_fk6`
    FOREIGN KEY (`modified_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_dept_fk7`
    FOREIGN KEY (`parent_id`)
    REFERENCES `#__redshopb_department` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `#__redshopb_wallet`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_wallet` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_wallet` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `start_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `end_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `checked_out` INT(11) NULL DEFAULT NULL,
  `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` INT(11) NULL DEFAULT NULL,
  `created_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` INT(11) NULL DEFAULT NULL,
  `modified_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  INDEX `#__rs_wallet_fk1` (`checked_out` ASC),
  INDEX `#__rs_wallet_fk2` (`created_by` ASC),
  INDEX `#__rs_wallet_fk3` (`modified_by` ASC),
  CONSTRAINT `#__rs_wallet_fk1`
    FOREIGN KEY (`checked_out`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_wallet_fk2`
    FOREIGN KEY (`created_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_wallet_fk3`
    FOREIGN KEY (`modified_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `#__redshopb_user`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_user` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_user` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name1` VARCHAR(255) NOT NULL,
  `name2` VARCHAR(255) NOT NULL,
  `printed_name` VARCHAR(255) NULL DEFAULT NULL,
  `joomla_user_id` INT(11) NOT NULL,
  `department_id` INT(10) UNSIGNED NULL DEFAULT NULL,
  `address_id` INT(10) UNSIGNED NULL DEFAULT NULL,
  `wallet_id` INT(10) UNSIGNED NULL DEFAULT NULL,
  `use_company_email` TINYINT(4) NOT NULL DEFAULT '0' COMMENT 'If enabled, it will use a random email using the company alias',
  `send_email` TINYINT(4) NOT NULL DEFAULT '1' COMMENT 'Allows disabling B2B-related emails to this user',
  `phone` VARCHAR(255) NULL DEFAULT NULL,
  `cell_phone` VARCHAR(255) NOT NULL,
  `employee_number` VARCHAR(255) NOT NULL,
  `checked_out` INT(11) NULL DEFAULT NULL,
  `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `image` VARCHAR(255) NULL,
  `created_by` INT(11) NULL DEFAULT NULL,
  `created_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` INT(11) NULL DEFAULT NULL,
  `modified_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  INDEX `#__rs_user_fk1` (`joomla_user_id` ASC),
  INDEX `#__rs_user_fk3` (`department_id` ASC),
  INDEX `#__rs_user_fk4` (`address_id` ASC),
  INDEX `#__rs_user_fk5` (`checked_out` ASC),
  INDEX `#__rs_user_fk6` (`created_by` ASC),
  INDEX `#__rs_user_fk7` (`modified_by` ASC),
  INDEX `#__rs_user_fk8` (`wallet_id` ASC),
  CONSTRAINT `#__rs_user_fk1`
    FOREIGN KEY (`joomla_user_id`)
    REFERENCES `#__users` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_user_fk3`
    FOREIGN KEY (`department_id`)
    REFERENCES `#__redshopb_department` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_user_fk4`
    FOREIGN KEY (`address_id`)
    REFERENCES `#__redshopb_address` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_user_fk5`
    FOREIGN KEY (`checked_out`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_user_fk6`
    FOREIGN KEY (`created_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_user_fk7`
    FOREIGN KEY (`modified_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_user_fk8`
    FOREIGN KEY (`wallet_id`)
    REFERENCES `#__redshopb_wallet` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `#__redshopb_company_sales_person_xref`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_company_sales_person_xref` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_company_sales_person_xref` (
  `user_id` INT(10) UNSIGNED NOT NULL,
  `company_id` INT(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`user_id`, `company_id`),
  INDEX `#__rs_companysp_fk1` (`company_id` ASC),
  INDEX `#__rs_companysp_fk2` (`user_id` ASC),
  CONSTRAINT `#__rs_companysp_fk1`
    FOREIGN KEY (`company_id`)
    REFERENCES `#__redshopb_company` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_companysp_fk2`
    FOREIGN KEY (`user_id`)
    REFERENCES `#__redshopb_user` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `#__redshopb_config`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_config` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_config` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(200) NOT NULL,
  `value` TEXT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `#__rs_config_fk1` (`name` ASC))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `#__redshopb_cron`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_cron` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_cron` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `plugin` VARCHAR(255) NULL,
  `parent_id` INT(11) NULL DEFAULT '0',
  `state` TINYINT(4) NOT NULL DEFAULT '0',
  `mute_from` TIME NOT NULL DEFAULT '00:00:00',
  `mute_to` TIME NOT NULL DEFAULT '00:00:00',
  `start_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `finish_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `next_start` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `lft` INT(11) NOT NULL,
  `rgt` INT(11) NOT NULL,
  `level` INT(10) UNSIGNED NOT NULL,
  `alias` VARCHAR(255) NOT NULL,
  `path` VARCHAR(255) NOT NULL,
  `parent_alias` VARCHAR(255) NOT NULL DEFAULT 'root',
  `execute_sync` TINYINT(4) NOT NULL,
  `mask_time` VARCHAR(255) NOT NULL DEFAULT 'Y-m-d 00:00:00',
  `offset_time` VARCHAR(255) NOT NULL DEFAULT '+1 day',
  `is_continuous` TINYINT(4) NOT NULL DEFAULT '1',
  `items_process_step` INT(11) NOT NULL DEFAULT '0',
  `items_processed` INT(11) NOT NULL DEFAULT '0',
  `items_total` INT(11) NOT NULL DEFAULT '0',
  `last_status_messages` LONGTEXT NOT NULL,
  `params` TEXT NOT NULL,
  `checked_out` INT(11) NOT NULL DEFAULT '0',
  `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  INDEX `idx_common` (`next_start` ASC, `state` ASC),
  INDEX `idx_parent_id` (`parent_id` ASC),
  INDEX `idx_left_right` (`lft` ASC, `rgt` ASC),
  INDEX `idx_name` (`name` ASC),
  UNIQUE INDEX `idx_alias` (`alias` ASC))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `#__redshopb_customer_discount_group`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_customer_discount_group` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_customer_discount_group` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `code` VARCHAR(255) NOT NULL,
  `company_id` INT(10) UNSIGNED NULL DEFAULT NULL,
  `state` TINYINT(4) NOT NULL DEFAULT '1',
  `checked_out` INT(11) NULL DEFAULT NULL,
  `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` INT(11) NULL DEFAULT NULL,
  `created_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` INT(11) NULL DEFAULT NULL,
  `modified_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  UNIQUE INDEX `idx_code` (`code` ASC),
  INDEX `idx_name` (`name` ASC),
  INDEX `#__rs_custdg_fk1` (`company_id` ASC),
  INDEX `#__rs_custdg_fk3` (`created_by` ASC),
  INDEX `#__rs_custdg_fk2` (`checked_out` ASC),
  INDEX `#__rs_custdg_fk4` (`modified_by` ASC),
  CONSTRAINT `#__rs_custdg_fk1`
    FOREIGN KEY (`company_id`)
    REFERENCES `#__redshopb_company` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_custdg_fk3`
    FOREIGN KEY (`created_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_custdg_fk2`
    FOREIGN KEY (`checked_out`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_custdg_fk4`
    FOREIGN KEY (`modified_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `#__redshopb_customer_discount_group_xref`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_customer_discount_group_xref` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_customer_discount_group_xref` (
  `customer_id` INT(10) UNSIGNED NOT NULL,
  `discount_group_id` INT(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`customer_id`, `discount_group_id`),
  INDEX `#__rs_custdgx_fk1` (`discount_group_id` ASC),
  INDEX `#__rs_custdgx_fk2` (`customer_id` ASC),
  CONSTRAINT `#__rs_custdgx_fk1`
    FOREIGN KEY (`discount_group_id`)
    REFERENCES `#__redshopb_customer_discount_group` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_custdgx_fk2`
    FOREIGN KEY (`customer_id`)
    REFERENCES `#__redshopb_company` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `#__redshopb_customer_price_group`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_customer_price_group` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_customer_price_group` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `code` VARCHAR(255) NOT NULL,
  `company_id` INT(10) UNSIGNED NULL DEFAULT NULL,
  `show_stock_as` ENUM('actual_stock', 'color_codes', 'hide', 'not_set') NOT NULL DEFAULT 'not_set',
  `default` TINYINT(1) NOT NULL DEFAULT 0,
  `state` TINYINT(4) NOT NULL DEFAULT '1',
  `checked_out` INT(11) NULL DEFAULT NULL,
  `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` INT(11) NULL DEFAULT NULL,
  `created_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` INT(11) NULL DEFAULT NULL,
  `modified_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  UNIQUE INDEX `idx_code` (`code` ASC),
  INDEX `idx_name` (`name` ASC),
  INDEX `#__rs_custpg_fk1` (`company_id` ASC),
  INDEX `#__rs_custpg_fk4` (`modified_by` ASC),
  INDEX `#__rs_custpg_fk3` (`created_by` ASC),
  INDEX `#__rs_custpg_fk2` (`checked_out` ASC),
  CONSTRAINT `#__rs_custpg_fk1`
    FOREIGN KEY (`company_id`)
    REFERENCES `#__redshopb_company` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_custpg_fk2`
    FOREIGN KEY (`checked_out`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_custpg_fk3`
    FOREIGN KEY (`created_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_custpg_fk4`
    FOREIGN KEY (`modified_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `#__redshopb_customer_price_group_xref`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_customer_price_group_xref` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_customer_price_group_xref` (
  `customer_id` INT(10) UNSIGNED NOT NULL,
  `price_group_id` INT(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`customer_id`, `price_group_id`),
  INDEX `#__rs_custpgx_pk1` (`price_group_id` ASC),
  INDEX `#__rs_custpgx_pk2` (`customer_id` ASC),
  CONSTRAINT `#__rs_custpgx_pk1`
    FOREIGN KEY (`price_group_id`)
    REFERENCES `#__redshopb_customer_price_group` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_custpgx_pk2`
    FOREIGN KEY (`customer_id`)
    REFERENCES `#__redshopb_company` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `#__redshopb_unit_measure`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_unit_measure` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_unit_measure` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `alias` VARCHAR(255) NOT NULL,
  `description` VARCHAR(255) NULL,
  `decimal_position` TINYINT(4) UNSIGNED NOT NULL DEFAULT '0',
  `decimal_separator` VARCHAR(1) NULL,
  `thousand_separator` VARCHAR(1) NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `idx_alias` (`alias` ASC))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `#__redshopb_manufacturer`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_manufacturer` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_manufacturer` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `parent_id` INT(10) UNSIGNED NULL,
  `lft` INT(11) NOT NULL DEFAULT 0,
  `rgt` INT(11) NOT NULL DEFAULT 0,
  `level` INT(10) UNSIGNED NOT NULL DEFAULT 0,
  `path` VARCHAR(255) NOT NULL DEFAULT '',
  `alias` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_bin' NOT NULL DEFAULT '',
  `description` TEXT NULL DEFAULT NULL,
  `category` VARCHAR(255) NULL DEFAULT NULL,
  `image` VARCHAR(255) NULL,
  `state` TINYINT(4) NOT NULL DEFAULT '1',
  `featured` TINYINT(4) NOT NULL DEFAULT '0',
  `checked_out` INT(11) NULL,
  `checked_out_time` DATETIME NULL,
  `created_by` INT(11) NULL,
  `created_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` INT(11) NULL,
  `modified_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `params` VARCHAR(2048) NULL COMMENT 'JSON formatted',
  PRIMARY KEY (`id`),
  INDEX `idx_name` (`name` ASC),
  INDEX `idx_state` (`state` ASC),
  INDEX `idx_path` (`path` ASC),
  INDEX `idx_left_right` (`lft` ASC, `rgt` ASC),
  UNIQUE INDEX `idx_alias` (`parent_id` ASC, `alias` ASC),
  INDEX `#__rs_manufacturer_fk2` (`created_by` ASC),
  INDEX `#__rs_manufacturer_fk3` (`modified_by` ASC),
  INDEX `#__rs_manufacturer_fk4` (`checked_out` ASC),
  INDEX `#__rs_manufacturer_fk1` (`parent_id` ASC),
  CONSTRAINT `#__rs_manufacturer_fk1`
    FOREIGN KEY (`parent_id`)
    REFERENCES `#__redshopb_manufacturer` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_manufacturer_fk2`
    FOREIGN KEY (`created_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_manufacturer_fk3`
    FOREIGN KEY (`modified_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_manufacturer_fk4`
    FOREIGN KEY (`checked_out`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `#__redshopb_calc_type`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_calc_type` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_calc_type` (
  `id` INT(10) NOT NULL,
  `name` TEXT NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `#__redshopb_product`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_product` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_product` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `alias` VARCHAR(255) NULL,
  `sku` VARCHAR(255) NOT NULL,
  `manufacturer_sku` VARCHAR(255) NULL,
  `related_sku` VARCHAR(255) NULL,
  `company_id` INT(10) UNSIGNED NULL DEFAULT NULL,
  `tax_group_id` INT(10) UNSIGNED NULL DEFAULT NULL,
  `category_id` INT(10) UNSIGNED NULL COMMENT 'Main category of the product',
  `state` TINYINT(4) NOT NULL DEFAULT '1',
  `discontinued` TINYINT(4) NOT NULL DEFAULT '0',
  `date_new` DATE NOT NULL DEFAULT '0000-00-00',
  `service` TINYINT(4) NOT NULL DEFAULT '0' COMMENT '1 - product use as service, 0 - normal product',
  `featured` TINYINT(4) NOT NULL DEFAULT '0',
  `unit_measure_id` INT NULL,
  `stock_upper_level` DOUBLE UNSIGNED NULL DEFAULT NULL COMMENT 'Above this limit, the stock is considered enough, below it, it presents a first level warning',
  `stock_lower_level` DOUBLE UNSIGNED NULL DEFAULT NULL COMMENT 'Below this level, it presents an alarm',
  `hits` INT(11) NULL,
  `template_id` INT(10) NULL,
  `print_template_id` INT(10) NULL DEFAULT NULL,
  `filter_fieldset_id` INT(11) UNSIGNED NULL DEFAULT NULL,
  `manufacturer_id` INT(10) UNSIGNED NULL DEFAULT NULL,
  `decimal_position` TINYINT(4) UNSIGNED NULL DEFAULT NULL,
  `min_sale` DOUBLE UNSIGNED NOT NULL DEFAULT 1,
  `max_sale` DOUBLE UNSIGNED NULL DEFAULT NULL,
  `pkg_size` DOUBLE UNSIGNED NOT NULL DEFAULT 1,
  `campaign` TINYINT(4) NOT NULL DEFAULT 0,
  `weight` FLOAT NOT NULL COMMENT 'kg',
  `volume` FLOAT NOT NULL COMMENT 'm3',
  `calc_type` INT(10) NULL,
  `publish_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `unpublish_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `checked_out` INT(11) NULL DEFAULT NULL,
  `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` INT(11) NULL DEFAULT NULL,
  `created_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` INT(11) NULL DEFAULT NULL,
  `modified_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `params` VARCHAR(2048) NULL COMMENT 'JSON formatted',
  PRIMARY KEY (`id`),
  INDEX `idx_name` (`name` ASC),
  INDEX `idx_sku` (`sku` ASC, `manufacturer_sku` ASC, `related_sku` ASC),
  INDEX `idx_state` (`state` ASC),
  INDEX `idx_discontinued` (`discontinued` ASC),
  INDEX `idx_service` (`service` ASC),
  INDEX `#__rs_prod_fk1` (`checked_out` ASC),
  INDEX `#__rs_prod_fk2` (`created_by` ASC),
  INDEX `#__rs_prod_fk3` (`modified_by` ASC),
  INDEX `#__rs_prod_fk4` (`company_id` ASC),
  INDEX `#__rs_prod_fk5` (`template_id` ASC),
  INDEX `#__rs_prod_fk6` (`unit_measure_id` ASC),
  UNIQUE INDEX `idx_alias` (`company_id` ASC, `category_id` ASC, `alias` ASC),
  INDEX `#__rs_prod_fk7` (`category_id` ASC),
  INDEX `#__rs_prod_fk8` (`filter_fieldset_id` ASC),
  INDEX `#__rs_prod_fk9` (`manufacturer_id` ASC),
  INDEX `idx_product_state` (`state` ASC, `discontinued` ASC, `service` ASC),
  INDEX `idx_related_sku` (`related_sku` ASC),
  INDEX `#__rs_prod_fk10` (`tax_group_id` ASC),
  INDEX `#__rs_prod_fk11` (`print_template_id` ASC),
  INDEX `#__rs_prod_fk12` (`calc_type` ASC),
  CONSTRAINT `#__rs_prod_fk1`
    FOREIGN KEY (`checked_out`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_prod_fk2`
    FOREIGN KEY (`created_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_prod_fk3`
    FOREIGN KEY (`modified_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_prod_fk4`
    FOREIGN KEY (`company_id`)
    REFERENCES `#__redshopb_company` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_prod_fk5`
    FOREIGN KEY (`template_id`)
    REFERENCES `#__redshopb_template` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_prod_fk6`
    FOREIGN KEY (`unit_measure_id`)
    REFERENCES `#__redshopb_unit_measure` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_prod_fk7`
    FOREIGN KEY (`category_id`)
    REFERENCES `#__redshopb_category` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_prod_fk8`
    FOREIGN KEY (`filter_fieldset_id`)
    REFERENCES `#__redshopb_filter_fieldset` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_prod_fk9`
    FOREIGN KEY (`manufacturer_id`)
    REFERENCES `#__redshopb_manufacturer` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_prod_fk10`
    FOREIGN KEY (`tax_group_id`)
    REFERENCES `#__redshopb_tax_group` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_prod_fk11`
    FOREIGN KEY (`print_template_id`)
    REFERENCES `#__redshopb_template` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_prod_fk12`
    FOREIGN KEY (`calc_type`)
    REFERENCES `#__redshopb_calc_type` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `#__redshopb_fee`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_fee` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_fee` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `currency_id` SMALLINT(5) UNSIGNED NULL DEFAULT NULL,
  `fee_limit` DECIMAL(10,2) NOT NULL,
  `fee_amount` DECIMAL(10,2) NOT NULL,
  `product_id` INT(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `#__rs_fee_fk1` (`currency_id` ASC),
  INDEX `#__rs_fee_fk2` (`product_id` ASC),
  CONSTRAINT `#__rs_fee_fk1`
    FOREIGN KEY (`currency_id`)
    REFERENCES `#__redshopb_currency` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_fee_fk2`
    FOREIGN KEY (`product_id`)
    REFERENCES `#__redshopb_product` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `#__redshopb_tag`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_tag` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_tag` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `alias` VARCHAR(255) NOT NULL,
  `type` VARCHAR(255) NULL,
  `company_id` INT(10) UNSIGNED NULL DEFAULT NULL,
  `parent_id` INT(10) UNSIGNED NULL,
  `lft` INT(11) NOT NULL DEFAULT 0,
  `rgt` INT(11) NOT NULL DEFAULT 0,
  `level` INT(11) NOT NULL DEFAULT 0,
  `path` VARCHAR(255) NOT NULL,
  `image` VARCHAR(255) NULL,
  `state` TINYINT(4) NOT NULL DEFAULT '1',
  `checked_out` INT(11) NULL DEFAULT NULL,
  `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` INT(11) NULL DEFAULT NULL,
  `created_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` INT(11) NULL DEFAULT NULL,
  `modified_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  INDEX `idx_state` (`state` ASC),
  INDEX `#__rs_tag_fk2` (`checked_out` ASC),
  INDEX `#__rs_tag_fk1` (`company_id` ASC),
  INDEX `#__rs_tag_fk3` (`created_by` ASC),
  INDEX `#__rs_tag_fk4` (`modified_by` ASC),
  INDEX `#__rs_tag_fk5` (`parent_id` ASC),
  UNIQUE INDEX `idx_alias` (`parent_id` ASC, `alias` ASC),
  CONSTRAINT `#__rs_tag_fk1`
    FOREIGN KEY (`company_id`)
    REFERENCES `#__redshopb_company` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_tag_fk2`
    FOREIGN KEY (`checked_out`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_tag_fk3`
    FOREIGN KEY (`created_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_tag_fk4`
    FOREIGN KEY (`modified_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_tag_fk5`
    FOREIGN KEY (`parent_id`)
    REFERENCES `#__redshopb_tag` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `#__redshopb_logos`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_logos` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_logos` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `type` VARCHAR(255) NOT NULL,
  `image` VARCHAR(255) NOT NULL,
  `brand_id` INT(10) UNSIGNED NULL COMMENT 'References a certain brand created as a tag',
  PRIMARY KEY (`id`),
  INDEX `#__rs_logos_fk1` (`brand_id` ASC),
  CONSTRAINT `#__rs_logos_fk1`
    FOREIGN KEY (`brand_id`)
    REFERENCES `#__redshopb_tag` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `#__redshopb_type`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_type` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_type` (
  `id` INT(11) NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `alias` VARCHAR(255) NOT NULL,
  `value_type` ENUM('string_value', 'float_value', 'int_value', 'text_value', 'field_value') NULL DEFAULT 'string_value' COMMENT 'Value field to use in the destination value table',
  `field_name` VARCHAR(50) NULL COMMENT 'PHP form field class',
  `multiple` TINYINT(4) NOT NULL DEFAULT 0,
  `params` VARCHAR(2048) NULL COMMENT 'JSON formatted',
  PRIMARY KEY (`id`),
  UNIQUE INDEX `idx_alias` (`alias` ASC))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `#__redshopb_product_attribute`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_product_attribute` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_product_attribute` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `alias` VARCHAR(255) NOT NULL,
  `product_id` INT(10) UNSIGNED NOT NULL,
  `type_id` INT(11) NOT NULL,
  `ordering` INT(11) NOT NULL DEFAULT '0',
  `main_attribute` TINYINT(4) NOT NULL DEFAULT '0',
  `enable_sku_value_display` TINYINT(4) NOT NULL DEFAULT '0',
  `state` TINYINT(4) NOT NULL DEFAULT '1',
  `conversion_sets` TINYINT(4) NOT NULL DEFAULT '0',
  `image` VARCHAR(255) NULL,
  `checked_out` INT(11) NULL DEFAULT NULL,
  `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` INT(11) NULL DEFAULT NULL,
  `created_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` INT(11) NULL DEFAULT NULL,
  `modified_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  INDEX `idx_state` (`state` ASC),
  INDEX `idx_ordering` (`ordering` ASC),
  INDEX `#__rs_prod_at_fk1` (`product_id` ASC),
  INDEX `#__rs_prod_at_fk2` (`checked_out` ASC),
  INDEX `#__rs_prod_at_fk3` (`created_by` ASC),
  INDEX `#__rs_prod_at_fk4` (`modified_by` ASC),
  INDEX `#__rs_prod_at_fk5` (`type_id` ASC),
  UNIQUE INDEX `idx_alias` (`product_id` ASC, `alias` ASC),
  CONSTRAINT `#__rs_prod_at_fk1`
    FOREIGN KEY (`product_id`)
    REFERENCES `#__redshopb_product` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_prod_at_fk2`
    FOREIGN KEY (`checked_out`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_prod_at_fk3`
    FOREIGN KEY (`created_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_prod_at_fk4`
    FOREIGN KEY (`modified_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_prod_at_fk5`
    FOREIGN KEY (`type_id`)
    REFERENCES `#__redshopb_type` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `#__redshopb_product_attribute_value`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_product_attribute_value` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_product_attribute_value` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_attribute_id` INT(10) UNSIGNED NOT NULL,
  `sku` VARCHAR(255) NOT NULL,
  `ordering` INT(11) NOT NULL DEFAULT '0',
  `state` TINYINT(4) NOT NULL DEFAULT '1',
  `selected` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '0: no, 1: yes',
  `string_value` VARCHAR(2048) NULL DEFAULT NULL,
  `float_value` FLOAT NULL DEFAULT NULL,
  `int_value` INT(11) NULL DEFAULT NULL,
  `text_value` TEXT NULL,
  `image` VARCHAR(255) NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_sku` (`sku` ASC),
  INDEX `idx_state` (`state` ASC),
  INDEX `idx_ordering` (`ordering` ASC),
  INDEX `#__rs_prod_av_fk1` (`product_attribute_id` ASC),
  CONSTRAINT `#__rs_prod_av_fk1`
    FOREIGN KEY (`product_attribute_id`)
    REFERENCES `#__redshopb_product_attribute` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `#__redshopb_media`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_media` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_media` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `remote_path` VARCHAR(255) NOT NULL DEFAULT '',
  `alt` VARCHAR(255) NOT NULL,
  `view` TINYINT(4) NOT NULL COMMENT '1 = Front, 2 = Back, 0 = Other',
  `product_id` INT(10) UNSIGNED NOT NULL,
  `ordering` INT(11) NOT NULL DEFAULT '0',
  `attribute_value_id` INT(10) UNSIGNED NULL,
  `state` TINYINT(4) NOT NULL DEFAULT '1',
  `checked_out` INT(11) NULL DEFAULT NULL,
  `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` INT(11) NULL DEFAULT NULL,
  `created_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` INT(11) NULL DEFAULT NULL,
  `modified_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  INDEX `#__rs_media_fk1` (`product_id` ASC),
  INDEX `idx_common` (`product_id` ASC, `state` ASC),
  INDEX `#__rs_media_fk2` (`checked_out` ASC),
  INDEX `#__rs_media_fk4` (`modified_by` ASC),
  INDEX `#__rs_media_fk5` (`attribute_value_id` ASC),
  INDEX `#__rs_media_fk3` (`created_by` ASC),
  INDEX `idx_ordering` (`product_id` ASC, `ordering` ASC),
  CONSTRAINT `#__rs_media_fk1`
    FOREIGN KEY (`product_id`)
    REFERENCES `#__redshopb_product` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_media_fk2`
    FOREIGN KEY (`checked_out`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_media_fk3`
    FOREIGN KEY (`created_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_media_fk4`
    FOREIGN KEY (`modified_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_media_fk5`
    FOREIGN KEY (`attribute_value_id`)
    REFERENCES `#__redshopb_product_attribute_value` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `#__redshopb_order`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_order` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_order` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `delivery_address_id` INT(10) UNSIGNED NULL DEFAULT NULL,
  `delivery_address_code` VARCHAR(255) NOT NULL,
  `delivery_address_type` ENUM('company','department','employee','') NOT NULL,
  `delivery_address_name` VARCHAR(255) NOT NULL DEFAULT '',
  `delivery_address_name2` VARCHAR(255) NULL DEFAULT '',
  `delivery_address_address` MEDIUMTEXT NOT NULL,
  `delivery_address_address2` MEDIUMTEXT NOT NULL,
  `delivery_address_zip` VARCHAR(255) NOT NULL,
  `delivery_address_city` VARCHAR(255) NOT NULL,
  `delivery_address_country` VARCHAR(255) NOT NULL,
  `delivery_address_country_code` CHAR(2) NOT NULL DEFAULT '',
  `delivery_address_state` VARCHAR(255) NOT NULL,
  `delivery_address_state_code` CHAR(2) NOT NULL DEFAULT '',
  `currency` CHAR(3) NOT NULL DEFAULT '',
  `currency_id` SMALLINT(5) UNSIGNED NOT NULL,
  `discount_type` ENUM('total', 'percent') NOT NULL DEFAULT 'percent',
  `discount` DECIMAL(10,2) NOT NULL DEFAULT '0.00',
  `offer_id` INT(10) UNSIGNED NULL DEFAULT NULL,
  `user_company_id` INT(10) UNSIGNED NULL DEFAULT NULL,
  `customer_type` VARCHAR(255) NOT NULL DEFAULT '',
  `customer_department` INT(10) UNSIGNED NULL DEFAULT NULL,
  `customer_company` INT(10) UNSIGNED NULL DEFAULT NULL,
  `customer_id` INT(10) UNSIGNED NOT NULL,
  `customer_name` VARCHAR(255) NOT NULL DEFAULT '',
  `customer_name2` VARCHAR(255) NOT NULL DEFAULT '',
  `customer_email` VARCHAR(255) NOT NULL DEFAULT '',
  `customer_phone` VARCHAR(50) NOT NULL DEFAULT '',
  `company_erp_id` VARCHAR(255) NOT NULL DEFAULT '',
  `department_erp_id` VARCHAR(255) NOT NULL DEFAULT '',
  `user_erp_id` VARCHAR(255) NOT NULL DEFAULT '',
  `status` TINYINT(4) NOT NULL DEFAULT '0' COMMENT '0: pending, 1:confirmed, 2:cancelled, 3:refunded, 4:shipped, 5:ready for delivery, 6:sent to upper level',
  `payment_name` VARCHAR(50) NOT NULL DEFAULT '',
  `payment_title` VARCHAR(255) NOT NULL DEFAULT '',
  `payment_status` VARCHAR(50) NULL DEFAULT '',
  `shipping_rate_id` INT(11) UNSIGNED NULL DEFAULT NULL,
  `shipping_details` MEDIUMTEXT NOT NULL,
  `shipping_price` DECIMAL(10,2) UNSIGNED NOT NULL,
  `comment` VARCHAR(250) NULL DEFAULT '',
  `requisition` VARCHAR(250) NULL DEFAULT '',
  `shipping_date` DATE NULL DEFAULT NULL,
  `total_price` DECIMAL(10,2) UNSIGNED NOT NULL,
  `total_price_paid` DECIMAL(10,2) UNSIGNED NOT NULL,
  `sales_header_id` INT(10) UNSIGNED NULL DEFAULT NULL,
  `sales_header_type` VARCHAR(50) NULL DEFAULT NULL,
  `ip_address` VARCHAR(15) NOT NULL DEFAULT '',
  `token` VARCHAR(32) NULL DEFAULT NULL,
  `params` TEXT NULL DEFAULT NULL,
  `checked_out` INT(11) NULL DEFAULT NULL,
  `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` INT(11) NULL DEFAULT NULL,
  `created_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` INT(11) NULL DEFAULT NULL,
  `modified_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  INDEX `#__rs_order_fk7` (`delivery_address_id` ASC),
  INDEX `#__rs_order_fk6` (`currency_id` ASC),
  INDEX `idx_status` (`status` ASC),
  INDEX `#__rs_order_fk1` (`checked_out` ASC),
  INDEX `#__rs_order_fk2` (`created_by` ASC),
  INDEX `#__rs_order_fk3` (`modified_by` ASC),
  INDEX `#__rs_order_fk4` (`customer_company` ASC),
  INDEX `#__rs_order_fk5` (`customer_department` ASC),
  INDEX `idx_currency` (`currency` ASC),
  INDEX `#__rs_order_fk9` (`offer_id` ASC),
  INDEX `#__rs_order_fk8` (`shipping_rate_id` ASC),
  INDEX `#__rs_order_fk10` (`user_company_id` ASC),
  CONSTRAINT `#__rs_order_fk1`
    FOREIGN KEY (`checked_out`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_order_fk2`
    FOREIGN KEY (`created_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_order_fk3`
    FOREIGN KEY (`modified_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_order_fk4`
    FOREIGN KEY (`customer_company`)
    REFERENCES `#__redshopb_company` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `#__rs_order_fk5`
    FOREIGN KEY (`customer_department`)
    REFERENCES `#__redshopb_department` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `#__rs_order_fk10`
    FOREIGN KEY (`user_company_id`)
    REFERENCES `#__redshopb_company` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `#__redshopb_order_item`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_order_item` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_order_item` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` INT(10) UNSIGNED NOT NULL,
  `product_id` INT(10) UNSIGNED NULL DEFAULT NULL,
  `product_name` VARCHAR(255) NOT NULL DEFAULT '',
  `product_sku` VARCHAR(255) NOT NULL DEFAULT '',
  `product_item_id` INT(10) UNSIGNED NULL DEFAULT NULL,
  `product_item_sku` VARCHAR(255) NOT NULL DEFAULT '',
  `currency` CHAR(3) NOT NULL DEFAULT '',
  `currency_id` SMALLINT(5) UNSIGNED NOT NULL,
  `discount_type` ENUM('total', 'percent') NOT NULL DEFAULT 'percent',
  `discount` DECIMAL(10,2) NOT NULL DEFAULT '0.00',
  `price_without_discount` DECIMAL(10,2) UNSIGNED NOT NULL DEFAULT '0.00',
  `price` DECIMAL(10,2) UNSIGNED NOT NULL,
  `quantity` DOUBLE UNSIGNED NOT NULL,
  `parent_id` INT(10) UNSIGNED NULL DEFAULT NULL COMMENT 'If product is an accessory for some other product, this field will contain its description.',
  `collection_id` INT(10) UNSIGNED NULL DEFAULT NULL,
  `collection_name` VARCHAR(255) NULL DEFAULT NULL,
  `collection_erp_id` VARCHAR(255) NULL DEFAULT NULL,
  `stockroom_id` INT(5) UNSIGNED NOT NULL,
  `stockroom_name` VARCHAR(255) NOT NULL,
  `offer_id` INT(10) UNSIGNED NULL DEFAULT NULL,
  `params` TEXT NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `#__rs_orderitem_fk6` (`product_item_id` ASC),
  INDEX `#__rs_orderitem_fk5` (`product_id` ASC),
  INDEX `idx_currency` (`currency` ASC),
  INDEX `#__rs_orderitem_fk4` (`currency_id` ASC),
  INDEX `#__rs_orderitem_fk1` (`order_id` ASC),
  INDEX `#__rs_orderitem_fk2` (`parent_id` ASC),
  INDEX `#__rs_orderitem_fk3` (`collection_id` ASC),
  INDEX `#__rs_orderitem_fk7` (`stockroom_id` ASC),
  CONSTRAINT `#__rs_orderitem_fk1`
    FOREIGN KEY (`order_id`)
    REFERENCES `#__redshopb_order` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_orderitem_fk2`
    FOREIGN KEY (`parent_id`)
    REFERENCES `#__redshopb_order_item` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `#__redshopb_order_item_attribute`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_order_item_attribute` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_order_item_attribute` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_item_id` INT(10) UNSIGNED NOT NULL,
  `ordering` INT(11) NOT NULL DEFAULT '0',
  `name` VARCHAR(255) NOT NULL DEFAULT '',
  `sku` VARCHAR(255) NOT NULL,
  `string_value` VARCHAR(255) NULL DEFAULT NULL,
  `float_value` FLOAT NULL DEFAULT NULL,
  `int_value` INT(10) UNSIGNED NULL DEFAULT NULL,
  `text_value` TEXT NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `#__rs_order_ita_fk1` (`order_item_id` ASC),
  CONSTRAINT `#__rs_order_ita_fk1`
    FOREIGN KEY (`order_item_id`)
    REFERENCES `#__redshopb_order_item` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `#__redshopb_order_logs`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_order_logs` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_order_logs` (
  `new_order_id` INT(10) UNSIGNED NOT NULL,
  `order_id` INT(10) UNSIGNED NOT NULL,
  `log_type` VARCHAR(255) NOT NULL DEFAULT 'collect' COMMENT 'Values: \"collect\" - order_id is collection of new_order_id, \"expedite\" - order_id is expedited',
  `date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`new_order_id`, `order_id`),
  INDEX `#__rs_orderlogs_fk2` (`order_id` ASC),
  INDEX `#__rs_orderlogs_fk1` (`new_order_id` ASC),
  CONSTRAINT `#__rs_orderlogs_fk1`
    FOREIGN KEY (`new_order_id`)
    REFERENCES `#__redshopb_order` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_orderlogs_fk2`
    FOREIGN KEY (`order_id`)
    REFERENCES `#__redshopb_order` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `#__redshopb_product_category_xref`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_product_category_xref` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_product_category_xref` (
  `product_id` INT(10) UNSIGNED NOT NULL,
  `category_id` INT(10) UNSIGNED NOT NULL,
  `ordering` INT(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`product_id`, `category_id`),
  INDEX `#__rs_prod_cat_fk2` (`category_id` ASC),
  INDEX `#__rs_prod_cat_fk1` (`product_id` ASC),
  CONSTRAINT `#__rs_prod_cat_fk1`
    FOREIGN KEY (`product_id`)
    REFERENCES `#__redshopb_product` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_prod_cat_fk2`
    FOREIGN KEY (`category_id`)
    REFERENCES `#__redshopb_category` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `#__redshopb_product_composition`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_product_composition` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_product_composition` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` INT(10) UNSIGNED NOT NULL,
  `flat_attribute_value_id` INT(10) UNSIGNED NULL DEFAULT NULL,
  `type` VARCHAR(255) NOT NULL,
  `quality` TEXT NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `#__rs_prod_comp_fk1` (`product_id` ASC),
  INDEX `#__rs_prod_comp_fk2` (`flat_attribute_value_id` ASC),
  CONSTRAINT `#__rs_prod_comp_fk1`
    FOREIGN KEY (`product_id`)
    REFERENCES `#__redshopb_product` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_prod_comp_fk2`
    FOREIGN KEY (`flat_attribute_value_id`)
    REFERENCES `#__redshopb_product_attribute_value` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `#__redshopb_product_descriptions`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_product_descriptions` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_product_descriptions` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` INT(10) UNSIGNED NOT NULL,
  `main_attribute_value_id` INT(10) UNSIGNED NULL DEFAULT NULL,
  `description_intro` TEXT NULL,
  `description` LONGTEXT NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `#__rs_prod_des_fk1` (`product_id` ASC),
  INDEX `#__rs_prod_des_fk2` (`main_attribute_value_id` ASC),
  CONSTRAINT `#__rs_prod_des_fk1`
    FOREIGN KEY (`product_id`)
    REFERENCES `#__redshopb_product` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_prod_des_fk2`
    FOREIGN KEY (`main_attribute_value_id`)
    REFERENCES `#__redshopb_product_attribute_value` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `#__redshopb_product_discount`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_product_discount` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_product_discount` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `type` ENUM('product', 'product_item', 'product_discount_group') NOT NULL DEFAULT 'product',
  `type_id` INT(10) UNSIGNED NOT NULL,
  `sales_type` ENUM('all_debtor', 'debtor_discount_group', 'debtor') NOT NULL DEFAULT 'all_debtor',
  `sales_id` INT(10) UNSIGNED NULL DEFAULT NULL,
  `starting_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ending_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `kind` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Kind of discount. 0 for percent. 1 for amount.',
  `percent` DECIMAL(10,2) NOT NULL DEFAULT '0.00',
  `total` DECIMAL(10,2) NOT NULL DEFAULT '0.00',
  `currency_id` SMALLINT(5) UNSIGNED NULL DEFAULT NULL,
  `quantity_min` DOUBLE UNSIGNED NULL DEFAULT NULL,
  `quantity_max` DOUBLE UNSIGNED NULL DEFAULT NULL,
  `state` TINYINT(4) NOT NULL DEFAULT '1',
  `checked_out` INT(11) NULL DEFAULT NULL,
  `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` INT(11) NULL DEFAULT NULL,
  `created_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` INT(11) NULL DEFAULT NULL,
  `modified_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  INDEX `idx_common` (`type` ASC, `type_id` ASC, `sales_type` ASC, `sales_id` ASC, `starting_date` ASC, `ending_date` ASC, `kind` ASC, `percent` ASC, `total` ASC, `state` ASC),
  INDEX `#__rs_pdisc_fk1` (`currency_id` ASC),
  INDEX `#__rs_pdisc_fk2` (`checked_out` ASC),
  INDEX `#__rs_pdisc_fk3` (`created_by` ASC),
  INDEX `#__rs_pdisc_fk4` (`modified_by` ASC),
  CONSTRAINT `#__rs_pdisc_fk1`
    FOREIGN KEY (`currency_id`)
    REFERENCES `#__redshopb_currency` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_pdisc_fk2`
    FOREIGN KEY (`checked_out`)
    REFERENCES `#__users` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `#__rs_pdisc_fk3`
    FOREIGN KEY (`created_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `#__rs_pdisc_fk4`
    FOREIGN KEY (`modified_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `#__redshopb_product_discount_group`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_product_discount_group` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_product_discount_group` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `code` VARCHAR(255) NOT NULL,
  `company_id` INT(10) UNSIGNED NULL DEFAULT NULL,
  `state` TINYINT(4) NOT NULL DEFAULT '1',
  `checked_out` INT(11) NULL DEFAULT NULL,
  `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` INT(11) NULL DEFAULT NULL,
  `created_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` INT(11) NULL DEFAULT NULL,
  `modified_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  UNIQUE INDEX `idx_code` (`code` ASC),
  INDEX `idx_name` (`name` ASC),
  INDEX `#__rs_proddg_fk1` (`company_id` ASC),
  INDEX `#__rs_proddg_fk3` (`created_by` ASC),
  INDEX `#__rs_proddg_fk2` (`checked_out` ASC),
  INDEX `#__rs_proddg_fk4` (`modified_by` ASC),
  CONSTRAINT `#__rs_proddg_fk1`
    FOREIGN KEY (`company_id`)
    REFERENCES `#__redshopb_company` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_proddg_fk3`
    FOREIGN KEY (`created_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_proddg_fk2`
    FOREIGN KEY (`checked_out`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_proddg_fk4`
    FOREIGN KEY (`modified_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `#__redshopb_product_discount_group_xref`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_product_discount_group_xref` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_product_discount_group_xref` (
  `product_id` INT(10) UNSIGNED NOT NULL,
  `discount_group_id` INT(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`product_id`, `discount_group_id`),
  INDEX `#__rs_prod_dgx_fk1` (`discount_group_id` ASC),
  INDEX `#__rs_prod_dgx_fk2` (`product_id` ASC),
  CONSTRAINT `#__rs_prod_dgx_fk1`
    FOREIGN KEY (`discount_group_id`)
    REFERENCES `#__redshopb_product_discount_group` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_prod_dgx_fk2`
    FOREIGN KEY (`product_id`)
    REFERENCES `#__redshopb_product` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `#__redshopb_product_item`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_product_item` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_product_item` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` INT(10) UNSIGNED NOT NULL,
  `sku` VARCHAR(255) NOT NULL,
  `state` TINYINT(4) NOT NULL DEFAULT '1',
  `discontinued` TINYINT(4) NOT NULL DEFAULT '0',
  `stock_upper_level` DOUBLE UNSIGNED NULL DEFAULT NULL COMMENT 'Above this limit, the stock is considered enough, below it, it presents a first level warning',
  `stock_lower_level` DOUBLE UNSIGNED NULL DEFAULT NULL COMMENT 'Below this level, it presents an alarm',
  PRIMARY KEY (`id`),
  INDEX `idx_state` (`state` ASC),
  INDEX `idx_discontinued` (`discontinued` ASC),
  INDEX `#__rs_prod_item_fk1` (`product_id` ASC),
  CONSTRAINT `#__rs_prod_item_fk1`
    FOREIGN KEY (`product_id`)
    REFERENCES `#__redshopb_product` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `#__redshopb_collection`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_collection` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_collection` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `alias` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_bin' NOT NULL DEFAULT '',
  `state` TINYINT(4) NOT NULL DEFAULT '1',
  `company_id` INT(10) UNSIGNED NOT NULL,
  `currency_id` SMALLINT(5) UNSIGNED NULL DEFAULT NULL,
  `checked_out` INT(11) NULL DEFAULT NULL,
  `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` INT(11) NULL DEFAULT NULL,
  `created_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` INT(11) NULL DEFAULT NULL,
  `modified_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  INDEX `idx_state` (`state` ASC),
  INDEX `#__rs_collection_fk1` (`company_id` ASC),
  INDEX `#__rs_collection_fk2` (`checked_out` ASC),
  INDEX `#__rs_collection_fk3` (`created_by` ASC),
  INDEX `#__rs_collection_fk4` (`modified_by` ASC),
  INDEX `#__rs_collection_fk5` (`currency_id` ASC),
  CONSTRAINT `#__rs_collection_fk1`
    FOREIGN KEY (`company_id`)
    REFERENCES `#__redshopb_company` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_collection_fk2`
    FOREIGN KEY (`checked_out`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_collection_fk3`
    FOREIGN KEY (`created_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_collection_fk4`
    FOREIGN KEY (`modified_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_collection_fk5`
    FOREIGN KEY (`currency_id`)
    REFERENCES `#__redshopb_currency` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `#__redshopb_product_item_accessory`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_product_item_accessory` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_product_item_accessory` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `attribute_value_id` INT(10) UNSIGNED NOT NULL,
  `accessory_product_id` INT(10) UNSIGNED NOT NULL,
  `description` VARCHAR(255) NOT NULL,
  `collection_id` INT(10) UNSIGNED NULL DEFAULT NULL,
  `hide_on_collection` TINYINT(4) NOT NULL DEFAULT '0',
  `price` DECIMAL(10,2) NULL COMMENT 'If price is not present, it uses regular price logic',
  `selection` ENUM('require','proposed','optional') NOT NULL,
  `state` TINYINT(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  INDEX `idx_common` (`attribute_value_id` ASC, `collection_id` ASC),
  INDEX `idx_selection` (`selection` ASC),
  INDEX `#__rs_prod_iac_fk1` (`accessory_product_id` ASC),
  INDEX `#__rs_prod_iac_fk3` (`collection_id` ASC),
  INDEX `#__rs_prod_iac_fk2` (`attribute_value_id` ASC),
  CONSTRAINT `#__rs_prod_iac_fk1`
    FOREIGN KEY (`accessory_product_id`)
    REFERENCES `#__redshopb_product` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_prod_iac_fk2`
    FOREIGN KEY (`attribute_value_id`)
    REFERENCES `#__redshopb_product_attribute_value` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_prod_iac_fk3`
    FOREIGN KEY (`collection_id`)
    REFERENCES `#__redshopb_collection` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `#__redshopb_product_item_attribute_value_xref`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_product_item_attribute_value_xref` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_product_item_attribute_value_xref` (
  `product_item_id` INT(10) UNSIGNED NOT NULL,
  `product_attribute_value_id` INT(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`product_item_id`, `product_attribute_value_id`),
  INDEX `#__rs_prod_iav_fk2` (`product_attribute_value_id` ASC),
  INDEX `#__rs_prod_iav_fk1` (`product_item_id` ASC),
  CONSTRAINT `#__rs_prod_iav_fk1`
    FOREIGN KEY (`product_item_id`)
    REFERENCES `#__redshopb_product_item` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_prod_iav_fk2`
    FOREIGN KEY (`product_attribute_value_id`)
    REFERENCES `#__redshopb_product_attribute_value` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `#__redshopb_product_price`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_product_price` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_product_price` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` INT(11) UNSIGNED NOT NULL,
  `product_item_id` INT(11) UNSIGNED NULL,
  `type_id` INT(10) UNSIGNED NOT NULL,
  `type` ENUM('product','product_item') NOT NULL DEFAULT 'product',
  `sales_type` ENUM('all_customers', 'customer_price_group', 'customer_price', 'campaign') NOT NULL DEFAULT 'all_customers',
  `sales_code` VARCHAR(255) NOT NULL,
  `currency_id` SMALLINT(5) UNSIGNED NULL DEFAULT NULL,
  `price` DECIMAL(10,2) NOT NULL,
  `retail_price` DECIMAL(10,2) NOT NULL,
  `starting_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ending_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `is_multiple` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  `quantity_min` DOUBLE UNSIGNED NULL DEFAULT NULL,
  `quantity_max` DOUBLE UNSIGNED NULL DEFAULT NULL,
  `allow_discount` TINYINT(4) NOT NULL DEFAULT '1',
  `country_id` SMALLINT(5) UNSIGNED NULL DEFAULT NULL,
  `checked_out` INT(11) NULL,
  `checked_out_time` DATETIME NULL,
  `created_by` INT(11) NULL,
  `created_date` DATETIME NULL,
  `modified_by` INT(11) NULL,
  `modified_date` DATETIME NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `idx_product_price` (`type_id` ASC, `type` ASC, `sales_type` ASC, `currency_id` ASC, `starting_date` ASC, `ending_date` ASC, `sales_code` ASC, `quantity_min` ASC, `quantity_max` ASC),
  INDEX `idx_price` (`price` ASC),
  INDEX `idx_date` (`starting_date` ASC, `ending_date` ASC),
  INDEX `idx_sales_type` (`sales_type` ASC),
  INDEX `idx_sales_code` (`sales_code` ASC),
  INDEX `idx_type_id` (`type_id` ASC),
  INDEX `idx_type` (`type` ASC),
  INDEX `#__rs_pprice_fk1` (`country_id` ASC),
  INDEX `#__rs_pprice_fk2` (`currency_id` ASC),
  INDEX `#__rs_pprice_fk3` (`checked_out` ASC),
  INDEX `#__rs_pprice_fk4` (`created_by` ASC),
  INDEX `#__rs_pprice_fk5` (`modified_by` ASC),
  CONSTRAINT `#__rs_pprice_fk1`
    FOREIGN KEY (`country_id`)
    REFERENCES `#__redshopb_country` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_pprice_fk2`
    FOREIGN KEY (`currency_id`)
    REFERENCES `#__redshopb_currency` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_pprice_fk3`
    FOREIGN KEY (`checked_out`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_pprice_fk4`
    FOREIGN KEY (`created_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_pprice_fk5`
    FOREIGN KEY (`modified_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `#__redshopb_product_tag_xref`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_product_tag_xref` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_product_tag_xref` (
  `product_id` INT(10) UNSIGNED NOT NULL,
  `tag_id` INT(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`product_id`, `tag_id`),
  INDEX `#__rs_prod_tag_fk1` (`tag_id` ASC),
  INDEX `#__rs_prod_tag_fk2` (`product_id` ASC),
  CONSTRAINT `#__rs_prod_tag_fk1`
    FOREIGN KEY (`tag_id`)
    REFERENCES `#__redshopb_tag` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_prod_tag_fk2`
    FOREIGN KEY (`product_id`)
    REFERENCES `#__redshopb_product` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `#__redshopb_wash_care_spec`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_wash_care_spec` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_wash_care_spec` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `type_code` VARCHAR(255) NOT NULL,
  `code` VARCHAR(255) NOT NULL,
  `image` VARCHAR(255) NOT NULL,
  `description` TEXT NOT NULL,
  `state` TINYINT(4) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `idx_common` (`type_code` ASC, `code` ASC),
  INDEX `idx_state` (`state` ASC))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `#__redshopb_product_wash_care_spec_xref`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_product_wash_care_spec_xref` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_product_wash_care_spec_xref` (
  `product_id` INT(10) UNSIGNED NOT NULL,
  `wash_care_spec_id` INT(10) UNSIGNED NOT NULL,
  `ordering` INT(10) NOT NULL,
  PRIMARY KEY (`product_id`, `wash_care_spec_id`),
  INDEX `#__rs_prod_wnc_fk2` (`product_id` ASC),
  INDEX `#__rs_prod_wnc_fk1` (`wash_care_spec_id` ASC),
  CONSTRAINT `#__rs_prod_wnc_fk1`
    FOREIGN KEY (`wash_care_spec_id`)
    REFERENCES `#__redshopb_wash_care_spec` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_prod_wnc_fk2`
    FOREIGN KEY (`product_id`)
    REFERENCES `#__redshopb_product` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `#__redshopb_stockroom`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_stockroom` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_stockroom` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `color` VARCHAR(7) NOT NULL DEFAULT '',
  `alias` VARCHAR(255) NOT NULL,
  `description` VARCHAR(2048) NULL,
  `company_id` INT(10) UNSIGNED NULL,
  `address_id` INT(10) UNSIGNED NULL,
  `min_delivery_time` INT(11) NULL,
  `max_delivery_time` INT(11) NULL,
  `stock_upper_level` DOUBLE UNSIGNED NULL DEFAULT NULL COMMENT 'Above this limit, the stock is considered enough, below it, it presents a first level warning',
  `stock_lower_level` DOUBLE UNSIGNED NULL DEFAULT NULL COMMENT 'Below this level, it presents an alarm',
  `ordering` INT NULL COMMENT 'Decides the order selection of stockrooms of the company',
  `state` TINYINT(4) NOT NULL DEFAULT '1',
  `pick_up` TINYINT(4) NOT NULL DEFAULT '0',
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
  UNIQUE INDEX `idx_alias` (`company_id` ASC, `alias` ASC),
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
-- Table `#__redshopb_sync`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_sync` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_sync` (
  `reference` VARCHAR(100) NOT NULL,
  `remote_key` VARCHAR(255) NOT NULL,
  `remote_parent_key` VARCHAR(100) NOT NULL,
  `local_id` VARCHAR(100) NOT NULL,
  `execute_sync` TINYINT(4) NOT NULL DEFAULT '0',
  `main_reference` TINYINT(1) NOT NULL DEFAULT '0',
  `deleted` TINYINT(1) NOT NULL DEFAULT '0',
  `serialize` TEXT NOT NULL,
  `metadata` TEXT NULL,
  `hash_key` VARCHAR(100) NOT NULL DEFAULT '',
  INDEX `idx_local_id` (`local_id` ASC),
  INDEX `idx_remote_parent_key` (`remote_parent_key` ASC),
  INDEX `idx_execute_sync` (`execute_sync` ASC, `reference` ASC),
  INDEX `idx_main_reference` (`main_reference` ASC),
  INDEX `idx_deleted` (`deleted` ASC),
  PRIMARY KEY (`reference`, `remote_key`, `remote_parent_key`),
  INDEX `idx_reference_local_id` (`reference` ASC, `local_id` ASC))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `#__redshopb_usergroup_sales_person_xref`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_usergroup_sales_person_xref` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_usergroup_sales_person_xref` (
  `user_id` INT(10) UNSIGNED NOT NULL,
  `joomla_group_id` INT(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`user_id`, `joomla_group_id`),
  INDEX `#__rs_usergroupsp_fk2` (`joomla_group_id` ASC),
  INDEX `#__rs_usergroupsp_fk1` (`user_id` ASC),
  CONSTRAINT `#__rs_usergroupsp_fk1`
    FOREIGN KEY (`user_id`)
    REFERENCES `#__redshopb_user` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_usergroupsp_fk2`
    FOREIGN KEY (`joomla_group_id`)
    REFERENCES `#__usergroups` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `#__redshopb_wallet_money`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_wallet_money` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_wallet_money` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `wallet_id` INT(10) UNSIGNED NOT NULL,
  `currency_id` SMALLINT(5) UNSIGNED NOT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `checked_out` INT(11) NULL DEFAULT NULL,
  `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` INT(11) NULL DEFAULT NULL,
  `created_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` INT(11) NULL DEFAULT NULL,
  `modified_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  UNIQUE INDEX `idx_wallet_id_currency_id` (`wallet_id` ASC, `currency_id` ASC),
  INDEX `#__rs_walletm_fk2` (`currency_id` ASC),
  INDEX `#__rs_walletm_fk3` (`checked_out` ASC),
  INDEX `#__rs_walletm_fk4` (`created_by` ASC),
  INDEX `#__rs_walletm_fk5` (`modified_by` ASC),
  INDEX `#__rs_walletm_fk1` (`wallet_id` ASC),
  CONSTRAINT `#__rs_walletm_fk1`
    FOREIGN KEY (`wallet_id`)
    REFERENCES `#__redshopb_wallet` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_walletm_fk2`
    FOREIGN KEY (`currency_id`)
    REFERENCES `#__redshopb_currency` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_walletm_fk3`
    FOREIGN KEY (`checked_out`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_walletm_fk4`
    FOREIGN KEY (`created_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_walletm_fk5`
    FOREIGN KEY (`modified_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `#__redshopb_collection_department_xref`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_collection_department_xref` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_collection_department_xref` (
  `collection_id` INT(10) UNSIGNED NOT NULL,
  `department_id` INT(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`collection_id`, `department_id`),
  INDEX `#__rs_colldept_fk2` (`department_id` ASC),
  INDEX `#__rs_colldept_fk1` (`collection_id` ASC),
  CONSTRAINT `#__rs_colldept_fk1`
    FOREIGN KEY (`collection_id`)
    REFERENCES `#__redshopb_collection` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_colldept_fk2`
    FOREIGN KEY (`department_id`)
    REFERENCES `#__redshopb_department` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `#__redshopb_collection_product_item_xref`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_collection_product_item_xref` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_collection_product_item_xref` (
  `collection_id` INT(10) UNSIGNED NOT NULL,
  `product_item_id` INT(10) UNSIGNED NOT NULL,
  `price` DECIMAL(10,2) NULL COMMENT 'If price is not present, it uses regular price logic',
  `state` TINYINT(4) NOT NULL,
  PRIMARY KEY (`collection_id`, `product_item_id`),
  INDEX `idx_state` (`state` ASC),
  INDEX `#__rs_collpix_fk2` (`product_item_id` ASC),
  INDEX `#__rs_collpix_fk1` (`collection_id` ASC),
  CONSTRAINT `#__rs_collpix_fk1`
    FOREIGN KEY (`collection_id`)
    REFERENCES `#__redshopb_collection` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_collpix_fk2`
    FOREIGN KEY (`product_item_id`)
    REFERENCES `#__redshopb_product_item` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `#__redshopb_collection_product_xref`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_collection_product_xref` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_collection_product_xref` (
  `collection_id` INT(10) UNSIGNED NOT NULL,
  `product_id` INT(10) UNSIGNED NOT NULL,
  `price` DECIMAL(10,2) NULL,
  `ordering` INT(10) NULL,
  `state` TINYINT(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`collection_id`, `product_id`),
  INDEX `#__rs_collprodx_fk2` (`product_id` ASC),
  INDEX `#__rs_collprodx_fk1` (`collection_id` ASC),
  CONSTRAINT `#__rs_collprodx_fk1`
    FOREIGN KEY (`collection_id`)
    REFERENCES `#__redshopb_collection` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_collprodx_fk2`
    FOREIGN KEY (`product_id`)
    REFERENCES `#__redshopb_product` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `#__redshopb_field_group`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_field_group` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_field_group` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `scope` ENUM('product', 'order', 'category', 'company', 'department', 'user') NOT NULL DEFAULT 'product',
  `name` VARCHAR(255) NOT NULL,
  `alias` VARCHAR(255) NOT NULL,
  `ordering` INT(11) UNSIGNED NOT NULL,
  `checked_out` INT(11) NULL,
  `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` INT(11) NULL,
  `created_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` INT(11) NULL,
  `modified_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  INDEX `#__rs_fieldgroup_fk1` (`checked_out` ASC),
  INDEX `#__rs_fieldgroup_fk2` (`created_by` ASC),
  INDEX `#__rs_fieldgroup_fk3` (`modified_by` ASC),
  CONSTRAINT `#__rs_fieldgroup_fk1`
    FOREIGN KEY (`checked_out`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_fieldgroup_fk2`
    FOREIGN KEY (`created_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_fieldgroup_fk3`
    FOREIGN KEY (`modified_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `#__redshopb_field`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_field` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_field` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `scope` ENUM('product', 'order', 'category', 'company', 'department', 'user') NOT NULL DEFAULT 'product',
  `type_id` INT(11) NOT NULL,
  `filter_type_id` INT(11) NULL DEFAULT NULL,
  `field_value_xref_id` INT(10) UNSIGNED NULL DEFAULT NULL,
  `field_group_id` INT(11) UNSIGNED NULL DEFAULT NULL,
  `unit_measure_id` INT(11) NULL DEFAULT NULL,
  `name` VARCHAR(255) NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `alias` VARCHAR(255) NOT NULL,
  `description` VARCHAR(255) NULL,
  `multiple_values` TINYINT(4) NOT NULL DEFAULT 0,
  `only_available` TINYINT(4) NOT NULL DEFAULT 1,
  `default_value` VARCHAR(255) NULL,
  `b2c` TINYINT(4) NOT NULL DEFAULT '0',
  `ordering` INT(11) UNSIGNED NOT NULL,
  `field_value_ordering` TINYINT(4) NOT NULL DEFAULT 0,
  `state` TINYINT(4) NOT NULL DEFAULT '1',
  `required` TINYINT(4) NOT NULL DEFAULT '0',
  `searchable_frontend` TINYINT(4) NOT NULL DEFAULT '1',
  `searchable_backend` TINYINT(4) NOT NULL DEFAULT '1',
  `global` TINYINT(1) NOT NULL DEFAULT 0,
  `params` VARCHAR(2048) NULL COMMENT 'JSON formatted',
  `prefix` VARCHAR(255) NULL,
  `suffix` VARCHAR(255) NULL,
  `decimal_separator` VARCHAR(1) NULL,
  `thousand_separator` VARCHAR(1) NULL,
  `decimal_position` TINYINT(4) UNSIGNED NOT NULL DEFAULT '0',
  `importable` TINYINT(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  INDEX `#__rs_field_fk1` (`type_id` ASC),
  UNIQUE INDEX `idx_alias` (`alias` ASC),
  INDEX `#__rs_field_fk2` (`filter_type_id` ASC),
  INDEX `#__rs_field_fk3` (`field_value_xref_id` ASC),
  INDEX `#__rs_field_fk4` (`field_group_id` ASC),
  INDEX `#__rs_field_fk5` (`unit_measure_id` ASC),
  CONSTRAINT `#__rs_field_fk1`
    FOREIGN KEY (`type_id`)
    REFERENCES `#__redshopb_type` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_field_fk2`
    FOREIGN KEY (`filter_type_id`)
    REFERENCES `#__redshopb_type` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_field_fk3`
    FOREIGN KEY (`field_value_xref_id`)
    REFERENCES `#__redshopb_field` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_field_fk4`
    FOREIGN KEY (`field_group_id`)
    REFERENCES `#__redshopb_field_group` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_field_fk5`
    FOREIGN KEY (`unit_measure_id`)
    REFERENCES `#__redshopb_unit_measure` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `#__redshopb_field_data`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_field_data` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_field_data` (
  `id` INT(10) NOT NULL AUTO_INCREMENT,
  `field_id` INT(10) UNSIGNED NOT NULL,
  `item_id` INT(10) UNSIGNED NOT NULL COMMENT 'Entity id to extend (i.e. product id) - variable FK depending on the extended entity',
  `subitem_id` INT(10) UNSIGNED NULL COMMENT 'Sub item id that overrides the main item value (i.e. product_item)',
  `state` TINYINT(4) NOT NULL DEFAULT '1',
  `field_value` INT(11) NULL DEFAULT NULL,
  `string_value` VARCHAR(2048) NULL,
  `int_value` INT(11) NULL,
  `float_value` DOUBLE(16,4) NULL,
  `text_value` TEXT NULL,
  `params` TEXT NULL,
  PRIMARY KEY (`id`),
  INDEX `#__rs_fdata_fk1` (`field_id` ASC),
  INDEX `idx_item` (`item_id` ASC, `subitem_id` ASC),
  INDEX `idx_value` (`field_value` ASC),
  INDEX `idx_value_string` (`string_value`(255) ASC),
  INDEX `idx_value_int` (`int_value` ASC),
  INDEX `idx_value_float` (`float_value` ASC),
  INDEX `idx_value_text` (`text_value`(255) ASC),
  INDEX `idx_common` (`state` ASC, `field_id` ASC, `item_id` ASC, `subitem_id` ASC, `field_value` ASC),
  INDEX `idx_subitem_id` (`subitem_id` ASC),
  CONSTRAINT `#__rs_fdata_fk1`
    FOREIGN KEY (`field_id`)
    REFERENCES `#__redshopb_field` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `#__redshopb_field_value`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_field_value` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_field_value` (
  `id` INT(10) NOT NULL AUTO_INCREMENT,
  `field_id` INT(10) UNSIGNED NOT NULL,
  `name` VARCHAR(255) NULL,
  `value` VARCHAR(255) NULL,
  `default` TINYINT(4) NOT NULL DEFAULT '0',
  `ordering` INT(11) NOT NULL DEFAULT 0,
  `params` TEXT NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `#__rs_fvalue_fk1` (`field_id` ASC, `ordering` ASC),
  CONSTRAINT `#__rs_fvalue_fk1`
    FOREIGN KEY (`field_id`)
    REFERENCES `#__redshopb_field` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `#__redshopb_conversion`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_conversion` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_conversion` (
  `id` INT(10) NOT NULL AUTO_INCREMENT,
  `product_attribute_id` INT(10) UNSIGNED NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `alias` VARCHAR(255) NULL,
  `default` TINYINT(4) NOT NULL DEFAULT 0,
  `image` VARCHAR(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE INDEX `idx_alias` (`alias` ASC),
  INDEX `#__rs_conv_fk1` (`product_attribute_id` ASC),
  CONSTRAINT `#__rs_conv_fk1`
    FOREIGN KEY (`product_attribute_id`)
    REFERENCES `#__redshopb_product_attribute` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `#__redshopb_product_attribute_value_conv_xref`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_product_attribute_value_conv_xref` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_product_attribute_value_conv_xref` (
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


-- -----------------------------------------------------
-- Table `#__redshopb_stockroom_product_xref`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_stockroom_product_xref` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_stockroom_product_xref` (
  `id` INT(10) NOT NULL AUTO_INCREMENT,
  `stockroom_id` INT(10) UNSIGNED NOT NULL,
  `product_id` INT(10) UNSIGNED NOT NULL,
  `amount` DOUBLE UNSIGNED NOT NULL DEFAULT '0',
  `unlimited` TINYINT(4) NOT NULL DEFAULT '0',
  `stock_upper_level` DOUBLE UNSIGNED NULL DEFAULT NULL,
  `stock_lower_level` DOUBLE UNSIGNED NULL DEFAULT NULL,
  INDEX `#__rs_stockroom_prod_fk2` (`product_id` ASC),
  INDEX `#__rs_stockroom_prod_fk1` (`stockroom_id` ASC),
  PRIMARY KEY (`id`),
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
DROP TABLE IF EXISTS `#__redshopb_stockroom_product_item_xref` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_stockroom_product_item_xref` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `stockroom_id` INT(10) UNSIGNED NOT NULL,
  `product_item_id` INT(10) UNSIGNED NOT NULL,
  `amount` DOUBLE UNSIGNED NOT NULL DEFAULT '0',
  `unlimited` TINYINT(4) NOT NULL DEFAULT '0',
  `stock_upper_level` DOUBLE UNSIGNED NULL DEFAULT NULL,
  `stock_lower_level` DOUBLE UNSIGNED NULL DEFAULT NULL,
  INDEX `#__rs_stockroom_pi_fk2` (`product_item_id` ASC),
  INDEX `#__rs_stockroom_pi_fk1` (`stockroom_id` ASC),
  PRIMARY KEY (`id`),
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


-- -----------------------------------------------------
-- Table `#__redshopb_product_accessory`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_product_accessory` ;

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
-- Table `#__redshopb_product_company_xref`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_product_company_xref` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_product_company_xref` (
  `product_id` INT(10) UNSIGNED NOT NULL,
  `company_id` INT(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`product_id`, `company_id`),
  INDEX `#__rs_prodcomp_fk2` (`company_id` ASC),
  INDEX `#__rs_prodcomp_fk1` (`product_id` ASC),
  CONSTRAINT `#__rs_prodcomp_fk1`
    FOREIGN KEY (`product_id`)
    REFERENCES `#__redshopb_product` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_prodcomp_fk2`
    FOREIGN KEY (`company_id`)
    REFERENCES `#__redshopb_company` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `#__redshopb_newsletter_list`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_newsletter_list` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_newsletter_list` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `alias` VARCHAR(255) NOT NULL,
  `company_id` INT(10) UNSIGNED NULL,
  `segmentation_query` TEXT NULL,
  `segmentation_json` TEXT NULL,
  `plugin` VARCHAR(255) NULL,
  `plugin_id` INT(11) NULL,
  `state` TINYINT(4) NOT NULL DEFAULT '1',
  `checked_out` INT(11) NULL,
  `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` INT(11) NULL,
  `created_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` INT(11) NULL,
  `modified_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  INDEX `#__rs_newslist_fk1` (`company_id` ASC),
  CONSTRAINT `#__rs_newslist_fk1`
    FOREIGN KEY (`company_id`)
    REFERENCES `#__redshopb_company` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `#__redshopb_newsletter`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_newsletter` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_newsletter` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `alias` VARCHAR(255) NOT NULL,
  `company_id` INT(10) UNSIGNED NULL,
  `newsletter_list_id` INT(10) UNSIGNED NULL,
  `subject` VARCHAR(255) NOT NULL,
  `body` LONGTEXT NOT NULL,
  `template_id` INT(10) NOT NULL,
  `plugin` VARCHAR(255) NULL,
  `plugin_id` INT(11) NULL,
  `state` TINYINT(4) NOT NULL,
  `checked_out` INT(11) NULL,
  `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` INT(11) NULL,
  `created_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` INT(11) NULL,
  `modified_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  INDEX `#__rs_newsletter_fk1` (`template_id` ASC),
  UNIQUE INDEX `idx_alias` (`alias` ASC),
  INDEX `#__rs_newsletter_fk2` (`checked_out` ASC),
  INDEX `#__rs_newsletter_fk3` (`created_by` ASC),
  INDEX `#__rs_newsletter_fk4` (`modified_by` ASC),
  INDEX `#__rs_newsletter_fk5` (`newsletter_list_id` ASC),
  INDEX `#__rs_newsletter_fk6` (`company_id` ASC),
  CONSTRAINT `#__rs_newsletter_fk1`
    FOREIGN KEY (`template_id`)
    REFERENCES `#__redshopb_template` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_newsletter_fk2`
    FOREIGN KEY (`checked_out`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_newsletter_fk3`
    FOREIGN KEY (`created_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_newsletter_fk4`
    FOREIGN KEY (`modified_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_newsletter_fk5`
    FOREIGN KEY (`newsletter_list_id`)
    REFERENCES `#__redshopb_newsletter_list` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_newsletter_fk6`
    FOREIGN KEY (`company_id`)
    REFERENCES `#__redshopb_company` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `#__redshopb_favoritelist`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_favoritelist` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_favoritelist` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `alias` VARCHAR(255) NOT NULL,
  `state` TINYINT(4) NOT NULL DEFAULT 1,
  `company_id` INT(10) UNSIGNED NULL,
  `department_id` INT(10) UNSIGNED NULL,
  `user_id` INT(10) UNSIGNED NULL,
  `visible_others` TINYINT(4) NOT NULL DEFAULT 0,
  `checked_out` INT(11) NULL,
  `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` INT(11) NULL,
  `created_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` INT(11) NULL,
  `modified_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  UNIQUE INDEX `idx_alias` (`alias` ASC),
  INDEX `#__rs_favlist_fk1` (`company_id` ASC),
  INDEX `#__rs_favlist_fk2` (`department_id` ASC),
  INDEX `#__rs_favlist_fk3` (`user_id` ASC),
  INDEX `#__rs_favlist_fk4` (`checked_out` ASC),
  INDEX `#__rs_favlist_fk5` (`created_by` ASC),
  INDEX `#__rs_favlist_fk6` (`modified_by` ASC),
  CONSTRAINT `#__rs_favlist_fk1`
    FOREIGN KEY (`company_id`)
    REFERENCES `#__redshopb_company` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_favlist_fk2`
    FOREIGN KEY (`department_id`)
    REFERENCES `#__redshopb_department` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_favlist_fk3`
    FOREIGN KEY (`user_id`)
    REFERENCES `#__redshopb_user` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_favlist_fk4`
    FOREIGN KEY (`checked_out`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_favlist_fk5`
    FOREIGN KEY (`created_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_favlist_fk6`
    FOREIGN KEY (`modified_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `#__redshopb_favoritelist_product_xref`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_favoritelist_product_xref` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_favoritelist_product_xref` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `favoritelist_id` INT(11) UNSIGNED NOT NULL,
  `product_id` INT(10) UNSIGNED NOT NULL,
  `quantity` DOUBLE UNSIGNED NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  INDEX `#__rs_favlistprod_fk2` (`product_id` ASC),
  INDEX `#__rs_favlistprod_fk1` (`favoritelist_id` ASC),
  CONSTRAINT `#__rs_favlistprod_fk1`
    FOREIGN KEY (`favoritelist_id`)
    REFERENCES `#__redshopb_favoritelist` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_favlistprod_fk2`
    FOREIGN KEY (`product_id`)
    REFERENCES `#__redshopb_product` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `#__redshopb_favoritelist_product_item_xref`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_favoritelist_product_item_xref` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_favoritelist_product_item_xref` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `favoritelist_id` INT(11) UNSIGNED NOT NULL,
  `product_item_id` INT(10) UNSIGNED NOT NULL,
  `quantity` DOUBLE UNSIGNED NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  INDEX `#__rs_favlistpi_fk2` (`product_item_id` ASC),
  INDEX `#__rs_favlistpi_fk1` (`favoritelist_id` ASC),
  CONSTRAINT `#__rs_favlistpi_fk1`
    FOREIGN KEY (`favoritelist_id`)
    REFERENCES `#__redshopb_favoritelist` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_favlistpi_fk2`
    FOREIGN KEY (`product_item_id`)
    REFERENCES `#__redshopb_product_item` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `#__redshopb_newsletter_user_xref`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_newsletter_user_xref` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_newsletter_user_xref` (
  `newsletter_list_id` INT(10) UNSIGNED NOT NULL,
  `user_id` INT(10) UNSIGNED NOT NULL,
  `fixed` TINYINT(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`newsletter_list_id`, `user_id`),
  INDEX `#__rs_newsletter_ux_fk2` (`user_id` ASC),
  INDEX `#__rs_newsletter_ux_fk1` (`newsletter_list_id` ASC),
  CONSTRAINT `#__rs_newsletter_ux_fk1`
    FOREIGN KEY (`newsletter_list_id`)
    REFERENCES `#__redshopb_newsletter_list` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_newsletter_ux_fk2`
    FOREIGN KEY (`user_id`)
    REFERENCES `#__redshopb_user` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `#__redshopb_cart`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_cart` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_cart` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `state` TINYINT(4) NOT NULL DEFAULT '1',
  `company_id` INT(10) UNSIGNED NULL,
  `department_id` INT(10) UNSIGNED NULL,
  `user_id` INT(10) UNSIGNED NULL,
  `visible_others` TINYINT(4) NOT NULL DEFAULT '0',
  `user_cart` TINYINT(4) NOT NULL DEFAULT 0,
  `last_order` DATETIME NULL DEFAULT NULL,
  `checked_out` INT(11) NULL,
  `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` INT(11) NULL,
  `created_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` INT(11) NULL,
  `modified_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  INDEX `#__rs_cart_fk1` (`company_id` ASC),
  INDEX `#__rs_cart_fk2` (`department_id` ASC),
  INDEX `#__rs_cart_fk3` (`user_id` ASC),
  INDEX `#__rs_cart_fk4` (`checked_out` ASC),
  INDEX `#__rs_cart_fk5` (`created_by` ASC),
  INDEX `#__rs_cart_fk6` (`modified_by` ASC),
  CONSTRAINT `#__rs_cart_fk1`
    FOREIGN KEY (`company_id`)
    REFERENCES `#__redshopb_company` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_cart_fk2`
    FOREIGN KEY (`department_id`)
    REFERENCES `#__redshopb_department` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_cart_fk3`
    FOREIGN KEY (`user_id`)
    REFERENCES `#__redshopb_user` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_cart_fk4`
    FOREIGN KEY (`checked_out`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_cart_fk5`
    FOREIGN KEY (`created_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_cart_fk6`
    FOREIGN KEY (`modified_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `#__redshopb_cart_item`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_cart_item` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_cart_item` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `cart_id` INT(11) NOT NULL,
  `product_id` INT(10) UNSIGNED NOT NULL,
  `product_item_id` INT(10) UNSIGNED NULL,
  `parent_cart_item_id` INT(11) NULL COMMENT 'When it’s an accessory item, it points to the parent item',
  `collection_id` INT(10) UNSIGNED NULL,
  `quantity` DOUBLE UNSIGNED NOT NULL,
  `state` TINYINT(4) NOT NULL DEFAULT '1',
  `created_by` INT(11) NULL,
  `created_date` DATETIME NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` INT(11) NULL,
  `modified_date` DATETIME NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  INDEX `#__rs_cartitem_fk1` (`cart_id` ASC),
  INDEX `#__rs_cartitem_fk2` (`created_by` ASC),
  INDEX `#__rs_cartitem_fk3` (`modified_by` ASC),
  INDEX `idx_product` (`product_id` ASC),
  INDEX `idx_product_item` (`product_item_id` ASC),
  INDEX `#__rs_cartitem_fk4` (`parent_cart_item_id` ASC),
  INDEX `idx_collection` (`collection_id` ASC),
  CONSTRAINT `#__rs_cartitem_fk1`
    FOREIGN KEY (`cart_id`)
    REFERENCES `#__redshopb_cart` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_cartitem_fk2`
    FOREIGN KEY (`created_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_cartitem_fk3`
    FOREIGN KEY (`modified_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_cartitem_fk4`
    FOREIGN KEY (`parent_cart_item_id`)
    REFERENCES `#__redshopb_cart_item` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `#__redshopb_newsletter_user_stats`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_newsletter_user_stats` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_newsletter_user_stats` (
  `newsletter_id` INT(10) UNSIGNED NOT NULL,
  `user_id` INT(10) UNSIGNED NOT NULL,
  `html` TINYINT(3) UNSIGNED NOT NULL DEFAULT '1',
  `sent` TINYINT(3) UNSIGNED NOT NULL DEFAULT '1',
  `send_date` INT(10) UNSIGNED NOT NULL,
  `open` TINYINT(3) UNSIGNED NOT NULL DEFAULT '1',
  `open_date` INT(10) UNSIGNED NOT NULL,
  `bounce` TINYINT(4) UNSIGNED NOT NULL DEFAULT '0',
  `fail` TINYINT(4) UNSIGNED NOT NULL DEFAULT '0',
  `ip` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`newsletter_id`, `user_id`),
  INDEX `idx_user_id` (`user_id` ASC),
  INDEX `idx_send_date` (`send_date` ASC),
  CONSTRAINT `#__rs_nl_userstats_fk1`
    FOREIGN KEY (`newsletter_id`)
    REFERENCES `#__redshopb_newsletter` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_nl_userstats_fk2`
    FOREIGN KEY (`user_id`)
    REFERENCES `#__redshopb_user` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `#__redshopb_offer`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_offer` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_offer` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `vendor_id` INT(10) UNSIGNED NOT NULL,
  `customer_type` ENUM('employee','department','company','') NOT NULL DEFAULT '',
  `company_id` INT(10) UNSIGNED NULL,
  `department_id` INT(10) UNSIGNED NULL,
  `user_id` INT(10) UNSIGNED NULL,
  `collection_id` INT(10) UNSIGNED NULL,
  `status` ENUM('requested', 'sent', 'accepted', 'rejected', 'ordered', 'created','pending') NOT NULL,
  `template_id` INT(10) NULL,
  `subtotal` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `discount_type` ENUM('total','percent') NOT NULL DEFAULT 'percent',
  `discount` DECIMAL(12,2) NULL,
  `total` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `requested_date` DATETIME NULL,
  `sent_date` DATETIME NULL,
  `execution_date` DATETIME NULL COMMENT 'Date for both For accept and reject statuses',
  `order_date` DATETIME NULL,
  `expiration_date` DATETIME NULL,
  `state` TINYINT(4) NOT NULL DEFAULT '1',
  `comments` TINYTEXT NULL,
  `checked_out` INT(11) NULL,
  `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` INT(11) NULL,
  `created_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` INT(11) NULL,
  `modified_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  INDEX `#__rs_offer_fk1` (`company_id` ASC),
  INDEX `#__rs_offer_fk2` (`department_id` ASC),
  INDEX `#__rs_offer_fk3` (`user_id` ASC),
  INDEX `#__rs_offer_fk4` (`checked_out` ASC),
  INDEX `#__rs_offer_fk5` (`created_by` ASC),
  INDEX `#__rs_offer_fk6` (`modified_by` ASC),
  INDEX `#__rs_offer_fk7` (`vendor_id` ASC),
  INDEX `#__rs_offer_fk8` (`collection_id` ASC),
  INDEX `#__rs_offer_fk9` (`template_id` ASC),
  CONSTRAINT `#__rs_offer_fk1`
    FOREIGN KEY (`company_id`)
    REFERENCES `#__redshopb_company` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_offer_fk2`
    FOREIGN KEY (`department_id`)
    REFERENCES `#__redshopb_department` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_offer_fk3`
    FOREIGN KEY (`user_id`)
    REFERENCES `#__redshopb_user` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_offer_fk4`
    FOREIGN KEY (`checked_out`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_offer_fk5`
    FOREIGN KEY (`created_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_offer_fk6`
    FOREIGN KEY (`modified_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_offer_fk7`
    FOREIGN KEY (`vendor_id`)
    REFERENCES `#__redshopb_company` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_offer_fk8`
    FOREIGN KEY (`collection_id`)
    REFERENCES `#__redshopb_collection` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_offer_fk9`
    FOREIGN KEY (`template_id`)
    REFERENCES `#__redshopb_template` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `#__redshopb_offer_item_xref`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_offer_item_xref` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_offer_item_xref` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `offer_id` INT(10) UNSIGNED NOT NULL,
  `product_id` INT(10) UNSIGNED NOT NULL,
  `product_item_id` INT(10) UNSIGNED NULL,
  `quantity` DOUBLE NOT NULL,
  `unit_price` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `subtotal` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `discount_type` ENUM('total','percent') NOT NULL DEFAULT 'percent',
  `discount` DECIMAL(12,2) NULL,
  `total` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `params` TEXT NULL,
  INDEX `#__rs_offitem_fk1` (`offer_id` ASC),
  INDEX `#__rs_offitem_fk2` (`product_id` ASC),
  PRIMARY KEY (`id`),
  INDEX `#__rs_offitem_fk3` (`product_item_id` ASC),
  CONSTRAINT `#__rs_offitem_fk2`
    FOREIGN KEY (`product_id`)
    REFERENCES `#__redshopb_product` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_offitem_fk1`
    FOREIGN KEY (`offer_id`)
    REFERENCES `#__redshopb_offer` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_offitem_fk3`
    FOREIGN KEY (`product_item_id`)
    REFERENCES `#__redshopb_product_item` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `#__redshopb_shipping_configuration`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_shipping_configuration` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_shipping_configuration` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `extension_name` VARCHAR(255) NOT NULL DEFAULT '',
  `owner_name` VARCHAR(255) NOT NULL DEFAULT '',
  `shipping_name` VARCHAR(50) NOT NULL DEFAULT '',
  `params` TEXT NOT NULL,
  `state` TINYINT(1) NOT NULL DEFAULT 1,
  `checked_out` INT(11) NULL DEFAULT NULL,
  `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` INT(11) NULL DEFAULT NULL,
  `created_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` INT(11) NULL DEFAULT NULL,
  `modified_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  INDEX `idx_extension_config` (`extension_name` ASC, `owner_name` ASC, `shipping_name` ASC))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `#__redshopb_shipping_rates`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_shipping_rates` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_shipping_rates` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `shipping_configuration_id` INT(10) UNSIGNED NOT NULL,
  `name` VARCHAR(255) NOT NULL DEFAULT '',
  `countries` TEXT NOT NULL,
  `zip_start` VARCHAR(20) NOT NULL DEFAULT '',
  `zip_end` VARCHAR(20) NOT NULL DEFAULT '',
  `weight_start` DECIMAL(10,2) NOT NULL,
  `weight_end` DECIMAL(10,2) NOT NULL,
  `volume_start` DECIMAL(10,2) NOT NULL,
  `volume_end` DECIMAL(10,2) NOT NULL,
  `length_start` DECIMAL(10,2) NOT NULL,
  `length_end` DECIMAL(10,2) NOT NULL,
  `width_start` DECIMAL(10,2) NOT NULL,
  `width_end` DECIMAL(10,2) NOT NULL,
  `height_start` DECIMAL(10,2) NOT NULL,
  `height_end` DECIMAL(10,2) NOT NULL,
  `order_total_start` DECIMAL(10,2) NOT NULL DEFAULT '0.00',
  `order_total_end` DECIMAL(10,2) NOT NULL,
  `on_product` TEXT NOT NULL,
  `on_product_discount_group` TEXT NOT NULL,
  `on_category` TEXT NOT NULL,
  `priority` TINYINT(4) NOT NULL DEFAULT 0,
  `price` DECIMAL(10,2) NOT NULL DEFAULT '0.00',
  `shipping_location_info` TEXT NOT NULL,
  `state` TINYINT(4) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  INDEX `idx_name` (`name` ASC),
  INDEX `#__rs_sr_config_fk_1` (`shipping_configuration_id` ASC),
  INDEX `idx_filter` (`zip_start` ASC, `countries`(255) ASC, `zip_end` ASC, `weight_start` ASC, `weight_end` ASC, `volume_start` ASC, `volume_end` ASC, `length_start` ASC, `length_end` ASC, `width_start` ASC, `width_end` ASC, `height_start` ASC, `height_end` ASC, `order_total_start` ASC, `order_total_end` ASC),
  CONSTRAINT `#__rs_sr_config_fk_1`
    FOREIGN KEY (`shipping_configuration_id`)
    REFERENCES `#__redshopb_shipping_configuration` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `#__redshopb_reports`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_reports` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_reports` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL DEFAULT '',
  `rows` INT(11) NOT NULL DEFAULT 0,
  `params` TEXT NOT NULL,
  `created_by` INT(11) NULL DEFAULT NULL,
  `created_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` INT(11) NULL DEFAULT NULL,
  `modified_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  INDEX `idx_name` (`name` ASC))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `#__redshopb_filter_fieldset_xref`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_filter_fieldset_xref` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_filter_fieldset_xref` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `fieldset_id` INT(10) UNSIGNED NOT NULL,
  `field_id` INT(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `#__rs_filfiefld_fk1` (`fieldset_id` ASC),
  INDEX `#__rs_filfiefld_fk2` (`field_id` ASC),
  CONSTRAINT `#__rs_filfiefld_fk1`
    FOREIGN KEY (`fieldset_id`)
    REFERENCES `#__redshopb_filter_fieldset` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_filfiefld_fk2`
    FOREIGN KEY (`field_id`)
    REFERENCES `#__redshopb_field` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `#__redshopb_return_orders`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_return_orders` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_return_orders` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` INT(10) UNSIGNED NOT NULL,
  `order_item_id` INT(10) UNSIGNED NOT NULL,
  `quantity` INT(10) NOT NULL DEFAULT 1,
  `comment` TINYTEXT NULL,
  `created_by` INT(11) NULL,
  `created_date` DATETIME NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` INT(11) NULL,
  `modified_date` DATETIME NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  INDEX `#__rs_retord_fk1` (`order_id` ASC),
  INDEX `#__rs_retord_fk2` (`order_item_id` ASC),
  INDEX `#__rs_retord_fk3` (`created_by` ASC),
  INDEX `#__rs_retord_fk4` (`modified_by` ASC),
  CONSTRAINT `#__rs_retord_fk1`
    FOREIGN KEY (`order_id`)
    REFERENCES `#__redshopb_order` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_retord_fk2`
    FOREIGN KEY (`order_item_id`)
    REFERENCES `#__redshopb_order_item` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_retord_fk3`
    FOREIGN KEY (`created_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_retord_fk4`
    FOREIGN KEY (`modified_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `#__redshopb_webservice_permission`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_webservice_permission` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_webservice_permission` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `scope` VARCHAR(255) NOT NULL DEFAULT 'product',
  `name` VARCHAR(255) NOT NULL DEFAULT '',
  `description` VARCHAR(500) NOT NULL DEFAULT '',
  `manual` TINYINT(4) NOT NULL DEFAULT '0',
  `state` TINYINT(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  INDEX `idx_scope` (`scope` ASC, `name` ASC, `state` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `#__redshopb_webservice_permission_user_xref`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_webservice_permission_user_xref` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_webservice_permission_user_xref` (
  `user_id` INT(11) NOT NULL,
  `webservice_permission_id` INT(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`user_id`, `webservice_permission_id`),
  INDEX `#__rs_webperuse_fk1` (`user_id` ASC),
  INDEX `#__rs_webperuse_fk2` (`webservice_permission_id` ASC),
  CONSTRAINT `#__rs_webperuse_fk1`
    FOREIGN KEY (`user_id`)
    REFERENCES `#__users` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_webperuse_fk2`
    FOREIGN KEY (`webservice_permission_id`)
    REFERENCES `#__redshopb_webservice_permission` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `#__redshopb_webservice_permission_item_xref`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_webservice_permission_item_xref` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_webservice_permission_item_xref` (
  `item_id` INT(11) NOT NULL COMMENT 'It depends on the webservice permission scope, it may be product ID or category ID or some other item ID',
  `scope` VARCHAR(255) NOT NULL DEFAULT 'product',
  `webservice_permission_id` INT(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`item_id`, `webservice_permission_id`),
  INDEX `#__rs_webperite_fk1` (`webservice_permission_id` ASC),
  CONSTRAINT `#__rs_webperite_fk1`
    FOREIGN KEY (`webservice_permission_id`)
    REFERENCES `#__redshopb_webservice_permission` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `#__redshopb_word`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_word` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_word` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `word` VARCHAR(255) NOT NULL,
  `shared` TINYINT(4) NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  INDEX `idx_word` (`word` ASC),
  INDEX `idx_shared` (`shared` ASC))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `#__redshopb_tax`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_tax` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_tax` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `tax_rate` DECIMAL(10,4) NULL DEFAULT NULL,
  `state` TINYINT(1) NOT NULL DEFAULT 1,
  `country_id` SMALLINT(4) UNSIGNED NULL DEFAULT NULL,
  `state_id` INT(10) UNSIGNED NULL DEFAULT NULL,
  `is_eu_country` TINYINT(4) NOT NULL DEFAULT '0',
  `company_id` INT(10) UNSIGNED NULL DEFAULT NULL,
  `checked_out` INT(11) NULL DEFAULT NULL,
  `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` INT(11) NULL DEFAULT NULL,
  `created_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` INT(11) NULL DEFAULT NULL,
  `modified_date` DATETIME NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  INDEX `idx_state` (`state` ASC),
  INDEX `#__rs_t_fk2_idx` (`checked_out` ASC),
  INDEX `#__rs_t_fk3_idx` (`created_by` ASC),
  INDEX `#__rs_t_fk4_idx` (`modified_by` ASC),
  INDEX `#__rs_t_fk5_idx` (`country_id` ASC),
  INDEX `#__rs_t_fk6_idx` (`state_id` ASC),
  INDEX `#__rs_t_fk7_idx` (`company_id` ASC),
  CONSTRAINT `#__rs_t_fk2`
    FOREIGN KEY (`checked_out`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_t_fk3`
    FOREIGN KEY (`created_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_t_fk4`
    FOREIGN KEY (`modified_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__redshopb_tax_ibfk_2`
    FOREIGN KEY (`country_id`)
    REFERENCES `#__redshopb_country` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `#__redshopb_tax_ibfk_1`
    FOREIGN KEY (`state_id`)
    REFERENCES `#__redshopb_state` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `#__redshopb_tax_ibfk_3`
    FOREIGN KEY (`company_id`)
    REFERENCES `#__redshopb_company` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `#__redshopb_word_synonym_xref`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_word_synonym_xref` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_word_synonym_xref` (
  `synonym_word_id` INT(10) UNSIGNED NOT NULL,
  `main_word_id` INT(10) UNSIGNED NOT NULL,
  INDEX `idx_synonym_word_id` (`synonym_word_id` ASC),
  INDEX `idx_main_word_id` (`main_word_id` ASC),
  CONSTRAINT `#__rs_word_fk1`
    FOREIGN KEY (`synonym_word_id`)
    REFERENCES `#__redshopb_word` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_word_fk2`
    FOREIGN KEY (`main_word_id`)
    REFERENCES `#__redshopb_word` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `#__redshopb_word_synonym_search_sets`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_word_synonym_search_sets` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_word_synonym_search_sets` (
  `hash` VARCHAR(255) NOT NULL,
  `cache` DATETIME NOT NULL,
  `phrase` VARCHAR(255) NOT NULL,
  `product_set` LONGTEXT NOT NULL,
  PRIMARY KEY (`hash`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `#__redshopb_tax_group_xref`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_tax_group_xref` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_tax_group_xref` (
  `tax_group_id` INT(10) UNSIGNED NOT NULL,
  `tax_id` INT(10) UNSIGNED NOT NULL,
  INDEX `#__rs_tgx_fk1_idx` (`tax_group_id` ASC),
  INDEX `#__rs_tgx_fk2_idx` (`tax_id` ASC),
  CONSTRAINT `#__redshopb_tax_group_xref_ibfk_2`
    FOREIGN KEY (`tax_id`)
    REFERENCES `#__redshopb_tax` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `#__redshopb_tax_group_xref_ibfk_1`
    FOREIGN KEY (`tax_group_id`)
    REFERENCES `#__redshopb_tax_group` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `#__redshopb_order_tax`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_order_tax` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_order_tax` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` INT(10) UNSIGNED NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `tax_rate` DECIMAL(10,4) NOT NULL,
  `price` DECIMAL(10,2) NOT NULL,
  `product_id` INT(10) UNSIGNED NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `#__rs_order_tx_fk1` (`order_id` ASC),
  CONSTRAINT `#__redshopb_order_tax_ibfk_1`
    FOREIGN KEY (`order_id`)
    REFERENCES `#__redshopb_order` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `#__redshopb_product_complimentary`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_product_complimentary` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_product_complimentary` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` INT(10) UNSIGNED NOT NULL,
  `complimentary_product_id` INT(10) UNSIGNED NOT NULL,
  `state` TINYINT(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  INDEX `#__rs_prod_compl_fk2` (`complimentary_product_id` ASC),
  INDEX `#__rs_prod_compl_fk1` (`product_id` ASC),
  CONSTRAINT `#__rs_prod_compl_fk1`
    FOREIGN KEY (`product_id`)
    REFERENCES `#__redshopb_product` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_prod_compl_fk2`
    FOREIGN KEY (`complimentary_product_id`)
    REFERENCES `#__redshopb_product` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `#__redshopb_category_field_xref`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_category_field_xref` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_category_field_xref` (
  `category_id` INT(10) UNSIGNED NOT NULL,
  `field_id` INT(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`category_id`, `field_id`),
  INDEX `#__rs_catfield_fk2` (`field_id` ASC),
  INDEX `#__rs_catfield_fk1` (`category_id` ASC),
  CONSTRAINT `#__rs_catfield_fk1`
    FOREIGN KEY (`category_id`)
    REFERENCES `#__redshopb_category` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_catfield_fk2`
    FOREIGN KEY (`field_id`)
    REFERENCES `#__redshopb_field` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `#__redshopb_holiday`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_holiday` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_holiday` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(45) NOT NULL,
  `day` INT NOT NULL,
  `month` INT NOT NULL,
  `year` INT NULL,
  `country_id` SMALLINT(5) UNSIGNED NOT NULL,
  `checked_out` INT(11) NULL,
  `checked_out_time` DATETIME NULL,
  `created_by` INT(11) NULL DEFAULT NULL,
  `created_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` INT(11) NULL DEFAULT NULL,
  `modified_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  INDEX `#__rs_holiday_fk1` (`country_id` ASC),
  INDEX `#__rs_holiday_fk2` (`checked_out` ASC),
  INDEX `#__rs_holiday_fk3` (`created_by` ASC),
  INDEX `#__rs_holiday_fk4` (`modified_by` ASC),
  CONSTRAINT `#__rs_holiday_fk1`
    FOREIGN KEY (`country_id`)
    REFERENCES `#__redshopb_country` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_holiday_fk2`
    FOREIGN KEY (`checked_out`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_holiday_fk3`
    FOREIGN KEY (`created_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_holiday_fk4`
    FOREIGN KEY (`modified_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `#__redshopb_user_multi_company`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_user_multi_company` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_user_multi_company` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT(10) UNSIGNED NOT NULL,
  `company_id` INT(10) UNSIGNED NOT NULL,
  `role_id` INT(10) UNSIGNED NOT NULL,
  `main` TINYINT(4) NOT NULL DEFAULT 0,
  `state` TINYINT(4) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `#__idx_user_company` (`user_id` ASC, `company_id` ASC),
  INDEX `#__rs_use_mul_com_fk1` (`user_id` ASC),
  INDEX `#__rs_use_mul_com_fk2` (`company_id` ASC),
  INDEX `#__rs_use_mul_com_fk3` (`role_id` ASC),
  INDEX `idx_state` (`state` ASC, `main` ASC),
  CONSTRAINT `#__rs_use_mul_com_fk1`
    FOREIGN KEY (`user_id`)
    REFERENCES `#__redshopb_user` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_use_mul_com_fk2`
    FOREIGN KEY (`company_id`)
    REFERENCES `#__redshopb_company` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_use_mul_com_fk3`
    FOREIGN KEY (`role_id`)
    REFERENCES `#__redshopb_role_type` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `#__redshopb_product_item_discount_group_xref`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_product_item_discount_group_xref` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_product_item_discount_group_xref` (
  `product_item_id` INT(10) UNSIGNED NOT NULL,
  `discount_group_id` INT(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`product_item_id`, `discount_group_id`),
  INDEX `#__rs_prod_item_dgx_fk1` (`discount_group_id` ASC),
  INDEX `#__rs_prod_item_dgx_fk2` (`product_item_id` ASC),
  CONSTRAINT `#__rs_prod_item_dgx_fk1`
    FOREIGN KEY (`discount_group_id`)
    REFERENCES `#__redshopb_product_discount_group` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_prod_item_dgx_fk2`
    FOREIGN KEY (`product_item_id`)
    REFERENCES `#__redshopb_product_item` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `#__redshopb_free_shipping_threshold_purchases`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_free_shipping_threshold_purchases` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_free_shipping_threshold_purchases` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_discount_group_id` INT(10) UNSIGNED NULL DEFAULT NULL,
  `category_id` INT(10) UNSIGNED NULL DEFAULT NULL,
  `threshold_expenditure` FLOAT NULL,
  `created_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` INT(11) NULL DEFAULT NULL,
  `modified_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` INT(11) NULL DEFAULT NULL,
  `checked_out` INT(11) NULL DEFAULT NULL,
  `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  UNIQUE INDEX `idx_alias` (`product_discount_group_id` ASC),
  UNIQUE INDEX `category_id_UNIQUE` (`category_id` ASC),
  CONSTRAINT `#__rs_freeshipthrespur_fk1`
    FOREIGN KEY (`product_discount_group_id`)
    REFERENCES `#__redshopb_product_discount_group` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `#__rs_freeshipthrespur_fk2`
    FOREIGN KEY (`category_id`)
    REFERENCES `#__redshopb_category` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `#__redshopb_table_lock`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `#__redshopb_table_lock` ;

CREATE TABLE IF NOT EXISTS `#__redshopb_table_lock` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `table_name` VARCHAR(100) NOT NULL,
  `table_id` INT(10) UNSIGNED NOT NULL,
  `column_name` VARCHAR(255) NOT NULL,
  `locked_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `locked_by` INT(11) NULL DEFAULT NULL,
  `locked_method` VARCHAR(100) NOT NULL DEFAULT 'User',
  PRIMARY KEY (`id`),
  UNIQUE INDEX `rs_table_lock_UNIQUE` (`table_name` ASC, `table_id` ASC, `column_name` ASC),
  INDEX `#__rs_datalock_fk1_idx` (`locked_by` ASC),
  CONSTRAINT `#__rs_datalock_fk1`
    FOREIGN KEY (`locked_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;

SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
