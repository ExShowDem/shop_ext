-- -----------------------------------------------------
-- Table `#__redshopb_acl_simple_access_xref`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_acl_simple_access_xref_1_6_0`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_acl_simple_access_xref_1_6_0`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_acl_simple_access_xref' AND `constraint_name` = '#__racl_sax_fk1') THEN
    ALTER TABLE `#__redshopb_acl_simple_access_xref` DROP FOREIGN KEY `#__racl_sax_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_acl_simple_access_xref' AND `index_name` = '#__racl_sax_fk1') THEN
    ALTER TABLE `#__redshopb_acl_simple_access_xref` DROP INDEX `#__racl_sax_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_acl_simple_access_xref' AND `constraint_name` = '#__racl_sax_fk2') THEN
    ALTER TABLE `#__redshopb_acl_simple_access_xref` DROP FOREIGN KEY `#__racl_sax_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_acl_simple_access_xref' AND `index_name` = '#__racl_sax_fk2') THEN
    ALTER TABLE `#__redshopb_acl_simple_access_xref` DROP INDEX `#__racl_sax_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_acl_simple_access_xref' AND `constraint_name` = '#__racl_sax_fk3') THEN
    ALTER TABLE `#__redshopb_acl_simple_access_xref` DROP FOREIGN KEY `#__racl_sax_fk3`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_acl_simple_access_xref' AND `index_name` = '#__racl_sax_fk3') THEN
    ALTER TABLE `#__redshopb_acl_simple_access_xref` DROP INDEX `#__racl_sax_fk3`;
  END IF;
END//

DELIMITER ;


-- -----------------------------------------------------
-- Table `#__redshopb_acl_access`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_acl_access_1_6_0`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_acl_access_1_6_0`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_acl_access' AND `constraint_name` = '#__racl_a_fk1') THEN
    ALTER TABLE `#__redshopb_acl_access` DROP FOREIGN KEY `#__racl_a_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_acl_access' AND `index_name` = '#__racl_a_fk1') THEN
    ALTER TABLE `#__redshopb_acl_access` DROP INDEX `#__racl_a_fk1`;
  END IF;
END//

DELIMITER ;


-- -----------------------------------------------------
-- Table `#__redshopb_acl_rule`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_acl_rule_1_6_0`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_acl_rule_1_6_0`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_acl_rule' AND `constraint_name` = '#__racl_r_fk1') THEN
    ALTER TABLE `#__redshopb_acl_rule` DROP FOREIGN KEY `#__racl_r_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_acl_rule' AND `index_name` = '#__racl_r_fk1') THEN
    ALTER TABLE `#__redshopb_acl_rule` DROP INDEX `#__racl_r_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_acl_rule' AND `constraint_name` = '#__racl_r_fk2') THEN
    ALTER TABLE `#__redshopb_acl_rule` DROP FOREIGN KEY `#__racl_r_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_acl_rule' AND `index_name` = '#__racl_r_fk2') THEN
    ALTER TABLE `#__redshopb_acl_rule` DROP INDEX `#__racl_r_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_acl_rule' AND `constraint_name` = '#__racl_r_fk3') THEN
    ALTER TABLE `#__redshopb_acl_rule` DROP FOREIGN KEY `#__racl_r_fk3`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_acl_rule' AND `index_name` = '#__racl_r_fk3') THEN
    ALTER TABLE `#__redshopb_acl_rule` DROP INDEX `#__racl_r_fk3`;
  END IF;
END//

DELIMITER ;


-- -----------------------------------------------------
-- Table `#__redshopb_role_type`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_role_type_1_6_0`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_role_type_1_6_0`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_currency' AND `constraint_name` = '#__rrole_t_fk1') THEN
    ALTER TABLE `#__redshopb_currency` DROP FOREIGN KEY `#__rrole_t_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_currency' AND `index_name` = '#__rrole_t_fk1') THEN
    ALTER TABLE `#__redshopb_currency` DROP INDEX `#__rrole_t_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_currency' AND `constraint_name` = '#__rrole_t_fk2') THEN
    ALTER TABLE `#__redshopb_currency` DROP FOREIGN KEY `#__rrole_t_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_currency' AND `index_name` = '#__rrole_t_fk2') THEN
    ALTER TABLE `#__redshopb_currency` DROP INDEX `#__rrole_t_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_currency' AND `constraint_name` = '#__rrole_t_fk3') THEN
    ALTER TABLE `#__redshopb_currency` DROP FOREIGN KEY `#__rrole_t_fk3`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_currency' AND `index_name` = '#__rrole_t_fk3') THEN
    ALTER TABLE `#__redshopb_currency` DROP INDEX `#__rrole_t_fk3`;
  END IF;
END//

DELIMITER ;


-- -----------------------------------------------------
-- Table `#__redshopb_currency`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_currency_1_6_0`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_currency_1_6_0`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_currency' AND `constraint_name` = '#__rcurrency_fk1') THEN
    ALTER TABLE `#__redshopb_currency` DROP FOREIGN KEY `#__rcurrency_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_currency' AND `index_name` = '#__rcurrency_fk1') THEN
    ALTER TABLE `#__redshopb_currency` DROP INDEX `#__rcurrency_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_currency' AND `constraint_name` = '#__rcurrency_fk2') THEN
    ALTER TABLE `#__redshopb_currency` DROP FOREIGN KEY `#__rcurrency_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_currency' AND `index_name` = '#__rcurrency_fk2') THEN
    ALTER TABLE `#__redshopb_currency` DROP INDEX `#__rcurrency_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_currency' AND `constraint_name` = '#__rcurrency_fk3') THEN
    ALTER TABLE `#__redshopb_currency` DROP FOREIGN KEY `#__rcurrency_fk3`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_currency' AND `index_name` = '#__rcurrency_fk3') THEN
    ALTER TABLE `#__redshopb_currency` DROP INDEX `#__rcurrency_fk3`;
  END IF;
END//

DELIMITER ;


-- -----------------------------------------------------
-- Table `#__redshopb_tag`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_tag_1_6_0`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_tag_1_6_0`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_tag' AND `index_name` = 'idx_checkout') THEN
    ALTER TABLE `#__redshopb_tag` DROP KEY `idx_checkout`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_tag' AND `constraint_name` = '#__rtag_fk1') THEN
    ALTER TABLE `#__redshopb_tag` DROP FOREIGN KEY `#__rtag_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_tag' AND `index_name` = '#__rtag_fk1') THEN
    ALTER TABLE `#__redshopb_tag` DROP INDEX `#__rtag_fk1`;
  END IF;
END//

DELIMITER ;


-- -----------------------------------------------------
-- Table `#__redshopb_product`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_product_1_6_0`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_product_1_6_0`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_product' AND `constraint_name` = '#__rproduct_fk1') THEN
    ALTER TABLE `#__redshopb_product` DROP FOREIGN KEY `#__rproduct_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_product' AND `index_name` = '#__rproduct_fk1') THEN
    ALTER TABLE `#__redshopb_product` DROP INDEX `#__rproduct_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_product' AND `constraint_name` = '#__rproduct_fk2') THEN
    ALTER TABLE `#__redshopb_product` DROP FOREIGN KEY `#__rproduct_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_product' AND `index_name` = '#__rproduct_fk2') THEN
    ALTER TABLE `#__redshopb_product` DROP INDEX `#__rproduct_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_product' AND `constraint_name` = '#__rproduct_fk3') THEN
    ALTER TABLE `#__redshopb_product` DROP FOREIGN KEY `#__rproduct_fk3`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_product' AND `index_name` = '#__rproduct_fk3') THEN
    ALTER TABLE `#__redshopb_product` DROP INDEX `#__rproduct_fk3`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_product' AND `constraint_name` = '#__rproduct_fk4') THEN
    ALTER TABLE `#__redshopb_product` DROP FOREIGN KEY `#__rproduct_fk4`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_product' AND `index_name` = '#__rproduct_fk4') THEN
    ALTER TABLE `#__redshopb_product` DROP INDEX `#__rproduct_fk4`;
  END IF;
END//

DELIMITER ;


-- -----------------------------------------------------
-- Table `#__redshopb_product_tag_xref`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_product_tag_xref_1_6_0`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_product_tag_xref_1_6_0`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_product_tag_xref' AND `constraint_name` = '#__rproduct_tx_fk1') THEN
    ALTER TABLE `#__redshopb_product_tag_xref` DROP FOREIGN KEY `#__rproduct_tx_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_product_tag_xref' AND `index_name` = '#__rproduct_tx_fk1') THEN
    ALTER TABLE `#__redshopb_product_tag_xref` DROP INDEX `#__rproduct_tx_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_product_tag_xref' AND `constraint_name` = '#__rproduct_tx_fk2') THEN
    ALTER TABLE `#__redshopb_product_tag_xref` DROP FOREIGN KEY `#__rproduct_tx_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_product_tag_xref' AND `index_name` = '#__rproduct_tx_fk2') THEN
    ALTER TABLE `#__redshopb_product_tag_xref` DROP INDEX `#__rproduct_tx_fk2`;
  END IF;
END//

DELIMITER ;


-- -----------------------------------------------------
-- Table `#__redshopb_product_category_xref`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_product_category_xref_1_6_0`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_product_category_xref_1_6_0`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_product_category_xref' AND `constraint_name` = '#__rs_prod_cat_fk1') THEN
    ALTER TABLE `#__redshopb_product_category_xref` DROP FOREIGN KEY `#__rs_prod_cat_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_product_category_xref' AND `index_name` = '#__rs_prod_cat_fk1') THEN
    ALTER TABLE `#__redshopb_product_category_xref` DROP INDEX `#__rs_prod_cat_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_product_category_xref' AND `constraint_name` = '#__rs_prod_cat_fk2') THEN
    ALTER TABLE `#__redshopb_product_category_xref` DROP FOREIGN KEY `#__rs_prod_cat_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_product_category_xref' AND `index_name` = '#__rs_prod_cat_fk2') THEN
    ALTER TABLE `#__redshopb_product_category_xref` DROP INDEX `#__rs_prod_cat_fk2`;
  END IF;
END//

DELIMITER ;


-- -----------------------------------------------------
-- Table `#__redshopb_product_attribute`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_product_attribute_1_6_0`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_product_attribute_1_6_0`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_product_attribute' AND `constraint_name` = '#__rproduct_a_fk1') THEN
    ALTER TABLE `#__redshopb_product_attribute` DROP FOREIGN KEY `#__rproduct_a_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_product_attribute' AND `index_name` = '#__rproduct_a_fk1') THEN
    ALTER TABLE `#__redshopb_product_attribute` DROP INDEX `#__rproduct_a_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_product_attribute' AND `constraint_name` = '#__rproduct_a_fk2') THEN
    ALTER TABLE `#__redshopb_product_attribute` DROP FOREIGN KEY `#__rproduct_a_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_product_attribute' AND `index_name` = '#__rproduct_a_fk2') THEN
    ALTER TABLE `#__redshopb_product_attribute` DROP INDEX `#__rproduct_a_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_product_attribute' AND `constraint_name` = '#__rproduct_a_fk3') THEN
    ALTER TABLE `#__redshopb_product_attribute` DROP FOREIGN KEY `#__rproduct_a_fk3`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_product_attribute' AND `index_name` = '#__rproduct_a_fk3') THEN
    ALTER TABLE `#__redshopb_product_attribute` DROP INDEX `#__rproduct_a_fk3`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_product_attribute' AND `constraint_name` = '#__rproduct_a_fk4') THEN
    ALTER TABLE `#__redshopb_product_attribute` DROP FOREIGN KEY `#__rproduct_a_fk4`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_product_attribute' AND `index_name` = '#__rproduct_a_fk4') THEN
    ALTER TABLE `#__redshopb_product_attribute` DROP INDEX `#__rproduct_a_fk4`;
  END IF;
END//

DELIMITER ;


-- -----------------------------------------------------
-- Table `#__redshopb_product_attribute_value`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_product_attribute_value_1_6_0`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_product_attribute_value_1_6_0`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_product_attribute_value' AND `constraint_name` = '#__rproduct_av_fk1') THEN
    ALTER TABLE `#__redshopb_product_attribute_value` DROP FOREIGN KEY `#__rproduct_av_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_product_attribute_value' AND `index_name` = '#__rproduct_av_fk1') THEN
    ALTER TABLE `#__redshopb_product_attribute_value` DROP INDEX `#__rproduct_av_fk1`;
  END IF;
END//

DELIMITER ;


-- -----------------------------------------------------
-- Table `#__redshopb_product_descriptions`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_product_descriptions_1_6_0`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_product_descriptions_1_6_0`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_product_descriptions' AND `index_name` = 'idx_sku') THEN
    ALTER TABLE `#__redshopb_product_descriptions` DROP KEY `idx_sku`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_product_descriptions' AND `constraint_name` = '#__rproduct_d_fk2') THEN
    ALTER TABLE `#__redshopb_product_descriptions` DROP FOREIGN KEY `#__rproduct_d_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_product_descriptions' AND `index_name` = '#__rproduct_d_fk2') THEN
    ALTER TABLE `#__redshopb_product_descriptions` DROP INDEX `#__rproduct_d_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_product_descriptions' AND `constraint_name` = '#__rproduct_d_fk3') THEN
    ALTER TABLE `#__redshopb_product_descriptions` DROP FOREIGN KEY `#__rproduct_d_fk3`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_product_descriptions' AND `index_name` = '#__rproduct_d_fk3') THEN
    ALTER TABLE `#__redshopb_product_descriptions` DROP INDEX `#__rproduct_d_fk3`;
  END IF;
END//

DELIMITER ;


-- -----------------------------------------------------
-- Table `#__redshopb_product_item`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_product_item_1_6_0`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_product_item_1_6_0`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_product_item' AND `constraint_name` = '#__rproduct_i_fk1') THEN
    ALTER TABLE `#__redshopb_product_item` DROP FOREIGN KEY `#__rproduct_i_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_product_item' AND `index_name` = '#__rproduct_i_fk1') THEN
    ALTER TABLE `#__redshopb_product_item` DROP INDEX `#__rproduct_i_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_product_item' AND `index_name` = 'idx_amount') THEN
    ALTER TABLE `#__redshopb_product_item` DROP KEY `idx_amount`;
  END IF;
END//

DELIMITER ;


-- -----------------------------------------------------
-- Table `#__redshopb_product_item_attribute_value_xref`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_product_item_attribute_value_xref_1_6_0`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_product_item_attribute_value_xref_1_6_0`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_product_item_attribute_value_xref' AND `constraint_name` = '#__rproduct_iavx_fk1') THEN
    ALTER TABLE `#__redshopb_product_item_attribute_value_xref` DROP FOREIGN KEY `#__rproduct_iavx_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_product_item_attribute_value_xref' AND `index_name` = '#__rproduct_iavx_fk2') THEN
    ALTER TABLE `#__redshopb_product_item_attribute_value_xref` DROP INDEX `#__rproduct_iavx_fk2`;
  END IF;
END//

DELIMITER ;


-- -----------------------------------------------------
-- Table `#__redshopb_product_item_accessory`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_product_item_accessory_1_6_0`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_product_item_accessory_1_6_0`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_product_item_accessory' AND `constraint_name` = '#__rproduct_ia_fk1') THEN
    ALTER TABLE `#__redshopb_product_item_accessory` DROP FOREIGN KEY `#__rproduct_ia_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_product_item_accessory' AND `index_name` = '#__rproduct_ia_fk1') THEN
    ALTER TABLE `#__redshopb_product_item_accessory` DROP INDEX `#__rproduct_ia_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_product_item_accessory' AND `constraint_name` = '#__rproduct_ia_fk2') THEN
    ALTER TABLE `#__redshopb_product_item_accessory` DROP FOREIGN KEY `#__rproduct_ia_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_product_item_accessory' AND `index_name` = '#__rproduct_ia_fk2') THEN
    ALTER TABLE `#__redshopb_product_item_accessory` DROP INDEX `#__rproduct_ia_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_product_item_accessory' AND `constraint_name` = '#__rproduct_ia_fk3') THEN
    ALTER TABLE `#__redshopb_product_item_accessory` DROP FOREIGN KEY `#__rproduct_ia_fk3`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_product_item_accessory' AND `index_name` = '#__rproduct_ia_fk3') THEN
    ALTER TABLE `#__redshopb_product_item_accessory` DROP INDEX `#__rproduct_ia_fk3`;
  END IF;
END//

DELIMITER ;


-- -----------------------------------------------------
-- Table `#__redshopb_collection_product_xref`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_collection_product_xref_1_6_0`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_collection_product_xref_1_6_0`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_collection_product_xref' AND `constraint_name` = '#__rwardrobe_px_fk1') THEN
    ALTER TABLE `#__redshopb_collection_product_xref` DROP FOREIGN KEY `#__rwardrobe_px_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_collection_product_xref' AND `index_name` = '#__rwardrobe_px_fk1') THEN
    ALTER TABLE `#__redshopb_collection_product_xref` DROP INDEX `#__rwardrobe_px_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_collection_product_xref' AND `constraint_name` = '#__rwardrobe_px_fk2') THEN
    ALTER TABLE `#__redshopb_collection_product_xref` DROP FOREIGN KEY `#__rwardrobe_px_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_collection_product_xref' AND `index_name` = '#__rwardrobe_px_fk2') THEN
    ALTER TABLE `#__redshopb_collection_product_xref` DROP INDEX `#__rwardrobe_px_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_collection_product_xref' AND `index_name` = 'PRIMARY') THEN
    ALTER TABLE `#__redshopb_collection_product_xref` DROP PRIMARY KEY;
  END IF;
END//

DELIMITER ;


-- -----------------------------------------------------
-- Table `#__redshopb_collection_product_item_xref`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_collection_product_item_1_6_0`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_collection_product_item_1_6_0`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_collection_product_item_xref' AND `constraint_name` = '#__rwardrobe_pix_fk1') THEN
    ALTER TABLE `#__redshopb_collection_product_item_xref` DROP FOREIGN KEY `#__rwardrobe_pix_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_collection_product_item_xref' AND `index_name` = '#__rwardrobe_pix_fk1') THEN
    ALTER TABLE `#__redshopb_collection_product_item_xref` DROP INDEX `#__rwardrobe_pix_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_collection_product_item_xref' AND `constraint_name` = '#__rwardrobe_pix_fk2') THEN
    ALTER TABLE `#__redshopb_collection_product_item_xref` DROP FOREIGN KEY `#__rwardrobe_pix_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_collection_product_item_xref' AND `index_name` = '#__rwardrobe_pix_fk2') THEN
    ALTER TABLE `#__redshopb_collection_product_item_xref` DROP INDEX `#__rwardrobe_pix_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_collection_product_item_xref' AND `index_name` = 'PRIMARY') THEN
    ALTER TABLE `#__redshopb_collection_product_item_xref` DROP PRIMARY KEY;
  END IF;
END//

DELIMITER ;


-- -----------------------------------------------------
-- Table `#__redshopb_media`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_media_1_6_0`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_media_1_6_0`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_media' AND `constraint_name` = '#__rmedia_fk1') THEN
    ALTER TABLE `#__redshopb_media` DROP FOREIGN KEY `#__rmedia_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_media' AND `index_name` = '#__rmedia_fk1') THEN
    ALTER TABLE `#__redshopb_media` DROP INDEX `#__rmedia_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_media' AND `constraint_name` = '#__rmedia_fk2') THEN
    ALTER TABLE `#__redshopb_media` DROP FOREIGN KEY `#__rmedia_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_media' AND `index_name` = '#__rmedia_fk2') THEN
    ALTER TABLE `#__redshopb_media` DROP INDEX `#__rmedia_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_media' AND `constraint_name` = '#__rmedia_fk3') THEN
    ALTER TABLE `#__redshopb_media` DROP FOREIGN KEY `#__rmedia_fk3`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_media' AND `index_name` = '#__rmedia_fk3') THEN
    ALTER TABLE `#__redshopb_media` DROP INDEX `#__rmedia_fk3`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_media' AND `constraint_name` = '#__rmedia_fk4') THEN
    ALTER TABLE `#__redshopb_media` DROP FOREIGN KEY `#__rmedia_fk4`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_media' AND `index_name` = '#__rmedia_fk4') THEN
    ALTER TABLE `#__redshopb_media` DROP INDEX `#__rmedia_fk4`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_media' AND `index_name` = 'idx_product_id') THEN
    ALTER TABLE `#__redshopb_media` DROP INDEX `idx_product_id`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_media' AND `index_name` = 'idx_common') THEN
    ALTER TABLE `#__redshopb_media` DROP INDEX `idx_common`;
  END IF;
END//

DELIMITER ;


-- -----------------------------------------------------
-- Table `#__redshopb_category`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_category_1_6_0`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_category_1_6_0`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_category' AND `constraint_name` = 'idx_checkout') THEN
    ALTER TABLE `#__redshopb_category` DROP FOREIGN KEY `idx_checkout`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_category' AND `index_name` = 'idx_checkout') THEN
    ALTER TABLE `#__redshopb_category` DROP INDEX `idx_checkout`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_category' AND `constraint_name` = '#__rcategory_fk1') THEN
    ALTER TABLE `#__redshopb_category` DROP FOREIGN KEY `#__rcategory_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_category' AND `index_name` = '#__rcategory_fk1') THEN
    ALTER TABLE `#__redshopb_category` DROP INDEX `#__rcategory_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_category' AND `constraint_name` = '#__rcategory_fk2') THEN
    ALTER TABLE `#__redshopb_category` DROP FOREIGN KEY `#__rcategory_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_category' AND `index_name` = '#__rcategory_fk2') THEN
    ALTER TABLE `#__redshopb_category` DROP INDEX `#__rcategory_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_category' AND `constraint_name` = '#__rcategory_fk3') THEN
    ALTER TABLE `#__redshopb_category` DROP FOREIGN KEY `#__rcategory_fk3`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_category' AND `index_name` = '#__rcategory_fk3') THEN
    ALTER TABLE `#__redshopb_category` DROP INDEX `#__rcategory_fk3`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_category' AND `constraint_name` = '#__rcategory_fk4') THEN
    ALTER TABLE `#__redshopb_category` DROP FOREIGN KEY `#__rcategory_fk4`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_category' AND `index_name` = '#__rcategory_fk4') THEN
    ALTER TABLE `#__redshopb_category` DROP INDEX `#__rcategory_fk4`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_category' AND `constraint_name` = '#__rcategory_fk5') THEN
    ALTER TABLE `#__redshopb_category` DROP FOREIGN KEY `#__rcategory_fk5`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_category' AND `index_name` = '#__rcategory_fk5') THEN
    ALTER TABLE `#__redshopb_category` DROP INDEX `#__rcategory_fk5`;
  END IF;
END//

DELIMITER ;


-- -----------------------------------------------------
-- Table `#__redshopb_product_price`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_product_price_1_6_0`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_product_price_1_6_0`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_product_price' AND `constraint_name` = '#__rproduct_p_fk1') THEN
    ALTER TABLE `#__redshopb_product_price` DROP FOREIGN KEY `#__rproduct_p_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_product_price' AND `index_name` = '#__rproduct_p_fk1') THEN
    ALTER TABLE `#__redshopb_product_price` DROP INDEX `#__rproduct_p_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_product_price' AND `constraint_name` = '#__rproduct_p_fk2') THEN
    ALTER TABLE `#__redshopb_product_price` DROP FOREIGN KEY `#__rproduct_p_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_product_price' AND `index_name` = '#__rproduct_p_fk2') THEN
    ALTER TABLE `#__redshopb_product_price` DROP INDEX `#__rproduct_p_fk2`;
  END IF;
END//

DELIMITER ;


-- -----------------------------------------------------
-- Table `#__redshopb_customer_price_group`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_customer_price_group_1_6_0`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_customer_price_group_1_6_0`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_customer_price_group' AND `constraint_name` = '#__rcustomer_pg_fk1') THEN
    ALTER TABLE `#__redshopb_customer_price_group` DROP FOREIGN KEY `#__rcustomer_pg_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_customer_price_group' AND `index_name` = '#__rcustomer_pg_fk1') THEN
    ALTER TABLE `#__redshopb_customer_price_group` DROP INDEX `#__rcustomer_pg_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_customer_price_group' AND `constraint_name` = '#__rcustomer_pg_fk2') THEN
    ALTER TABLE `#__redshopb_customer_price_group` DROP FOREIGN KEY `#__rcustomer_pg_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_customer_price_group' AND `index_name` = '#__rcustomer_pg_fk2') THEN
    ALTER TABLE `#__redshopb_customer_price_group` DROP INDEX `#__rcustomer_pg_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_customer_price_group' AND `constraint_name` = '#__rcustomer_pg_fk3') THEN
    ALTER TABLE `#__redshopb_customer_price_group` DROP FOREIGN KEY `#__rcustomer_pg_fk3`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_customer_price_group' AND `index_name` = '#__rcustomer_pg_fk3') THEN
    ALTER TABLE `#__redshopb_customer_price_group` DROP INDEX `#__rcustomer_pg_fk3`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_customer_price_group' AND `constraint_name` = '#__rcustomer_pg_fk4') THEN
    ALTER TABLE `#__redshopb_customer_price_group` DROP FOREIGN KEY `#__rcustomer_pg_fk4`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_customer_price_group' AND `index_name` = '#__rcustomer_pg_fk4') THEN
    ALTER TABLE `#__redshopb_customer_price_group` DROP INDEX `#__rcustomer_pg_fk4`;
  END IF;
END//

DELIMITER ;


-- -----------------------------------------------------
-- Table `#__redshopb_customer_price_group_xref`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_customer_price_group_xref_1_6_0`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_customer_price_group_xref_1_6_0`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_customer_price_group_xref' AND `constraint_name` = '#__rcustomer_pgx_1') THEN
    ALTER TABLE `#__redshopb_customer_price_group_xref` DROP FOREIGN KEY `#__rcustomer_pgx_1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_customer_price_group_xref' AND `index_name` = '#__rcustomer_pgx_1') THEN
    ALTER TABLE `#__redshopb_customer_price_group_xref` DROP INDEX `#__rcustomer_pgx_1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_customer_price_group_xref' AND `constraint_name` = '#__rcustomer_pgx_2') THEN
    ALTER TABLE `#__redshopb_customer_price_group_xref` DROP FOREIGN KEY `#__rcustomer_pgx_2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_customer_price_group_xref' AND `index_name` = '#__rcustomer_pgx_2') THEN
    ALTER TABLE `#__redshopb_customer_price_group_xref` DROP INDEX `#__rcustomer_pgx_2`;
  END IF;
END//

DELIMITER ;



-- -----------------------------------------------------
-- Table `#__redshopb_product_discount`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_product_discount_1_6_0`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_product_discount_1_6_0`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_product_discount' AND `constraint_name` = '#__rproduct_d_fk1') THEN
    ALTER TABLE `#__redshopb_product_discount` DROP FOREIGN KEY `#__rproduct_d_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_product_discount' AND `index_name` = '#__rproduct_d_fk1') THEN
    ALTER TABLE `#__redshopb_product_discount` DROP INDEX `#__rproduct_d_fk1`;
  END IF;
END//

DELIMITER ;


-- -----------------------------------------------------
-- Table `#__redshopb_product_discount_group`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_product_discount_group_1_6_0`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_product_discount_group_1_6_0`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_product_discount_group' AND `constraint_name` = '#__rproduct_dg_fk1') THEN
    ALTER TABLE `#__redshopb_product_discount_group` DROP FOREIGN KEY `#__rproduct_dg_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_product_discount_group' AND `index_name` = '#__rproduct_dg_fk1') THEN
    ALTER TABLE `#__redshopb_product_discount_group` DROP INDEX `#__rproduct_dg_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_product_discount_group' AND `constraint_name` = '#__rproduct_dg_fk2') THEN
    ALTER TABLE `#__redshopb_product_discount_group` DROP FOREIGN KEY `#__rproduct_dg_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_product_discount_group' AND `index_name` = '#__rproduct_dg_fk2') THEN
    ALTER TABLE `#__redshopb_product_discount_group` DROP INDEX `#__rproduct_dg_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_product_discount_group' AND `constraint_name` = '#__rproduct_dg_fk3') THEN
    ALTER TABLE `#__redshopb_product_discount_group` DROP FOREIGN KEY `#__rproduct_dg_fk3`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_product_discount_group' AND `index_name` = '#__rproduct_dg_fk3') THEN
    ALTER TABLE `#__redshopb_product_discount_group` DROP INDEX `#__rproduct_dg_fk3`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_product_discount_group' AND `constraint_name` = '#__rproduct_dg_fk4') THEN
    ALTER TABLE `#__redshopb_product_discount_group` DROP FOREIGN KEY `#__rproduct_dg_fk4`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_product_discount_group' AND `index_name` = '#__rproduct_dg_fk4') THEN
    ALTER TABLE `#__redshopb_product_discount_group` DROP INDEX `#__rproduct_dg_fk4`;
  END IF;
END//

DELIMITER ;


-- -----------------------------------------------------
-- Table `#__redshopb_product_discount_group_xref`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_product_discount_group_xref_1_6_0`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_product_discount_group_xref_1_6_0`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_product_discount_group_xref' AND `constraint_name` = '#__rproduct_dgx_fk1') THEN
    ALTER TABLE `#__redshopb_product_discount_group_xref` DROP FOREIGN KEY `#__rproduct_dgx_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_product_discount_group_xref' AND `index_name` = '#__rproduct_dgx_fk1') THEN
    ALTER TABLE `#__redshopb_product_discount_group_xref` DROP INDEX `#__rproduct_dgx_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_product_discount_group_xref' AND `constraint_name` = '#__rproduct_dgx_fk2') THEN
    ALTER TABLE `#__redshopb_product_discount_group_xref` DROP FOREIGN KEY `#__rproduct_dgx_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_product_discount_group_xref' AND `index_name` = '#__rproduct_dgx_fk2') THEN
    ALTER TABLE `#__redshopb_product_discount_group_xref` DROP INDEX `#__rproduct_dgx_fk2`;
  END IF;
END//

DELIMITER ;


-- -----------------------------------------------------
-- Table `#__redshopb_customer_discount_group`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_customer_discount_group_1_6_0`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_customer_discount_group_1_6_0`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_customer_discount_group' AND `constraint_name` = '#__rcustomer_dg_fk1') THEN
    ALTER TABLE `#__redshopb_customer_discount_group` DROP FOREIGN KEY `#__rcustomer_dg_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_customer_discount_group' AND `index_name` = '#__rcustomer_dg_fk1') THEN
    ALTER TABLE `#__redshopb_customer_discount_group` DROP INDEX `#__rcustomer_dg_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_customer_discount_group' AND `constraint_name` = '#__rcustomer_dg_fk2') THEN
    ALTER TABLE `#__redshopb_customer_discount_group` DROP FOREIGN KEY `#__rcustomer_dg_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_customer_discount_group' AND `index_name` = '#__rcustomer_dg_fk2') THEN
    ALTER TABLE `#__redshopb_customer_discount_group` DROP INDEX `#__rcustomer_dg_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_customer_discount_group' AND `constraint_name` = '#__rcustomer_dg_fk3') THEN
    ALTER TABLE `#__redshopb_customer_discount_group` DROP FOREIGN KEY `#__rcustomer_dg_fk3`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_customer_discount_group' AND `index_name` = '#__rcustomer_dg_fk3') THEN
    ALTER TABLE `#__redshopb_customer_discount_group` DROP INDEX `#__rcustomer_dg_fk3`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_customer_discount_group' AND `constraint_name` = '#__rcustomer_dg_fk4') THEN
    ALTER TABLE `#__redshopb_customer_discount_group` DROP FOREIGN KEY `#__rcustomer_dg_fk4`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_customer_discount_group' AND `index_name` = '#__rcustomer_dg_fk4') THEN
    ALTER TABLE `#__redshopb_customer_discount_group` DROP INDEX `#__rcustomer_dg_fk4`;
  END IF;
END//

DELIMITER ;


-- -----------------------------------------------------
-- Table `#__redshopb_customer_discount_group_xref`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_customer_discount_group_xref_1_6_0`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_customer_discount_group_xref_1_6_0`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_customer_discount_group_xref' AND `constraint_name` = '#__rcustomer_dgx_fk_1') THEN
    ALTER TABLE `#__redshopb_customer_discount_group_xref` DROP FOREIGN KEY `#__rcustomer_dgx_fk_1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_customer_discount_group_xref' AND `index_name` = '#__rcustomer_dgx_fk_1') THEN
    ALTER TABLE `#__redshopb_customer_discount_group_xref` DROP INDEX `#__rcustomer_dgx_fk_1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_customer_discount_group_xref' AND `constraint_name` = '#__rcustomer_dgx_fk_2') THEN
    ALTER TABLE `#__redshopb_customer_discount_group_xref` DROP FOREIGN KEY `#__rcustomer_dgx_fk_2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_customer_discount_group_xref' AND `index_name` = '#__rcustomer_dgx_fk_2') THEN
    ALTER TABLE `#__redshopb_customer_discount_group_xref` DROP INDEX `#__rcustomer_dgx_fk_2`;
  END IF;
END//

DELIMITER ;


-- Move product item amounts into stockroom (when stock is present)
DROP PROCEDURE IF EXISTS `#__redshopb_stockroom_1_6_0`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_stockroom_1_6_0`() BEGIN
  IF EXISTS (SELECT * FROM `#__redshopb_product_item` WHERE `amount` > 0) THEN
    INSERT INTO `#__redshopb_stockroom` (`id`, `name`, `alias`)
      VALUES (1, 'Default Stockroom', 'default-stockroom');

    INSERT INTO `#__redshopb_stockroom_product_item_xref` (`stockroom_id`, `product_item_id`, `amount`)
      SELECT 1, `id`, `amount` FROM `#__redshopb_product_item`
       WHERE `amount` > 0;
  END IF;
END//

DELIMITER ;


-- -----------------------------------------------------
-- Table `#__redshopb_address`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_address_1_6_0`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_address_1_6_0`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_address' AND `constraint_name` = '#__raddress_fk1') THEN
    ALTER TABLE `#__redshopb_address` DROP FOREIGN KEY `#__raddress_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_address' AND `index_name` = '#__raddress_fk1') THEN
    ALTER TABLE `#__redshopb_address` DROP INDEX `#__raddress_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_address' AND `index_name` = 'idx_country_id') THEN
    ALTER TABLE `#__redshopb_address` DROP INDEX `idx_country_id`;
  END IF;
END//

DELIMITER ;


-- -----------------------------------------------------
-- Table `#__redshopb_company`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_company_1_6_0`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_company_1_6_0`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_company' AND `constraint_name` = '#__rcompany_fk1') THEN
    ALTER TABLE `#__redshopb_company` DROP FOREIGN KEY `#__rcompany_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_company' AND `index_name` = '#__rcompany_fk1') THEN
    ALTER TABLE `#__redshopb_company` DROP INDEX `#__rcompany_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_company' AND `constraint_name` = '#__rcompany_fk2') THEN
    ALTER TABLE `#__redshopb_company` DROP FOREIGN KEY `#__rcompany_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_company' AND `index_name` = '#__rcompany_fk2') THEN
    ALTER TABLE `#__redshopb_company` DROP INDEX `#__rcompany_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_company' AND `constraint_name` = '#__rcompany_fk3') THEN
    ALTER TABLE `#__redshopb_company` DROP FOREIGN KEY `#__rcompany_fk3`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_company' AND `index_name` = '#__rcompany_fk3') THEN
    ALTER TABLE `#__redshopb_company` DROP INDEX `#__rcompany_fk3`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_company' AND `constraint_name` = '#__rcompany_fk4') THEN
    ALTER TABLE `#__redshopb_company` DROP FOREIGN KEY `#__rcompany_fk4`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_company' AND `index_name` = '#__rcompany_fk4') THEN
    ALTER TABLE `#__redshopb_company` DROP INDEX `#__rcompany_fk4`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_company' AND `constraint_name` = '#__rcompany_fk5') THEN
    ALTER TABLE `#__redshopb_company` DROP FOREIGN KEY `#__rcompany_fk5`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_company' AND `index_name` = '#__rcompany_fk5') THEN
    ALTER TABLE `#__redshopb_company` DROP INDEX `#__rcompany_fk5`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_company' AND `constraint_name` = '#__rcompany_fk6') THEN
    ALTER TABLE `#__redshopb_company` DROP FOREIGN KEY `#__rcompany_fk6`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_company' AND `index_name` = '#__rcompany_fk6') THEN
    ALTER TABLE `#__redshopb_company` DROP INDEX `#__rcompany_fk6`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_company' AND `constraint_name` = '#__rcompany_fk7') THEN
    ALTER TABLE `#__redshopb_company` DROP FOREIGN KEY `#__rcompany_fk7`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_company' AND `index_name` = '#__rcompany_fk7') THEN
    ALTER TABLE `#__redshopb_company` DROP INDEX `#__rcompany_fk7`;
  END IF;
END//

DELIMITER ;


-- -----------------------------------------------------
-- Table `#__redshopb_collection`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_collection_1_6_0`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_collection_1_6_0`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_collection' AND `constraint_name` = '#__rwardrobe_fk1') THEN
    ALTER TABLE `#__redshopb_collection` DROP FOREIGN KEY `#__rwardrobe_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_collection' AND `index_name` = '#__rwardrobe_fk1') THEN
    ALTER TABLE `#__redshopb_collection` DROP INDEX `#__rwardrobe_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_collection' AND `constraint_name` = '#__rwardrobe_fk2') THEN
    ALTER TABLE `#__redshopb_collection` DROP FOREIGN KEY `#__rwardrobe_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_collection' AND `index_name` = '#__rwardrobe_fk2') THEN
    ALTER TABLE `#__redshopb_collection` DROP INDEX `#__rwardrobe_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_collection' AND `constraint_name` = '#__rwardrobe_fk3') THEN
    ALTER TABLE `#__redshopb_collection` DROP FOREIGN KEY `#__rwardrobe_fk3`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_collection' AND `index_name` = '#__rwardrobe_fk3') THEN
    ALTER TABLE `#__redshopb_collection` DROP INDEX `#__rwardrobe_fk3`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_collection' AND `constraint_name` = '#__rwardrobe_fk4') THEN
    ALTER TABLE `#__redshopb_collection` DROP FOREIGN KEY `#__rwardrobe_fk4`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_collection' AND `index_name` = '#__rwardrobe_fk4') THEN
    ALTER TABLE `#__redshopb_collection` DROP INDEX `#__rwardrobe_fk4`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_collection' AND `constraint_name` = '#__rwardrobe_fk5') THEN
    ALTER TABLE `#__redshopb_collection` DROP FOREIGN KEY `#__rwardrobe_fk5`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_collection' AND `index_name` = '#__rwardrobe_fk5') THEN
    ALTER TABLE `#__redshopb_collection` DROP INDEX `#__rwardrobe_fk5`;
  END IF;
END//

DELIMITER ;


-- -----------------------------------------------------
-- Table `#__redshopb_collection_department_xref`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_collection_department_xref_1_6_0`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_collection_department_xref_1_6_0`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_collection_department_xref' AND `constraint_name` = '#__rwardrobe_dx_fk1') THEN
    ALTER TABLE `#__redshopb_collection_department_xref` DROP FOREIGN KEY `#__rwardrobe_dx_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_collection_department_xref' AND `index_name` = '#__rwardrobe_dx_fk1') THEN
    ALTER TABLE `#__redshopb_collection_department_xref` DROP INDEX `#__rwardrobe_dx_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_collection_department_xref' AND `constraint_name` = '#__rwardrobe_dx_fk2') THEN
    ALTER TABLE `#__redshopb_collection_department_xref` DROP FOREIGN KEY `#__rwardrobe_dx_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_collection_department_xref' AND `index_name` = '#__rwardrobe_dx_fk2') THEN
    ALTER TABLE `#__redshopb_collection_department_xref` DROP INDEX `#__rwardrobe_dx_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_collection_department_xref' AND `index_name` = 'PRIMARY') THEN
    ALTER TABLE `#__redshopb_collection_department_xref` DROP PRIMARY KEY;
  END IF;
END//

DELIMITER ;


-- -----------------------------------------------------
-- Table `#__redshopb_user`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_user_1_6_0`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_user_1_6_0`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_user' AND `constraint_name` = '#__ruser_fk1') THEN
    ALTER TABLE `#__redshopb_user` DROP FOREIGN KEY `#__ruser_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_user' AND `index_name` = '#__ruser_fk1') THEN
    ALTER TABLE `#__redshopb_user` DROP INDEX `#__ruser_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_user' AND `constraint_name` = '#__ruser_fk2') THEN
    ALTER TABLE `#__redshopb_user` DROP FOREIGN KEY `#__ruser_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_user' AND `index_name` = '#__ruser_fk2') THEN
    ALTER TABLE `#__redshopb_user` DROP INDEX `#__ruser_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_user' AND `constraint_name` = '#__ruser_fk3') THEN
    ALTER TABLE `#__redshopb_user` DROP FOREIGN KEY `#__ruser_fk3`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_user' AND `index_name` = '#__ruser_fk3') THEN
    ALTER TABLE `#__redshopb_user` DROP INDEX `#__ruser_fk3`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_user' AND `constraint_name` = '#__ruser_fk4') THEN
    ALTER TABLE `#__redshopb_user` DROP FOREIGN KEY `#__ruser_fk4`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_user' AND `index_name` = '#__ruser_fk4') THEN
    ALTER TABLE `#__redshopb_user` DROP INDEX `#__ruser_fk4`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_user' AND `constraint_name` = '#__ruser_fk5') THEN
    ALTER TABLE `#__redshopb_user` DROP FOREIGN KEY `#__ruser_fk5`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_user' AND `index_name` = '#__ruser_fk5') THEN
    ALTER TABLE `#__redshopb_user` DROP INDEX `#__ruser_fk5`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_user' AND `constraint_name` = '#__ruser_fk6') THEN
    ALTER TABLE `#__redshopb_user` DROP FOREIGN KEY `#__ruser_fk6`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_user' AND `index_name` = '#__ruser_fk6') THEN
    ALTER TABLE `#__redshopb_user` DROP INDEX `#__ruser_fk6`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_user' AND `constraint_name` = '#__ruser_fk7') THEN
    ALTER TABLE `#__redshopb_user` DROP FOREIGN KEY `#__ruser_fk7`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_user' AND `index_name` = '#__ruser_fk7') THEN
    ALTER TABLE `#__redshopb_user` DROP INDEX `#__ruser_fk7`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_user' AND `constraint_name` = '#__ruser_fk8') THEN
    ALTER TABLE `#__redshopb_user` DROP FOREIGN KEY `#__ruser_fk8`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_user' AND `index_name` = '#__ruser_fk8') THEN
    ALTER TABLE `#__redshopb_user` DROP INDEX `#__ruser_fk8`;
  END IF;
END//

DELIMITER ;


-- -----------------------------------------------------
-- Table `#__redshopb_wallet`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_wallet_1_6_0`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_wallet_1_6_0`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_wallet' AND `constraint_name` = '#__rwallet_fk1') THEN
    ALTER TABLE `#__redshopb_wallet` DROP FOREIGN KEY `#__rwallet_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_wallet' AND `index_name` = '#__rwallet_fk1') THEN
    ALTER TABLE `#__redshopb_wallet` DROP INDEX `#__rwallet_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_wallet' AND `constraint_name` = '#__rwallet_fk2') THEN
    ALTER TABLE `#__redshopb_wallet` DROP FOREIGN KEY `#__rwallet_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_wallet' AND `index_name` = '#__rwallet_fk2') THEN
    ALTER TABLE `#__redshopb_wallet` DROP INDEX `#__rwallet_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_wallet' AND `constraint_name` = '#__rwallet_fk3') THEN
    ALTER TABLE `#__redshopb_wallet` DROP FOREIGN KEY `#__rwallet_fk3`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_wallet' AND `index_name` = '#__rwallet_fk3') THEN
    ALTER TABLE `#__redshopb_wallet` DROP INDEX `#__rwallet_fk3`;
  END IF;
END//

DELIMITER ;


-- -----------------------------------------------------
-- Table `#__redshopb_wallet_money`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_wallet_money_1_6_0`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_wallet_money_1_6_0`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_wallet_money' AND `constraint_name` = '#__rwallet_m_fk1') THEN
    ALTER TABLE `#__redshopb_wallet_money` DROP FOREIGN KEY `#__rwallet_m_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_wallet_money' AND `index_name` = '#__rwallet_m_fk1') THEN
    ALTER TABLE `#__redshopb_wallet_money` DROP INDEX `#__rwallet_m_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_wallet_money' AND `constraint_name` = '#__rwallet_m_fk2') THEN
    ALTER TABLE `#__redshopb_wallet_money` DROP FOREIGN KEY `#__rwallet_m_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_wallet_money' AND `index_name` = '#__rwallet_m_fk2') THEN
    ALTER TABLE `#__redshopb_wallet_money` DROP INDEX `#__rwallet_m_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_wallet_money' AND `constraint_name` = '#__rwallet_m_fk3') THEN
    ALTER TABLE `#__redshopb_wallet_money` DROP FOREIGN KEY `#__rwallet_m_fk3`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_wallet_money' AND `index_name` = '#__rwallet_m_fk3') THEN
    ALTER TABLE `#__redshopb_wallet_money` DROP INDEX `#__rwallet_m_fk3`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_wallet_money' AND `constraint_name` = '#__rwallet_m_fk4') THEN
    ALTER TABLE `#__redshopb_wallet_money` DROP FOREIGN KEY `#__rwallet_m_fk4`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_wallet_money' AND `index_name` = '#__rwallet_m_fk4') THEN
    ALTER TABLE `#__redshopb_wallet_money` DROP INDEX `#__rwallet_m_fk4`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_wallet_money' AND `constraint_name` = '#__rwallet_m_fk5') THEN
    ALTER TABLE `#__redshopb_wallet_money` DROP FOREIGN KEY `#__rwallet_m_fk5`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_wallet_money' AND `index_name` = '#__rwallet_m_fk5') THEN
    ALTER TABLE `#__redshopb_wallet_money` DROP INDEX `#__rwallet_m_fk5`;
  END IF;
END//

DELIMITER ;


-- -----------------------------------------------------
-- Table `#__redshopb_company_sales_person_xref`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_company_sales_person_xref_1_6_0`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_company_sales_person_xref_1_6_0`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_company_sales_person_xref' AND `constraint_name` = '#__rcompany_spx_fk1') THEN
    ALTER TABLE `#__redshopb_company_sales_person_xref` DROP FOREIGN KEY `#__rcompany_spx_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_company_sales_person_xref' AND `index_name` = '#__rcompany_spx_fk1') THEN
    ALTER TABLE `#__redshopb_company_sales_person_xref` DROP INDEX `#__rcompany_spx_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_company_sales_person_xref' AND `constraint_name` = '#__rcompany_spx_fk2') THEN
    ALTER TABLE `#__redshopb_company_sales_person_xref` DROP FOREIGN KEY `#__rcompany_spx_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_company_sales_person_xref' AND `index_name` = '#__rcompany_spx_fk2') THEN
    ALTER TABLE `#__redshopb_company_sales_person_xref` DROP INDEX `#__rcompany_spx_fk2`;
  END IF;
END//

DELIMITER ;


-- -----------------------------------------------------
-- Table `#__redshopb_usergroup_sales_person_xref`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_usergroup_sales_person_xref_1_6_0`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_usergroup_sales_person_xref_1_6_0`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_usergroup_sales_person_xref' AND `constraint_name` = '#__rusergroup_spx_fk1') THEN
    ALTER TABLE `#__redshopb_usergroup_sales_person_xref` DROP FOREIGN KEY `#__rusergroup_spx_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_usergroup_sales_person_xref' AND `index_name` = '#__rusergroup_spx_fk1') THEN
    ALTER TABLE `#__redshopb_usergroup_sales_person_xref` DROP INDEX `#__rusergroup_spx_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_usergroup_sales_person_xref' AND `constraint_name` = '#__rusergroup_spx_fk2') THEN
    ALTER TABLE `#__redshopb_usergroup_sales_person_xref` DROP FOREIGN KEY `#__rusergroup_spx_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_usergroup_sales_person_xref' AND `index_name` = '#__rusergroup_spx_fk2') THEN
    ALTER TABLE `#__redshopb_usergroup_sales_person_xref` DROP INDEX `#__rusergroup_spx_fk2`;
  END IF;
END//

DELIMITER ;


-- -----------------------------------------------------
-- Table `#__redshopb_role`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_role_1_6_0`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_role_1_6_0`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_role' AND `constraint_name` = '#__rrole_fk1') THEN
    ALTER TABLE `#__redshopb_role` DROP FOREIGN KEY `#__rrole_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_role' AND `index_name` = '#__rrole_fk1') THEN
    ALTER TABLE `#__redshopb_role` DROP INDEX `#__rrole_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_role' AND `constraint_name` = '#__rrole_fk2') THEN
    ALTER TABLE `#__redshopb_role` DROP FOREIGN KEY `#__rrole_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_role' AND `index_name` = '#__rrole_fk2') THEN
    ALTER TABLE `#__redshopb_role` DROP INDEX `#__rrole_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_role' AND `constraint_name` = '#__rrole_fk3') THEN
    ALTER TABLE `#__redshopb_role` DROP FOREIGN KEY `#__rrole_fk3`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_role' AND `index_name` = '#__rrole_fk3') THEN
    ALTER TABLE `#__redshopb_role` DROP INDEX `#__rrole_fk3`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_role' AND `constraint_name` = '#__rrole_fk4') THEN
    ALTER TABLE `#__redshopb_role` DROP FOREIGN KEY `#__rrole_fk4`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_role' AND `index_name` = '#__rrole_fk4') THEN
    ALTER TABLE `#__redshopb_role` DROP INDEX `#__rrole_fk4`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_role' AND `constraint_name` = '#__rrole_fk5') THEN
    ALTER TABLE `#__redshopb_role` DROP FOREIGN KEY `#__rrole_fk5`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_role' AND `index_name` = '#__rrole_fk5') THEN
    ALTER TABLE `#__redshopb_role` DROP INDEX `#__rrole_fk5`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_role' AND `constraint_name` = '#__rrole_fk6') THEN
    ALTER TABLE `#__redshopb_role` DROP FOREIGN KEY `#__rrole_fk6`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_role' AND `index_name` = '#__rrole_fk6') THEN
    ALTER TABLE `#__redshopb_role` DROP INDEX `#__rrole_fk6`;
  END IF;
END//

DELIMITER ;


-- -----------------------------------------------------
-- Table `#__redshopb_layout`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_layout_1_6_0`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_layout_1_6_0`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_layout' AND `constraint_name` = '#__rlayout_fk1') THEN
    ALTER TABLE `#__redshopb_layout` DROP FOREIGN KEY `#__rlayout_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_layout' AND `index_name` = '#__rlayout_fk1') THEN
    ALTER TABLE `#__redshopb_layout` DROP INDEX `#__rlayout_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_layout' AND `constraint_name` = '#__rlayout_fk2') THEN
    ALTER TABLE `#__redshopb_layout` DROP FOREIGN KEY `#__rlayout_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_layout' AND `index_name` = '#__rlayout_fk2') THEN
    ALTER TABLE `#__redshopb_layout` DROP INDEX `#__rlayout_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_layout' AND `constraint_name` = '#__rlayout_fk3') THEN
    ALTER TABLE `#__redshopb_layout` DROP FOREIGN KEY `#__rlayout_fk3`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_layout' AND `index_name` = '#__rlayout_fk3') THEN
    ALTER TABLE `#__redshopb_layout` DROP INDEX `#__rlayout_fk3`;
  END IF;
END//

DELIMITER ;


-- -----------------------------------------------------
-- Table `#__redshopb_logos`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_logos_1_6_0`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_logos_1_6_0`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_logos' AND `constraint_name` = '#__rlogos_fk1') THEN
    ALTER TABLE `#__redshopb_logos` DROP FOREIGN KEY `#__rlogos_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_logos' AND `index_name` = '#__rlogos_fk1') THEN
    ALTER TABLE `#__redshopb_logos` DROP INDEX `#__rlogos_fk1`;
  END IF;
END//

DELIMITER ;


-- -----------------------------------------------------
-- Table `#__redshopb_fee`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_fee_1_6_0`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_fee_1_6_0`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_fee' AND `constraint_name` = '#__rfee_fk1') THEN
    ALTER TABLE `#__redshopb_fee` DROP FOREIGN KEY `#__rfee_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_fee' AND `index_name` = '#__rfee_fk1') THEN
    ALTER TABLE `#__redshopb_fee` DROP INDEX `#__rfee_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_fee' AND `constraint_name` = '#__rfee_fk2') THEN
    ALTER TABLE `#__redshopb_fee` DROP FOREIGN KEY `#__rfee_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_fee' AND `index_name` = '#__rfee_fk2') THEN
    ALTER TABLE `#__redshopb_fee` DROP INDEX `#__rfee_fk2`;
  END IF;
END//

DELIMITER ;


-- -----------------------------------------------------
-- Table `#__redshopb_order`
-- -----------------------------------------------------

DROP PROCEDURE IF EXISTS `#__redshopb_order_1_6_0`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_order_1_6_0`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_order' AND `constraint_name` = '#__rorder_fk1') THEN
    ALTER TABLE `#__redshopb_order` DROP FOREIGN KEY `#__rorder_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_order' AND `index_name` = '#__rorder_fk1') THEN
    ALTER TABLE `#__redshopb_order` DROP INDEX `#__rorder_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_order' AND `constraint_name` = '#__rorder_fk2') THEN
    ALTER TABLE `#__redshopb_order` DROP FOREIGN KEY `#__rorder_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_order' AND `index_name` = '#__rorder_fk2') THEN
    ALTER TABLE `#__redshopb_order` DROP INDEX `#__rorder_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_order' AND `constraint_name` = '#__rorder_fk3') THEN
    ALTER TABLE `#__redshopb_order` DROP FOREIGN KEY `#__rorder_fk3`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_order' AND `index_name` = '#__rorder_fk3') THEN
    ALTER TABLE `#__redshopb_order` DROP INDEX `#__rorder_fk3`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_order' AND `constraint_name` = '#__rorder_fk4') THEN
    ALTER TABLE `#__redshopb_order` DROP FOREIGN KEY `#__rorder_fk4`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_order' AND `index_name` = '#__rorder_fk4') THEN
    ALTER TABLE `#__redshopb_order` DROP INDEX `#__rorder_fk4`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_order' AND `constraint_name` = '#__rorder_fk5') THEN
    ALTER TABLE `#__redshopb_order` DROP FOREIGN KEY `#__rorder_fk5`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_order' AND `index_name` = '#__rorder_fk5') THEN
    ALTER TABLE `#__redshopb_order` DROP INDEX `#__rorder_fk5`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_order' AND `index_name` = 'idx_delivery_address_id') THEN
    ALTER TABLE `#__redshopb_order` DROP INDEX `idx_delivery_address_id`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_order' AND `index_name` = 'idx_currency') THEN
    ALTER TABLE `#__redshopb_order` DROP INDEX `idx_currency`;
  END IF;
END//

DELIMITER ;


-- -----------------------------------------------------
-- Table `#__redshopb_order_logs`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_order_logs_1_6_0`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_order_logs_1_6_0`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_order_logs' AND `constraint_name` = '#__rorder_l_fk1') THEN
    ALTER TABLE `#__redshopb_order_logs` DROP FOREIGN KEY `#__rorder_l_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_order_logs' AND `index_name` = '#__rorder_l_fk1') THEN
    ALTER TABLE `#__redshopb_order_logs` DROP INDEX `#__rorder_l_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_order_logs' AND `constraint_name` = '#__rorder_l_fk2') THEN
    ALTER TABLE `#__redshopb_order_logs` DROP FOREIGN KEY `#__rorder_l_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_order_logs' AND `index_name` = '#__rorder_l_fk2') THEN
    ALTER TABLE `#__redshopb_order_logs` DROP INDEX `#__rorder_l_fk2`;
  END IF;
END//

DELIMITER ;


-- -----------------------------------------------------
-- Table `#__redshopb_order_item`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_order_item_1_6_0`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_order_item_1_6_0`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_order_item' AND `constraint_name` = '#__rorder_i_fk1') THEN
    ALTER TABLE `#__redshopb_order_item` DROP FOREIGN KEY `#__rorder_i_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_order_item' AND `index_name` = '#__rorder_i_fk1') THEN
    ALTER TABLE `#__redshopb_order_item` DROP INDEX `#__rorder_i_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_order_item' AND `constraint_name` = '#__rorder_i_fk2') THEN
    ALTER TABLE `#__redshopb_order_item` DROP FOREIGN KEY `#__rorder_i_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_order_item' AND `index_name` = '#__rorder_i_fk2') THEN
    ALTER TABLE `#__redshopb_order_item` DROP INDEX `#__rorder_i_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_order_item' AND `constraint_name` = '#__rorder_i_fk3') THEN
    ALTER TABLE `#__redshopb_order_item` DROP FOREIGN KEY `#__rorder_i_fk3`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_order_item' AND `index_name` = '#__rorder_i_fk3') THEN
    ALTER TABLE `#__redshopb_order_item` DROP INDEX `#__rorder_i_fk3`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_order_item' AND `index_name` = 'idx_product_item_id') THEN
    ALTER TABLE `#__redshopb_order_item` DROP INDEX `idx_product_item_id`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_order_item' AND `index_name` = 'idx_product_id') THEN
    ALTER TABLE `#__redshopb_order_item` DROP INDEX `idx_product_id`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_order_item' AND `index_name` = 'idx_currency_id') THEN
    ALTER TABLE `#__redshopb_order_item` DROP INDEX `idx_currency_id`;
  END IF;
END//

DELIMITER ;


-- -----------------------------------------------------
-- Table `#__redshopb_order_item_attribute`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_order_item_attribute_1_6_0`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_order_item_attribute_1_6_0`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_order_item_attribute' AND `constraint_name` = '#__rorder_ia_fk1') THEN
    ALTER TABLE `#__redshopb_order_item_attribute` DROP FOREIGN KEY `#__rorder_ia_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_order_item_attribute' AND `index_name` = '#__rorder_ia_fk1') THEN
    ALTER TABLE `#__redshopb_order_item_attribute` DROP INDEX `#__rorder_ia_fk1`;
  END IF;
END//

DELIMITER ;


-- -----------------------------------------------------
-- Table `#__redshopb_product_wash_care_spec_xref`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_product_wash_care_spec_xref_1_6_0`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_product_wash_care_spec_xref_1_6_0`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_product_wash_care_spec_xref' AND `constraint_name` = '#__rproduct_wcsx_fk1') THEN
    ALTER TABLE `#__redshopb_product_wash_care_spec_xref` DROP FOREIGN KEY `#__rproduct_wcsx_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_product_wash_care_spec_xref' AND `index_name` = '#__rproduct_wcsx_fk1') THEN
    ALTER TABLE `#__redshopb_product_wash_care_spec_xref` DROP INDEX `#__rproduct_wcsx_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_product_wash_care_spec_xref' AND `constraint_name` = '#__rproduct_wcsx_fk2') THEN
    ALTER TABLE `#__redshopb_product_wash_care_spec_xref` DROP FOREIGN KEY `#__rproduct_wcsx_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_product_wash_care_spec_xref' AND `index_name` = '#__rproduct_wcsx_fk2') THEN
    ALTER TABLE `#__redshopb_product_wash_care_spec_xref` DROP INDEX `#__rproduct_wcsx_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_product_wash_care_spec_xref' AND `index_name` = 'idx_product_id') THEN
    ALTER TABLE `#__redshopb_product_wash_care_spec_xref` DROP INDEX `idx_product_id`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_product_wash_care_spec_xref' AND `index_name` = 'idx_category_id') THEN
    ALTER TABLE `#__redshopb_product_wash_care_spec_xref` DROP INDEX `idx_category_id`;
  END IF;
END//

DELIMITER ;


-- -----------------------------------------------------
-- Table `#__redshopb_product_composition`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_product_composition_1_6_0`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_product_composition_1_6_0`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_product_composition' AND `constraint_name` = '#__redshopb_product_composition_ibfk_1') THEN
    ALTER TABLE `#__redshopb_product_composition` DROP FOREIGN KEY `#__redshopb_product_composition_ibfk_1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_product_composition' AND `index_name` = '#__redshopb_product_composition_ibfk_1') THEN
    ALTER TABLE `#__redshopb_product_composition` DROP INDEX `#__redshopb_product_composition_ibfk_1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_product_composition' AND `constraint_name` = '#__redshopb_product_composition_ibfk_2') THEN
    ALTER TABLE `#__redshopb_product_composition` DROP FOREIGN KEY `#__redshopb_product_composition_ibfk_2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_product_composition' AND `index_name` = '#__redshopb_product_composition_ibfk_2') THEN
    ALTER TABLE `#__redshopb_product_composition` DROP INDEX `#__redshopb_product_composition_ibfk_2`;
  END IF;
END//

DELIMITER ;


-- -----------------------------------------------------
-- Table `#__redshopb_department`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_department_1_6_0`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_department_1_6_0`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_department' AND `constraint_name` = '#__rdepartment_fk1') THEN
    ALTER TABLE `#__redshopb_department` DROP FOREIGN KEY `#__rdepartment_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_department' AND `index_name` = '#__rdepartment_fk1') THEN
    ALTER TABLE `#__redshopb_department` DROP INDEX `#__rdepartment_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_department' AND `constraint_name` = '#__rdepartment_fk2') THEN
    ALTER TABLE `#__redshopb_department` DROP FOREIGN KEY `#__rdepartment_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_department' AND `index_name` = '#__rdepartment_fk2') THEN
    ALTER TABLE `#__redshopb_department` DROP INDEX `#__rdepartment_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_department' AND `constraint_name` = '#__rdepartment_fk3') THEN
    ALTER TABLE `#__redshopb_department` DROP FOREIGN KEY `#__rdepartment_fk3`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_department' AND `index_name` = '#__rdepartment_fk3') THEN
    ALTER TABLE `#__redshopb_department` DROP INDEX `#__rdepartment_fk3`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_department' AND `constraint_name` = '#__rdepartment_fk4') THEN
    ALTER TABLE `#__redshopb_department` DROP FOREIGN KEY `#__rdepartment_fk4`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_department' AND `index_name` = '#__rdepartment_fk4') THEN
    ALTER TABLE `#__redshopb_department` DROP INDEX `#__rdepartment_fk4`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_department' AND `constraint_name` = '#__rdepartment_fk5') THEN
    ALTER TABLE `#__redshopb_department` DROP FOREIGN KEY `#__rdepartment_fk5`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_department' AND `index_name` = '#__rdepartment_fk5') THEN
    ALTER TABLE `#__redshopb_department` DROP INDEX `#__rdepartment_fk5`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_department' AND `constraint_name` = '#__rdepartment_fk6') THEN
    ALTER TABLE `#__redshopb_department` DROP FOREIGN KEY `#__rdepartment_fk6`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_department' AND `index_name` = '#__rdepartment_fk6') THEN
    ALTER TABLE `#__redshopb_department` DROP INDEX `#__rdepartment_fk6`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_department' AND `constraint_name` = '#__rdeparmtnet_fk7') THEN
    ALTER TABLE `#__redshopb_department` DROP FOREIGN KEY `#__rdeparmtnet_fk7`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_department' AND `index_name` = '#__rdeparmtnet_fk7') THEN
    ALTER TABLE `#__redshopb_department` DROP INDEX `#__rdeparmtnet_fk7`;
  END IF;
END//

DELIMITER ;
