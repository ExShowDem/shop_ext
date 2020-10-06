-- -----------------------------------------------------
-- Table `#__redshopb_acl_access`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_acl_access_1_5_0`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_acl_access_1_5_0`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_acl_access' AND `constraint_name` = '#__redshopb_acl_access_fk1') THEN
    ALTER TABLE `#__redshopb_acl_access` DROP FOREIGN KEY `#__redshopb_acl_access_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_acl_access' AND `index_name` = '#__redshopb_acl_access_fk1') THEN
    ALTER TABLE `#__redshopb_acl_access` DROP INDEX `#__redshopb_acl_access_fk1`;
  END IF;
END//

DELIMITER ;


-- -----------------------------------------------------
-- Table `#__redshopb_acl_rule`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_acl_rule_1_5_0`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_acl_rule_1_5_0`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_acl_rule' AND `constraint_name` = '#__redshopb_acl_access_fk1') THEN
    ALTER TABLE `#__redshopb_acl_rule` DROP FOREIGN KEY `#__redshopb_acl_access_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_acl_rule' AND `index_name` = '#__redshopb_acl_access_fk1') THEN
    ALTER TABLE `#__redshopb_acl_rule` DROP INDEX `#__redshopb_acl_access_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_acl_rule' AND `constraint_name` = '#__redshopb_acl_rule_fk1') THEN
    ALTER TABLE `#__redshopb_acl_rule` DROP FOREIGN KEY `#__redshopb_acl_rule_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_acl_rule' AND `index_name` = '#__redshopb_acl_rule_fk1') THEN
    ALTER TABLE `#__redshopb_acl_rule` DROP INDEX `#__redshopb_acl_rule_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_acl_rule' AND `constraint_name` = '#__redshopb_acl_rule_fk2') THEN
    ALTER TABLE `#__redshopb_acl_rule` DROP FOREIGN KEY `#__redshopb_acl_rule_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_acl_rule' AND `index_name` = '#__redshopb_acl_rule_fk2') THEN
    ALTER TABLE `#__redshopb_acl_rule` DROP INDEX `#__redshopb_acl_rule_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_acl_rule' AND `constraint_name` = '#__redshopb_acl_rule_fk3') THEN
    ALTER TABLE `#__redshopb_acl_rule` DROP FOREIGN KEY `#__redshopb_acl_rule_fk3`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_acl_rule' AND `index_name` = '#__redshopb_acl_rule_fk3') THEN
    ALTER TABLE `#__redshopb_acl_rule` DROP INDEX `#__redshopb_acl_rule_fk3`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_acl_rule' AND `index_name` = '#__idx_redshopb_acl_rule_effective') THEN
    ALTER TABLE `#__redshopb_acl_rule` DROP INDEX `#__idx_redshopb_acl_rule_effective`;
  END IF;
END//

DELIMITER ;


-- -----------------------------------------------------
-- Table `#__redshopb_address`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_address_1_5_0`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_address_1_5_0`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_address' AND `constraint_name` = '#__redshopb_address_fk1') THEN
    ALTER TABLE `#__redshopb_address` DROP FOREIGN KEY `#__redshopb_address_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_address' AND `index_name` = '#__redshopb_address_fk1') THEN
    ALTER TABLE `#__redshopb_address` DROP INDEX `#__redshopb_address_fk1`;
  END IF;
END//

DELIMITER ;


-- -----------------------------------------------------
-- Table `#__redshopb_tag`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_tag_1_5_0`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_tag_1_5_0`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_tag' AND `constraint_name` = 'idx_type') THEN
    ALTER TABLE `#__redshopb_tag` DROP FOREIGN KEY `idx_type`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_tag' AND `index_name` = 'idx_type') THEN
    ALTER TABLE `#__redshopb_tag` DROP INDEX `idx_type`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_tag' AND `constraint_name` = '#__redshopb_category_fk1') THEN
    ALTER TABLE `#__redshopb_tag` DROP FOREIGN KEY `#__redshopb_category_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_tag' AND `index_name` = '#__redshopb_category_fk1') THEN
    ALTER TABLE `#__redshopb_tag` DROP INDEX `#__redshopb_category_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_tag' AND `constraint_name` = '#__redshopb_category_fk2') THEN
    ALTER TABLE `#__redshopb_tag` DROP FOREIGN KEY `#__redshopb_category_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_tag' AND `index_name` = '#__redshopb_category_fk2') THEN
    ALTER TABLE `#__redshopb_tag` DROP INDEX `#__redshopb_category_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_tag' AND `constraint_name` = '#__redshopb_category_fk3') THEN
    ALTER TABLE `#__redshopb_tag` DROP FOREIGN KEY `#__redshopb_category_fk3`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_tag' AND `index_name` = '#__redshopb_category_fk3') THEN
    ALTER TABLE `#__redshopb_tag` DROP INDEX `#__redshopb_category_fk3`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_tag' AND `constraint_name` = '#__redshopb_category_fk4') THEN
    ALTER TABLE `#__redshopb_tag` DROP FOREIGN KEY `#__redshopb_category_fk4`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_tag' AND `index_name` = '#__redshopb_category_fk4') THEN
    ALTER TABLE `#__redshopb_tag` DROP INDEX `#__redshopb_category_fk4`;
  END IF;

END//

DELIMITER ;


-- -----------------------------------------------------
-- Table `#__redshopb_tag_rctranslations` cleanup for translation system
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_tag_rctranslations_1_5_0`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_tag_rctranslations_1_5_0`() BEGIN
  DECLARE bDone INT DEFAULT FALSE;
  DECLARE fk VARCHAR(255);
  DECLARE keyname VARCHAR(255);

  DECLARE cursorfk CURSOR FOR SELECT DISTINCT `constraint_name` FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_tag_rctranslations';
  DECLARE cursorkey CURSOR FOR SELECT DISTINCT `index_name` FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_tag_rctranslations' AND index_name NOT IN ('PRIMARY', 'language_idx');

  DECLARE CONTINUE HANDLER FOR NOT FOUND SET bDone = TRUE;

  IF EXISTS (SELECT DISTINCT `constraint_name` FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_tag_rctranslations') THEN
    OPEN cursorfk;
    SET bDone = FALSE;
    read_loop: LOOP
      IF bDone THEN
      LEAVE read_loop;
      END IF;
    
      FETCH cursorfk INTO fk;
      IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_tag_rctranslations' AND `constraint_name` = fk) THEN
        SET @query = CONCAT('ALTER TABLE `#__redshopb_tag_rctranslations` DROP FOREIGN KEY `', fk, '`;');
        PREPARE stmt FROM @query; 
        EXECUTE stmt; 
        DEALLOCATE PREPARE stmt;
      END IF;
    END LOOP;
    CLOSE cursorfk;
  END IF;

  IF EXISTS (SELECT DISTINCT `index_name` FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_tag_rctranslations' AND index_name NOT IN ('PRIMARY', 'language_idx')) THEN
    OPEN cursorkey;
    SET bDone = FALSE;
    read_loop: LOOP
      IF bDone THEN
      LEAVE read_loop;
      END IF;
    
      FETCH cursorkey INTO keyname;
      IF EXISTS (SELECT DISTINCT `index_name` FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_tag_rctranslations' AND `index_name` = keyname) THEN
        SET @query = CONCAT('ALTER TABLE `#__redshopb_tag_rctranslations` DROP INDEX `', keyname, '`;');
        PREPARE stmt FROM @query; 
        EXECUTE stmt; 
        DEALLOCATE PREPARE stmt;
      END IF;
    END LOOP;
    CLOSE cursorkey;
  END IF;

END//

DELIMITER ;


-- -----------------------------------------------------
-- Table `#__redshopb_category`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_category_1_5_0`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_category_1_5_0`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_category' AND `constraint_name` = 'cat_idx') THEN
    ALTER TABLE `#__redshopb_category` DROP FOREIGN KEY `cat_idx`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_category' AND `index_name` = 'cat_idx') THEN
    ALTER TABLE `#__redshopb_category` DROP INDEX `cat_idx`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_category' AND `constraint_name` = '#__redshopb_category_ibfk_1') THEN
    ALTER TABLE `#__redshopb_category` DROP FOREIGN KEY `#__redshopb_category_ibfk_1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_category' AND `index_name` = '#__redshopb_category_ibfk_1') THEN
    ALTER TABLE `#__redshopb_category` DROP INDEX `#__redshopb_category_ibfk_1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_category' AND `constraint_name` = 'idx_company_id') THEN
    ALTER TABLE `#__redshopb_category` DROP FOREIGN KEY `idx_company_id`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_category' AND `index_name` = 'idx_company_id') THEN
    ALTER TABLE `#__redshopb_category` DROP INDEX `idx_company_id`;
  END IF;

END//

DELIMITER ;


-- -----------------------------------------------------
-- Table `#__redshopb_category_rctranslations` cleanup for translation system
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_category_rctranslations_1_5_0`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_category_rctranslations_1_5_0`() BEGIN
  DECLARE bDone INT DEFAULT FALSE;
  DECLARE fk VARCHAR(255);
  DECLARE keyname VARCHAR(255);

  DECLARE cursorfk CURSOR FOR SELECT DISTINCT `constraint_name` FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_category_rctranslations';
  DECLARE cursorkey CURSOR FOR SELECT DISTINCT `index_name` FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_category_rctranslations' AND index_name NOT IN ('PRIMARY', 'language_idx');

  DECLARE CONTINUE HANDLER FOR NOT FOUND SET bDone = TRUE;

  IF EXISTS (SELECT DISTINCT `constraint_name` FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_category_rctranslations') THEN
    OPEN cursorfk;
    SET bDone = FALSE;
    read_loop: LOOP
      IF bDone THEN
      LEAVE read_loop;
      END IF;
    
      FETCH cursorfk INTO fk;
      IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_category_rctranslations' AND `constraint_name` = fk) THEN
        SET @query = CONCAT('ALTER TABLE `#__redshopb_category_rctranslations` DROP FOREIGN KEY `', fk, '`;');
        PREPARE stmt FROM @query; 
        EXECUTE stmt; 
        DEALLOCATE PREPARE stmt;
      END IF;
    END LOOP;
    CLOSE cursorfk;
  END IF;

  IF EXISTS (SELECT DISTINCT `index_name` FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_category_rctranslations' AND index_name NOT IN ('PRIMARY', 'language_idx')) THEN
    OPEN cursorkey;
    SET bDone = FALSE;
    read_loop: LOOP
      IF bDone THEN
      LEAVE read_loop;
      END IF;
    
      FETCH cursorkey INTO keyname;
      IF EXISTS (SELECT DISTINCT `index_name` FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_category_rctranslations' AND `index_name` = keyname) THEN
        SET @query = CONCAT('ALTER TABLE `#__redshopb_category_rctranslations` DROP INDEX `', keyname, '`;');
        PREPARE stmt FROM @query; 
        EXECUTE stmt; 
        DEALLOCATE PREPARE stmt;
      END IF;
    END LOOP;
    CLOSE cursorkey;
  END IF;

END//

DELIMITER ;

-- -----------------------------------------------------
-- Table `#__redshopb_company`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_company_1_5_0`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_company_1_5_0`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_company' AND `constraint_name` = '#__redshopb_company_fk1') THEN
    ALTER TABLE `#__redshopb_company` DROP FOREIGN KEY `#__redshopb_company_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_company' AND `index_name` = '#__redshopb_company_fk1') THEN
    ALTER TABLE `#__redshopb_company` DROP INDEX `#__redshopb_company_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_company' AND `constraint_name` = '#__redshopb_company_fk2') THEN
    ALTER TABLE `#__redshopb_company` DROP FOREIGN KEY `#__redshopb_company_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_company' AND `index_name` = '#__redshopb_company_fk2') THEN
    ALTER TABLE `#__redshopb_company` DROP INDEX `#__redshopb_company_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_company' AND `constraint_name` = '#__redshopb_company_fk3') THEN
    ALTER TABLE `#__redshopb_company` DROP FOREIGN KEY `#__redshopb_company_fk3`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_company' AND `index_name` = '#__redshopb_company_fk3') THEN
    ALTER TABLE `#__redshopb_company` DROP INDEX `#__redshopb_company_fk3`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_company' AND `constraint_name` = '#__redshopb_company_fk4') THEN
    ALTER TABLE `#__redshopb_company` DROP FOREIGN KEY `#__redshopb_company_fk4`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_company' AND `index_name` = '#__redshopb_company_fk4') THEN
    ALTER TABLE `#__redshopb_company` DROP INDEX `#__redshopb_company_fk4`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_company' AND `constraint_name` = '#__redshopb_company_fk5') THEN
    ALTER TABLE `#__redshopb_company` DROP FOREIGN KEY `#__redshopb_company_fk5`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_company' AND `index_name` = '#__redshopb_company_fk5') THEN
    ALTER TABLE `#__redshopb_company` DROP INDEX `#__redshopb_company_fk5`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_company' AND `constraint_name` = '#__redshopb_company_fk6') THEN
    ALTER TABLE `#__redshopb_company` DROP FOREIGN KEY `#__redshopb_company_fk6`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_company' AND `index_name` = '#__redshopb_company_fk6') THEN
    ALTER TABLE `#__redshopb_company` DROP INDEX `#__redshopb_company_fk6`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_company' AND `constraint_name` = '#__redshopb_company_fk7') THEN
    ALTER TABLE `#__redshopb_company` DROP FOREIGN KEY `#__redshopb_company_fk7`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_company' AND `index_name` = '#__redshopb_company_fk7') THEN
    ALTER TABLE `#__redshopb_company` DROP INDEX `#__redshopb_company_fk7`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_company' AND `index_name` = 'idx_address_id') THEN
    ALTER TABLE `#__redshopb_company` DROP INDEX `idx_address_id`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_company' AND `index_name` = 'idx_parent_id') THEN
    ALTER TABLE `#__redshopb_company` DROP INDEX `idx_parent_id`;
  END IF;
END//

DELIMITER ;


-- -----------------------------------------------------
-- Table `#__redshopb_company_sales_person_xref`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_company_sales_person_xref_1_5_0`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_company_sales_person_xref_1_5_0`() BEGIN
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

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_company_sales_person_xref' AND `index_name` = 'idx_user_id') THEN
    ALTER TABLE `#__redshopb_company_sales_person_xref` DROP INDEX `idx_user_id`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_company_sales_person_xref' AND `index_name` = 'idx_company_id') THEN
    ALTER TABLE `#__redshopb_company_sales_person_xref` DROP INDEX `idx_company_id`;
  END IF;
END//

DELIMITER ;


-- -----------------------------------------------------
-- Table `#__redshopb_currency`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_currency_1_5_0`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_currency_1_5_0`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_currency' AND `constraint_name` = '#__redshopb_currency_fk1') THEN
    ALTER TABLE `#__redshopb_currency` DROP FOREIGN KEY `#__redshopb_currency_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_currency' AND `index_name` = '#__redshopb_currency_fk1') THEN
    ALTER TABLE `#__redshopb_currency` DROP INDEX `#__redshopb_currency_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_currency' AND `constraint_name` = '#__redshopb_currency_fk2') THEN
    ALTER TABLE `#__redshopb_currency` DROP FOREIGN KEY `#__redshopb_currency_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_currency' AND `index_name` = '#__redshopb_currency_fk2') THEN
    ALTER TABLE `#__redshopb_currency` DROP INDEX `#__redshopb_currency_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_currency' AND `constraint_name` = '#__redshopb_currency_fk3') THEN
    ALTER TABLE `#__redshopb_currency` DROP FOREIGN KEY `#__redshopb_currency_fk3`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_currency' AND `index_name` = '#__redshopb_currency_fk3') THEN
    ALTER TABLE `#__redshopb_currency` DROP INDEX `#__redshopb_currency_fk3`;
  END IF;
END//

DELIMITER ;



-- -----------------------------------------------------
-- Table `#__redshopb_customer_discount_group`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_customer_discount_group_1_5_0`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_customer_discount_group_1_5_0`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_customer_discount_group' AND `constraint_name` = '#__rcustomer_dg_fk1') THEN
    ALTER TABLE `#__redshopb_customer_discount_group` DROP FOREIGN KEY `#__rcustomer_dg_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_customer_discount_group' AND `index_name` = '#__rcustomer_dg_fk1') THEN
    ALTER TABLE `#__redshopb_customer_discount_group` DROP INDEX `#__rcustomer_dg_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_customer_discount_group' AND `constraint_name` = '#__redshopb_customer_discount_group_fk1') THEN
    ALTER TABLE `#__redshopb_customer_discount_group` DROP FOREIGN KEY `#__redshopb_customer_discount_group_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_customer_discount_group' AND `index_name` = '#__redshopb_customer_discount_group_fk1') THEN
    ALTER TABLE `#__redshopb_customer_discount_group` DROP INDEX `#__redshopb_customer_discount_group_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_customer_discount_group' AND `constraint_name` = '#__redshopb_customer_discount_group_fk2') THEN
    ALTER TABLE `#__redshopb_customer_discount_group` DROP FOREIGN KEY `#__redshopb_customer_discount_group_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_customer_discount_group' AND `index_name` = '#__redshopb_customer_discount_group_fk2') THEN
    ALTER TABLE `#__redshopb_customer_discount_group` DROP INDEX `#__redshopb_customer_discount_group_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_customer_discount_group' AND `constraint_name` = '#__redshopb_customer_discount_group_fk3') THEN
    ALTER TABLE `#__redshopb_customer_discount_group` DROP FOREIGN KEY `#__redshopb_customer_discount_group_fk3`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_customer_discount_group' AND `index_name` = '#__redshopb_customer_discount_group_fk3') THEN
    ALTER TABLE `#__redshopb_customer_discount_group` DROP INDEX `#__redshopb_customer_discount_group_fk3`;
  END IF;
END//

DELIMITER ;


-- -----------------------------------------------------
-- Table `#__redshopb_customer_discount_group_xref`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_customer_discount_group_xref_1_5_0`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_customer_discount_group_xref_1_5_0`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_customer_discount_group_xref' AND `constraint_name` = '#__redshopb_customer_discount_group_xref_fk_1') THEN
    ALTER TABLE `#__redshopb_customer_discount_group_xref` DROP FOREIGN KEY `#__redshopb_customer_discount_group_xref_fk_1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_customer_discount_group_xref' AND `index_name` = '#__redshopb_customer_discount_group_xref_fk_1') THEN
    ALTER TABLE `#__redshopb_customer_discount_group_xref` DROP INDEX `#__redshopb_customer_discount_group_xref_fk_1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_customer_discount_group_xref' AND `constraint_name` = '#__redshopb_customer_discount_group_xref_fk_2') THEN
    ALTER TABLE `#__redshopb_customer_discount_group_xref` DROP FOREIGN KEY `#__redshopb_customer_discount_group_xref_fk_2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_customer_discount_group_xref' AND `index_name` = '#__redshopb_customer_discount_group_xref_fk_2') THEN
    ALTER TABLE `#__redshopb_customer_discount_group_xref` DROP INDEX `#__redshopb_customer_discount_group_xref_fk_2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_customer_discount_group_xref' AND `index_name` = 'idx_customer_id') THEN
    ALTER TABLE `#__redshopb_customer_discount_group_xref` DROP INDEX `idx_customer_id`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_customer_discount_group_xref' AND `index_name` = 'idx_discount_group_id') THEN
    ALTER TABLE `#__redshopb_customer_discount_group_xref` DROP INDEX `idx_discount_group_id`;
  END IF;
END//

DELIMITER ;


-- -----------------------------------------------------
-- Table `#__redshopb_customer_price_group`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_customer_price_group_1_5_0`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_customer_price_group_1_5_0`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_customer_price_group' AND `constraint_name` = '#__rcustomer_pg_fk1') THEN
    ALTER TABLE `#__redshopb_customer_price_group` DROP FOREIGN KEY `#__rcustomer_pg_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_customer_price_group' AND `index_name` = '#__rcustomer_pg_fk1') THEN
    ALTER TABLE `#__redshopb_customer_price_group` DROP INDEX `#__rcustomer_pg_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_customer_price_group' AND `constraint_name` = '#__redshopb_customer_discount_group_fk1') THEN
    ALTER TABLE `#__redshopb_customer_price_group` DROP FOREIGN KEY `#__redshopb_customer_discount_group_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_customer_price_group' AND `index_name` = '#__redshopb_customer_discount_group_fk1') THEN
    ALTER TABLE `#__redshopb_customer_price_group` DROP INDEX `#__redshopb_customer_discount_group_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_customer_price_group' AND `constraint_name` = '#__redshopb_customer_discount_group_fk2') THEN
    ALTER TABLE `#__redshopb_customer_price_group` DROP FOREIGN KEY `#__redshopb_customer_discount_group_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_customer_price_group' AND `index_name` = '#__redshopb_customer_discount_group_fk2') THEN
    ALTER TABLE `#__redshopb_customer_price_group` DROP INDEX `#__redshopb_customer_discount_group_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_customer_price_group' AND `constraint_name` = '#__redshopb_customer_discount_group_fk3') THEN
    ALTER TABLE `#__redshopb_customer_price_group` DROP FOREIGN KEY `#__redshopb_customer_discount_group_fk3`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_customer_price_group' AND `index_name` = '#__redshopb_customer_discount_group_fk3') THEN
    ALTER TABLE `#__redshopb_customer_price_group` DROP INDEX `#__redshopb_customer_discount_group_fk3`;
  END IF;
END//

DELIMITER ;


-- -----------------------------------------------------
-- Table `#__redshopb_customer_price_group_xref`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_customer_price_group_xref_1_5_0`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_customer_price_group_xref_1_5_0`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_customer_price_group_xref' AND `constraint_name` = '#__redshopb_customer_price_group_xref_ibfk_1') THEN
    ALTER TABLE `#__redshopb_customer_price_group_xref` DROP FOREIGN KEY `#__redshopb_customer_price_group_xref_ibfk_1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_customer_price_group_xref' AND `index_name` = '#__redshopb_customer_price_group_xref_ibfk_1') THEN
    ALTER TABLE `#__redshopb_customer_price_group_xref` DROP INDEX `#__redshopb_customer_price_group_xref_ibfk_1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_customer_price_group_xref' AND `constraint_name` = '#__redshopb_customer_price_group_xref_ibfk_2') THEN
    ALTER TABLE `#__redshopb_customer_price_group_xref` DROP FOREIGN KEY `#__redshopb_customer_price_group_xref_ibfk_2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_customer_price_group_xref' AND `index_name` = '#__redshopb_customer_price_group_xref_ibfk_2') THEN
    ALTER TABLE `#__redshopb_customer_price_group_xref` DROP INDEX `#__redshopb_customer_price_group_xref_ibfk_2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_customer_price_group_xref' AND `index_name` = 'idx_customer_id') THEN
    ALTER TABLE `#__redshopb_customer_price_group_xref` DROP INDEX `idx_customer_id`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_customer_price_group_xref' AND `index_name` = 'idx_price_group_id') THEN
    ALTER TABLE `#__redshopb_customer_price_group_xref` DROP INDEX `idx_price_group_id`;
  END IF;
END//

DELIMITER ;


-- -----------------------------------------------------
-- Table `#__redshopb_department`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_department_1_5_0`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_department_1_5_0`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_department' AND `constraint_name` = '#__redshopb_department_fk1') THEN
    ALTER TABLE `#__redshopb_department` DROP FOREIGN KEY `#__redshopb_department_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_department' AND `index_name` = '#__redshopb_department_fk1') THEN
    ALTER TABLE `#__redshopb_department` DROP INDEX `#__redshopb_department_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_department' AND `constraint_name` = '#__redshopb_department_fk2') THEN
    ALTER TABLE `#__redshopb_department` DROP FOREIGN KEY `#__redshopb_department_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_department' AND `index_name` = '#__redshopb_department_fk2') THEN
    ALTER TABLE `#__redshopb_department` DROP INDEX `#__redshopb_department_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_department' AND `constraint_name` = '#__redshopb_department_fk3') THEN
    ALTER TABLE `#__redshopb_department` DROP FOREIGN KEY `#__redshopb_department_fk3`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_department' AND `index_name` = '#__redshopb_department_fk3') THEN
    ALTER TABLE `#__redshopb_department` DROP INDEX `#__redshopb_department_fk3`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_department' AND `constraint_name` = '#__redshopb_department_fk4') THEN
    ALTER TABLE `#__redshopb_department` DROP FOREIGN KEY `#__redshopb_department_fk4`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_department' AND `index_name` = '#__redshopb_department_fk4') THEN
    ALTER TABLE `#__redshopb_department` DROP INDEX `#__redshopb_department_fk4`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_department' AND `constraint_name` = '#__redshopb_department_fk5') THEN
    ALTER TABLE `#__redshopb_department` DROP FOREIGN KEY `#__redshopb_department_fk5`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_department' AND `index_name` = '#__redshopb_department_fk5') THEN
    ALTER TABLE `#__redshopb_department` DROP INDEX `#__redshopb_department_fk5`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_department' AND `constraint_name` = '#__redshopb_department_fk6') THEN
    ALTER TABLE `#__redshopb_department` DROP FOREIGN KEY `#__redshopb_department_fk6`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_department' AND `index_name` = '#__redshopb_department_fk6') THEN
    ALTER TABLE `#__redshopb_department` DROP INDEX `#__redshopb_department_fk6`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_department' AND `constraint_name` = '#__redshopb_department_fk7') THEN
    ALTER TABLE `#__redshopb_department` DROP FOREIGN KEY `#__redshopb_department_fk7`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_department' AND `index_name` = '#__redshopb_department_fk7') THEN
    ALTER TABLE `#__redshopb_department` DROP INDEX `#__redshopb_department_fk7`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_department' AND `index_name` = 'idx_address_id') THEN
    ALTER TABLE `#__redshopb_department` DROP INDEX `idx_address_id`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_department' AND `index_name` = 'idx_company_id') THEN
    ALTER TABLE `#__redshopb_department` DROP INDEX `idx_company_id`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_department' AND `index_name` = 'idx_parent_id') THEN
    ALTER TABLE `#__redshopb_department` DROP INDEX `idx_parent_id`;
  END IF;
END//

DELIMITER ;


-- -----------------------------------------------------
-- Table `#__redshopb_fee`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_fee_1_5_0`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_fee_1_5_0`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_fee' AND `constraint_name` = '#__redshopb_fee_ibfk_1') THEN
    ALTER TABLE `#__redshopb_fee` DROP FOREIGN KEY `#__redshopb_fee_ibfk_1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_fee' AND `index_name` = '#__redshopb_fee_ibfk_1') THEN
    ALTER TABLE `#__redshopb_fee` DROP INDEX `#__redshopb_fee_ibfk_1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_fee' AND `constraint_name` = '#__redshopb_fee_ibfk_2') THEN
    ALTER TABLE `#__redshopb_fee` DROP FOREIGN KEY `#__redshopb_fee_ibfk_2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_fee' AND `index_name` = '#__redshopb_fee_ibfk_2') THEN
    ALTER TABLE `#__redshopb_fee` DROP INDEX `#__redshopb_fee_ibfk_2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_fee' AND `constraint_name` = '#__redshopb_fee_ibfk_3') THEN
    ALTER TABLE `#__redshopb_fee` DROP FOREIGN KEY `#__redshopb_fee_ibfk_3`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_fee' AND `index_name` = '#__redshopb_fee_ibfk_3') THEN
    ALTER TABLE `#__redshopb_fee` DROP INDEX `#__redshopb_fee_ibfk_3`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_fee' AND `index_name` = 'idx_currency_id') THEN
    ALTER TABLE `#__redshopb_fee` DROP INDEX `idx_currency_id`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_fee' AND `index_name` = 'idx_product_id') THEN
    ALTER TABLE `#__redshopb_fee` DROP INDEX `idx_product_id`;
  END IF;
END//

DELIMITER ;


-- -----------------------------------------------------
-- Table `#__redshopb_layout`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_layout_1_5_0`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_layout_1_5_0`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_layout' AND `constraint_name` = '#__rlayout_fk1') THEN
    ALTER TABLE `#__redshopb_layout` DROP FOREIGN KEY `#__rlayout_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_layout' AND `index_name` = '#__rlayout_fk1') THEN
    ALTER TABLE `#__redshopb_layout` DROP INDEX `#__rlayout_fk1`;
  END IF;
END//

DELIMITER ;


-- -----------------------------------------------------
-- Table `#__redshopb_logos`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_logos_1_5_0`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_logos_1_5_0`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_logos' AND `constraint_name` = '#__redshopb_logos_ibfk_1') THEN
    ALTER TABLE `#__redshopb_logos` DROP FOREIGN KEY `#__redshopb_logos_ibfk_1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_logos' AND `index_name` = '#__redshopb_logos_ibfk_1') THEN
    ALTER TABLE `#__redshopb_logos` DROP INDEX `#__redshopb_logos_ibfk_1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_logos' AND `constraint_name` = '#__redshopb_logos_ibfk_2') THEN
    ALTER TABLE `#__redshopb_logos` DROP FOREIGN KEY `#__redshopb_logos_ibfk_2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_logos' AND `index_name` = '#__redshopb_logos_ibfk_2') THEN
    ALTER TABLE `#__redshopb_logos` DROP INDEX `#__redshopb_logos_ibfk_2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_logos' AND `index_name` = 'idx_brand_id') THEN
    ALTER TABLE `#__redshopb_logos` DROP INDEX `idx_brand_id`;
  END IF;
END//


DELIMITER ;


-- -----------------------------------------------------
-- Table `#__redshopb_media`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_media_1_5_0`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_media_1_5_0`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_media' AND `constraint_name` = '#__redshopb_media_fk1') THEN
    ALTER TABLE `#__redshopb_media` DROP FOREIGN KEY `#__redshopb_media_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_media' AND `index_name` = '#__redshopb_media_fk1') THEN
    ALTER TABLE `#__redshopb_media` DROP INDEX `#__redshopb_media_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_media' AND `index_name` = 'product_id') THEN
    ALTER TABLE `#__redshopb_media` DROP INDEX `product_id`;
  END IF;
END//

DELIMITER ;


-- -----------------------------------------------------
-- Table `#__redshopb_order`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_order_1_5_0`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_order_1_5_0`() BEGIN
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

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_order' AND `index_name` = 'idx_company') THEN
    ALTER TABLE `#__redshopb_order` DROP INDEX `idx_company`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_order' AND `index_name` = 'idx_department') THEN
    ALTER TABLE `#__redshopb_order` DROP INDEX `idx_department`;
  END IF;
END//

DELIMITER ;


-- -----------------------------------------------------
-- Table `#__redshopb_order_item`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_order_item_1_5_0`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_order_item_1_5_0`() BEGIN
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

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_order_item' AND `index_name` = 'idx_order_id') THEN
    ALTER TABLE `#__redshopb_order_item` DROP INDEX `idx_order_id`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_order_item' AND `index_name` = 'idx_parent_id') THEN
    ALTER TABLE `#__redshopb_order_item` DROP INDEX `idx_parent_id`;
  END IF;
END//

DELIMITER ;


-- -----------------------------------------------------
-- Table `#__redshopb_order_logs`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_order_logs_1_5_0`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_order_logs_1_5_0`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_order_logs' AND `constraint_name` = '#__redshopb_order_logs_fk1') THEN
    ALTER TABLE `#__redshopb_order_logs` DROP FOREIGN KEY `#__redshopb_order_logs_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_order_logs' AND `index_name` = '#__redshopb_order_logs_fk1') THEN
    ALTER TABLE `#__redshopb_order_logs` DROP INDEX `#__redshopb_order_logs_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_order_logs' AND `constraint_name` = '#__redshopb_order_logs_fk2') THEN
    ALTER TABLE `#__redshopb_order_logs` DROP FOREIGN KEY `#__redshopb_order_logs_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_order_logs' AND `index_name` = '#__redshopb_order_logs_fk2') THEN
    ALTER TABLE `#__redshopb_order_logs` DROP INDEX `#__redshopb_order_logs_fk2`;
  END IF;
END//

DELIMITER ;


-- -----------------------------------------------------
-- Table `#__redshopb_product`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_product_1_5_0`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_product_1_5_0`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_product' AND `constraint_name` = '#__redshopb_product_fk1') THEN
    ALTER TABLE `#__redshopb_product` DROP FOREIGN KEY `#__redshopb_product_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_product' AND `index_name` = '#__redshopb_product_fk1') THEN
    ALTER TABLE `#__redshopb_product` DROP INDEX `#__redshopb_product_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_product' AND `constraint_name` = '#__redshopb_product_fk2') THEN
    ALTER TABLE `#__redshopb_product` DROP FOREIGN KEY `#__redshopb_product_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_product' AND `index_name` = '#__redshopb_product_fk2') THEN
    ALTER TABLE `#__redshopb_product` DROP INDEX `#__redshopb_product_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_product' AND `constraint_name` = '#__redshopb_product_fk3') THEN
    ALTER TABLE `#__redshopb_product` DROP FOREIGN KEY `#__redshopb_product_fk3`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_product' AND `index_name` = '#__redshopb_product_fk3') THEN
    ALTER TABLE `#__redshopb_product` DROP INDEX `#__redshopb_product_fk3`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_product' AND `constraint_name` = '#__redshopb_product_fk4') THEN
    ALTER TABLE `#__redshopb_product` DROP FOREIGN KEY `#__redshopb_product_fk4`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_product' AND `index_name` = '#__redshopb_product_fk4') THEN
    ALTER TABLE `#__redshopb_product` DROP INDEX `#__redshopb_product_fk4`;
  END IF;
END//

DELIMITER ;


-- -----------------------------------------------------
-- Table `#__redshopb_product_attribute`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_product_attribute_1_5_0`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_product_attribute_1_5_0`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_product_attribute' AND `constraint_name` = '#__redshopb_product_attribute_fk1') THEN
    ALTER TABLE `#__redshopb_product_attribute` DROP FOREIGN KEY `#__redshopb_product_attribute_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_product_attribute' AND `index_name` = '#__redshopb_product_attribute_fk1') THEN
    ALTER TABLE `#__redshopb_product_attribute` DROP INDEX `#__redshopb_product_attribute_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_product_attribute' AND `constraint_name` = '#__redshopb_product_attribute_fk2') THEN
    ALTER TABLE `#__redshopb_product_attribute` DROP FOREIGN KEY `#__redshopb_product_attribute_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_product_attribute' AND `index_name` = '#__redshopb_product_attribute_fk2') THEN
    ALTER TABLE `#__redshopb_product_attribute` DROP INDEX `#__redshopb_product_attribute_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_product_attribute' AND `constraint_name` = '#__redshopb_product_attribute_fk3') THEN
    ALTER TABLE `#__redshopb_product_attribute` DROP FOREIGN KEY `#__redshopb_product_attribute_fk3`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_product_attribute' AND `index_name` = '#__redshopb_product_attribute_fk3') THEN
    ALTER TABLE `#__redshopb_product_attribute` DROP INDEX `#__redshopb_product_attribute_fk3`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_product_attribute' AND `constraint_name` = '#__redshopb_product_attribute_fk4') THEN
    ALTER TABLE `#__redshopb_product_attribute` DROP FOREIGN KEY `#__redshopb_product_attribute_fk4`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_product_attribute' AND `index_name` = '#__redshopb_product_attribute_fk4') THEN
    ALTER TABLE `#__redshopb_product_attribute` DROP INDEX `#__redshopb_product_attribute_fk4`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_product_attribute' AND `index_name` = 'idx_product_id') THEN
    ALTER TABLE `#__redshopb_product_attribute` DROP INDEX `idx_product_id`;
  END IF;
END//

DELIMITER ;


-- -----------------------------------------------------
-- Table `#__redshopb_product_attribute_value`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_product_attribute_value_1_5_0`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_product_attribute_value_1_5_0`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_product_attribute_value' AND `constraint_name` = '#__redshopb_product_attribute_value_fk1') THEN
    ALTER TABLE `#__redshopb_product_attribute_value` DROP FOREIGN KEY `#__redshopb_product_attribute_value_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_product_attribute_value' AND `index_name` = '#__redshopb_product_attribute_value_fk1') THEN
    ALTER TABLE `#__redshopb_product_attribute_value` DROP INDEX `#__redshopb_product_attribute_value_fk1`;
  END IF;
END//

DELIMITER ;


-- -----------------------------------------------------
-- Table `#__redshopb_product_category_xref`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_product_category_xref_1_5_0`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_product_category_xref_1_5_0`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_product_category_xref' AND `constraint_name` = '#__redshopb_product_category_xref_ibfk_1') THEN
    ALTER TABLE `#__redshopb_product_category_xref` DROP FOREIGN KEY `#__redshopb_product_category_xref_ibfk_1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_product_category_xref' AND `index_name` = '#__redshopb_product_category_xref_ibfk_1') THEN
    ALTER TABLE `#__redshopb_product_category_xref` DROP INDEX `#__redshopb_product_category_xref_ibfk_1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_product_category_xref' AND `constraint_name` = '#__redshopb_product_category_xref_ibfk_2') THEN
    ALTER TABLE `#__redshopb_product_category_xref` DROP FOREIGN KEY `#__redshopb_product_category_xref_ibfk_2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_product_category_xref' AND `index_name` = '#__redshopb_product_category_xref_ibfk_2') THEN
    ALTER TABLE `#__redshopb_product_category_xref` DROP INDEX `#__redshopb_product_category_xref_ibfk_2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_product_category_xref' AND `index_name` = 'idx_tag_id') THEN
    ALTER TABLE `#__redshopb_product_category_xref` DROP INDEX `idx_tag_id`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_product_category_xref' AND `index_name` = 'idx_product_id') THEN
    ALTER TABLE `#__redshopb_product_category_xref` DROP INDEX `idx_product_id`;
  END IF;
END//

DELIMITER ;


-- -----------------------------------------------------
-- Table `#__redshopb_product_tag_xref`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_product_tag_xref_1_5_0`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_product_tag_xref_1_5_0`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_product_tag_xref' AND `constraint_name` = '#__redshopb_product_category_xref_fk1') THEN
    ALTER TABLE `#__redshopb_product_tag_xref` DROP FOREIGN KEY `#__redshopb_product_category_xref_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_product_tag_xref' AND `index_name` = '#__redshopb_product_category_xref_fk1') THEN
    ALTER TABLE `#__redshopb_product_tag_xref` DROP INDEX `#__redshopb_product_category_xref_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_product_tag_xref' AND `constraint_name` = '#__redshopb_product_category_xref_fk2') THEN
    ALTER TABLE `#__redshopb_product_tag_xref` DROP FOREIGN KEY `#__redshopb_product_category_xref_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_product_tag_xref' AND `index_name` = '#__redshopb_product_category_xref_fk2') THEN
    ALTER TABLE `#__redshopb_product_tag_xref` DROP INDEX `#__redshopb_product_category_xref_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_product_tag_xref' AND `index_name` = 'idx_category_id') THEN
    ALTER TABLE `#__redshopb_product_tag_xref` DROP INDEX `idx_category_id`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_product_tag_xref' AND `index_name` = 'idx_product_id') THEN
    ALTER TABLE `#__redshopb_product_tag_xref` DROP INDEX `idx_product_id`;
  END IF;
END//

DELIMITER ;


-- -----------------------------------------------------
-- Table `#__redshopb_product_composition`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_product_composition_1_5_0`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_product_composition_1_5_0`() BEGIN
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

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_product_composition' AND `index_name` = 'idx_product_id') THEN
    ALTER TABLE `#__redshopb_product_composition` DROP INDEX `idx_product_id`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_product_composition' AND `index_name` = 'idx_product_attribute_value_id') THEN
    ALTER TABLE `#__redshopb_product_composition` DROP INDEX `idx_product_attribute_value_id`;
  END IF;
END//

DELIMITER ;


-- -----------------------------------------------------
-- Table `#__redshopb_product_descriptions`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_product_descriptions_1_5_0`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_product_descriptions_1_5_0`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_product_descriptions' AND `constraint_name` = '#__redshopb_product_descriptions_ibfk_1') THEN
    ALTER TABLE `#__redshopb_product_descriptions` DROP FOREIGN KEY `#__redshopb_product_descriptions_ibfk_1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_product_descriptions' AND `index_name` = '#__redshopb_product_descriptions_ibfk_1') THEN
    ALTER TABLE `#__redshopb_product_descriptions` DROP INDEX `#__redshopb_product_descriptions_ibfk_1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_product_descriptions' AND `constraint_name` = '#__redshopb_product_descriptions_ibfk_2') THEN
    ALTER TABLE `#__redshopb_product_descriptions` DROP FOREIGN KEY `#__redshopb_product_descriptions_ibfk_2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_product_descriptions' AND `index_name` = '#__redshopb_product_descriptions_ibfk_2') THEN
    ALTER TABLE `#__redshopb_product_descriptions` DROP INDEX `#__redshopb_product_descriptions_ibfk_2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_product_descriptions' AND `index_name` = 'idx_product_id') THEN
    ALTER TABLE `#__redshopb_product_descriptions` DROP INDEX `idx_product_id`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_product_descriptions' AND `index_name` = 'idx_flat_attribute_value_id') THEN
    ALTER TABLE `#__redshopb_product_descriptions` DROP INDEX `idx_flat_attribute_value_id`;
  END IF;
END//

DELIMITER ;


-- -----------------------------------------------------
-- Table `#__redshopb_product_discount`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_product_discount_1_5_0`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_product_discount_1_5_0`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_product_discount' AND `constraint_name` = '#__redshopb_product_discount_fk1') THEN
    ALTER TABLE `#__redshopb_product_discount` DROP FOREIGN KEY `#__redshopb_product_discount_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_product_discount' AND `index_name` = '#__redshopb_product_discount_fk1') THEN
    ALTER TABLE `#__redshopb_product_discount` DROP INDEX `#__redshopb_product_discount_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_product_discount' AND `index_name` = 'idx_currency_id') THEN
    ALTER TABLE `#__redshopb_product_discount` DROP INDEX `idx_currency_id`;
  END IF;
END//

DELIMITER ;


-- -----------------------------------------------------
-- Table `#__redshopb_product_discount_group`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_product_discount_group_1_5_0`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_product_discount_group_1_5_0`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_product_discount_group' AND `constraint_name` = '#__redshopb_product_discount_group_fk1') THEN
    ALTER TABLE `#__redshopb_product_discount_group` DROP FOREIGN KEY `#__redshopb_product_discount_group_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_product_discount_group' AND `index_name` = '#__redshopb_product_discount_group_fk1') THEN
    ALTER TABLE `#__redshopb_product_discount_group` DROP INDEX `#__redshopb_product_discount_group_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_product_discount_group' AND `constraint_name` = '#__redshopb_product_discount_group_fk2') THEN
    ALTER TABLE `#__redshopb_product_discount_group` DROP FOREIGN KEY `#__redshopb_product_discount_group_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_product_discount_group' AND `index_name` = '#__redshopb_product_discount_group_fk2') THEN
    ALTER TABLE `#__redshopb_product_discount_group` DROP INDEX `#__redshopb_product_discount_group_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_product_discount_group' AND `constraint_name` = '#__redshopb_product_discount_group_fk3') THEN
    ALTER TABLE `#__redshopb_product_discount_group` DROP FOREIGN KEY `#__redshopb_product_discount_group_fk3`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_product_discount_group' AND `index_name` = '#__redshopb_product_discount_group_fk3') THEN
    ALTER TABLE `#__redshopb_product_discount_group` DROP INDEX `#__redshopb_product_discount_group_fk3`;
  END IF;
END//

DELIMITER ;


-- -----------------------------------------------------
-- Table `#__redshopb_product_discount_group_xref`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_product_discount_group_xref_1_5_0`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_product_discount_group_xref_1_5_0`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_product_discount_group_xref' AND `constraint_name` = '#__redshopb_product_discount_group_xref_ibfk_1') THEN
    ALTER TABLE `#__redshopb_product_discount_group_xref` DROP FOREIGN KEY `#__redshopb_product_discount_group_xref_ibfk_1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_product_discount_group_xref' AND `index_name` = '#__redshopb_product_discount_group_xref_ibfk_1') THEN
    ALTER TABLE `#__redshopb_product_discount_group_xref` DROP INDEX `#__redshopb_product_discount_group_xref_ibfk_1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_product_discount_group_xref' AND `constraint_name` = '#__redshopb_product_discount_group_xref_ibfk_2') THEN
    ALTER TABLE `#__redshopb_product_discount_group_xref` DROP FOREIGN KEY `#__redshopb_product_discount_group_xref_ibfk_2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_product_discount_group_xref' AND `index_name` = '#__redshopb_product_discount_group_xref_ibfk_2') THEN
    ALTER TABLE `#__redshopb_product_discount_group_xref` DROP INDEX `#__redshopb_product_discount_group_xref_ibfk_2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_product_discount_group_xref' AND `index_name` = 'idx_product_id') THEN
    ALTER TABLE `#__redshopb_product_discount_group_xref` DROP INDEX `idx_product_id`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_product_discount_group_xref' AND `index_name` = 'idx_discount_group_id') THEN
    ALTER TABLE `#__redshopb_product_discount_group_xref` DROP INDEX `idx_discount_group_id`;
  END IF;
END//

DELIMITER ;


-- -----------------------------------------------------
-- Table `#__redshopb_product_item`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_product_item_1_5_0`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_product_item_1_5_0`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_product_item' AND `constraint_name` = '#__redshopb_product_item_fk1') THEN
    ALTER TABLE `#__redshopb_product_item` DROP FOREIGN KEY `#__redshopb_product_item_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_product_item' AND `index_name` = '#__redshopb_product_item_fk1') THEN
    ALTER TABLE `#__redshopb_product_item` DROP INDEX `#__redshopb_product_item_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_product_item' AND `index_name` = 'idx_product_id') THEN
    ALTER TABLE `#__redshopb_product_item` DROP INDEX `idx_product_id`;
  END IF;
END//

DELIMITER ;


-- -----------------------------------------------------
-- Table `#__redshopb_product_item_accessory`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_product_item_accessory_1_5_0`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_product_item_accessory_1_5_0`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_product_item_accessory' AND `constraint_name` = '#__redshopb_product_item_accessory_ibfk_1') THEN
    ALTER TABLE `#__redshopb_product_item_accessory` DROP FOREIGN KEY `#__redshopb_product_item_accessory_ibfk_1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_product_item_accessory' AND `index_name` = '#__redshopb_product_item_accessory_ibfk_1') THEN
    ALTER TABLE `#__redshopb_product_item_accessory` DROP INDEX `#__redshopb_product_item_accessory_ibfk_1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_product_item_accessory' AND `constraint_name` = '#__redshopb_product_item_accessory_ibfk_2') THEN
    ALTER TABLE `#__redshopb_product_item_accessory` DROP FOREIGN KEY `#__redshopb_product_item_accessory_ibfk_2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_product_item_accessory' AND `index_name` = '#__redshopb_product_item_accessory_ibfk_2') THEN
    ALTER TABLE `#__redshopb_product_item_accessory` DROP INDEX `#__redshopb_product_item_accessory_ibfk_2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_product_item_accessory' AND `constraint_name` = '#__redshopb_product_item_accessory_ibfk_3') THEN
    ALTER TABLE `#__redshopb_product_item_accessory` DROP FOREIGN KEY `#__redshopb_product_item_accessory_ibfk_3`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_product_item_accessory' AND `index_name` = '#__redshopb_product_item_accessory_ibfk_3') THEN
    ALTER TABLE `#__redshopb_product_item_accessory` DROP INDEX `#__redshopb_product_item_accessory_ibfk_3`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_product_item_accessory' AND `index_name` = 'idx_attribute_value_id') THEN
    ALTER TABLE `#__redshopb_product_item_accessory` DROP INDEX `idx_attribute_value_id`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_product_item_accessory' AND `index_name` = 'idx_accessory_product_id') THEN
    ALTER TABLE `#__redshopb_product_item_accessory` DROP INDEX `idx_accessory_product_id`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_product_item_accessory' AND `index_name` = 'idx_wardrobe_id') THEN
    ALTER TABLE `#__redshopb_product_item_accessory` DROP INDEX `idx_wardrobe_id`;
  END IF;
END//

DELIMITER ;


-- -----------------------------------------------------
-- Table `#__redshopb_product_item_attribute_value_xref`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_product_item_attribute_value_xref_1_5_0`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_product_item_attribute_value_xref_1_5_0`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_product_item_attribute_value_xref' AND `constraint_name` = '#__redshopb_product_item_attribute_value_xref_fk1') THEN
    ALTER TABLE `#__redshopb_product_item_attribute_value_xref` DROP FOREIGN KEY `#__redshopb_product_item_attribute_value_xref_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_product_item_attribute_value_xref' AND `index_name` = '#__redshopb_product_item_attribute_value_xref_fk1') THEN
    ALTER TABLE `#__redshopb_product_item_attribute_value_xref` DROP INDEX `#__redshopb_product_item_attribute_value_xref_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_product_item_attribute_value_xref' AND `constraint_name` = '#__redshopb_product_item_attribute_value_xref_fk2') THEN
    ALTER TABLE `#__redshopb_product_item_attribute_value_xref` DROP FOREIGN KEY `#__redshopb_product_item_attribute_value_xref_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_product_item_attribute_value_xref' AND `index_name` = '#__redshopb_product_item_attribute_value_xref_fk2') THEN
    ALTER TABLE `#__redshopb_product_item_attribute_value_xref` DROP INDEX `#__redshopb_product_item_attribute_value_xref_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_product_item_attribute_value_xref' AND `index_name` = 'idx_product_item') THEN
    ALTER TABLE `#__redshopb_product_item_attribute_value_xref` DROP INDEX `idx_product_item`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_product_item_attribute_value_xref' AND `index_name` = 'product_attribute_value') THEN
    ALTER TABLE `#__redshopb_product_item_attribute_value_xref` DROP INDEX `product_attribute_value`;
  END IF;
END//

DELIMITER ;


-- -----------------------------------------------------
-- Table `#__redshopb_product_price`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_product_price_1_5_0`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_product_price_1_5_0`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_product_price' AND `constraint_name` = '#__redshopb_product_price_fk1') THEN
    ALTER TABLE `#__redshopb_product_price` DROP FOREIGN KEY `#__redshopb_product_price_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_product_price' AND `index_name` = '#__redshopb_product_price_fk1') THEN
    ALTER TABLE `#__redshopb_product_price` DROP INDEX `#__redshopb_product_price_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_product_price' AND `index_name` = 'idx_country_id') THEN
    ALTER TABLE `#__redshopb_product_price` DROP INDEX `idx_country_id`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_product_price' AND `index_name` = 'idx_currency_id') THEN
    ALTER TABLE `#__redshopb_product_price` DROP INDEX `idx_currency_id`;
  END IF;
END//

DELIMITER ;


-- -----------------------------------------------------
-- Table `#__redshopb_product_wash_care_spec_xref`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_product_wash_care_spec_xref_1_5_0`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_product_wash_care_spec_xref_1_5_0`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_product_wash_care_spec_xref' AND `constraint_name` = '#__redshopb_product_wash_care_spec_xref_ibfk_1') THEN
    ALTER TABLE `#__redshopb_product_wash_care_spec_xref` DROP FOREIGN KEY `#__redshopb_product_wash_care_spec_xref_ibfk_1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_product_wash_care_spec_xref' AND `index_name` = '#__redshopb_product_wash_care_spec_xref_ibfk_1') THEN
    ALTER TABLE `#__redshopb_product_wash_care_spec_xref` DROP INDEX `#__redshopb_product_wash_care_spec_xref_ibfk_1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_product_wash_care_spec_xref' AND `constraint_name` = '#__redshopb_product_wash_care_spec_xref_ibfk_2') THEN
    ALTER TABLE `#__redshopb_product_wash_care_spec_xref` DROP FOREIGN KEY `#__redshopb_product_wash_care_spec_xref_ibfk_2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_product_wash_care_spec_xref' AND `index_name` = '#__redshopb_product_wash_care_spec_xref_ibfk_2') THEN
    ALTER TABLE `#__redshopb_product_wash_care_spec_xref` DROP INDEX `#__redshopb_product_wash_care_spec_xref_ibfk_2`;
  END IF;
END//

DELIMITER ;


-- -----------------------------------------------------
-- Table `#__redshopb_role`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_role_1_5_0`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_role_1_5_0`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_role' AND `constraint_name` = '#__redshopb_role_fk1') THEN
    ALTER TABLE `#__redshopb_role` DROP FOREIGN KEY `#__redshopb_role_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_role' AND `index_name` = '#__redshopb_role_fk1') THEN
    ALTER TABLE `#__redshopb_role` DROP INDEX `#__redshopb_role_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_role' AND `constraint_name` = '#__redshopb_role_fk2') THEN
    ALTER TABLE `#__redshopb_role` DROP FOREIGN KEY `#__redshopb_role_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_role' AND `index_name` = '#__redshopb_role_fk2') THEN
    ALTER TABLE `#__redshopb_role` DROP INDEX `#__redshopb_role_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_role' AND `constraint_name` = '#__redshopb_role_fk3') THEN
    ALTER TABLE `#__redshopb_role` DROP FOREIGN KEY `#__redshopb_role_fk3`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_role' AND `index_name` = '#__redshopb_role_fk3') THEN
    ALTER TABLE `#__redshopb_role` DROP INDEX `#__redshopb_role_fk3`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_role' AND `constraint_name` = '#__redshopb_role_fk4') THEN
    ALTER TABLE `#__redshopb_role` DROP FOREIGN KEY `#__redshopb_role_fk4`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_role' AND `index_name` = '#__redshopb_role_fk4') THEN
    ALTER TABLE `#__redshopb_role` DROP INDEX `#__redshopb_role_fk4`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_role' AND `constraint_name` = '#__redshopb_role_fk5') THEN
    ALTER TABLE `#__redshopb_role` DROP FOREIGN KEY `#__redshopb_role_fk5`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_role' AND `index_name` = '#__redshopb_role_fk5') THEN
    ALTER TABLE `#__redshopb_role` DROP INDEX `#__redshopb_role_fk5`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_role' AND `constraint_name` = '#__redshopb_role_fk6') THEN
    ALTER TABLE `#__redshopb_role` DROP FOREIGN KEY `#__redshopb_role_fk6`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_role' AND `index_name` = '#__redshopb_role_fk6') THEN
    ALTER TABLE `#__redshopb_role` DROP INDEX `#__redshopb_role_fk6`;
  END IF;
END//

DELIMITER ;


-- -----------------------------------------------------
-- Table `#__redshopb_role_type`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_role_type_1_5_0`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_role_type_1_5_0`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_role_type' AND `constraint_name` = '#__redshopb_role_type_fk1') THEN
    ALTER TABLE `#__redshopb_role_type` DROP FOREIGN KEY `#__redshopb_role_type_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_role_type' AND `index_name` = '#__redshopb_role_type_fk1') THEN
    ALTER TABLE `#__redshopb_role_type` DROP INDEX `#__redshopb_role_type_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_role_type' AND `constraint_name` = '#__redshopb_role_type_fk2') THEN
    ALTER TABLE `#__redshopb_role_type` DROP FOREIGN KEY `#__redshopb_role_type_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_role_type' AND `index_name` = '#__redshopb_role_type_fk2') THEN
    ALTER TABLE `#__redshopb_role_type` DROP INDEX `#__redshopb_role_type_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_role_type' AND `constraint_name` = '#__redshopb_role_type_fk3') THEN
    ALTER TABLE `#__redshopb_role_type` DROP FOREIGN KEY `#__redshopb_role_type_fk3`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_role_type' AND `index_name` = '#__redshopb_role_type_fk3') THEN
    ALTER TABLE `#__redshopb_role_type` DROP INDEX `#__redshopb_role_type_fk3`;
  END IF;
END//

DELIMITER ;


-- -----------------------------------------------------
-- Table `#__redshopb_user`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_user_1_5_0`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_user_1_5_0`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_user' AND `constraint_name` = '#__redshopb_user_fk1') THEN
    ALTER TABLE `#__redshopb_user` DROP FOREIGN KEY `#__redshopb_user_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_user' AND `index_name` = '#__redshopb_user_fk1') THEN
    ALTER TABLE `#__redshopb_user` DROP INDEX `#__redshopb_user_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_user' AND `constraint_name` = '#__redshopb_user_fk2') THEN
    ALTER TABLE `#__redshopb_user` DROP FOREIGN KEY `#__redshopb_user_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_user' AND `index_name` = '#__redshopb_user_fk2') THEN
    ALTER TABLE `#__redshopb_user` DROP INDEX `#__redshopb_user_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_user' AND `constraint_name` = '#__redshopb_user_fk3') THEN
    ALTER TABLE `#__redshopb_user` DROP FOREIGN KEY `#__redshopb_user_fk3`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_user' AND `index_name` = '#__redshopb_user_fk3') THEN
    ALTER TABLE `#__redshopb_user` DROP INDEX `#__redshopb_user_fk3`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_user' AND `constraint_name` = '#__redshopb_user_fk4') THEN
    ALTER TABLE `#__redshopb_user` DROP FOREIGN KEY `#__redshopb_user_fk4`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_user' AND `index_name` = '#__redshopb_user_fk4') THEN
    ALTER TABLE `#__redshopb_user` DROP INDEX `#__redshopb_user_fk4`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_user' AND `constraint_name` = '#__redshopb_user_fk5') THEN
    ALTER TABLE `#__redshopb_user` DROP FOREIGN KEY `#__redshopb_user_fk5`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_user' AND `index_name` = '#__redshopb_user_fk5') THEN
    ALTER TABLE `#__redshopb_user` DROP INDEX `#__redshopb_user_fk5`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_user' AND `constraint_name` = '#__redshopb_user_fk6') THEN
    ALTER TABLE `#__redshopb_user` DROP FOREIGN KEY `#__redshopb_user_fk6`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_user' AND `index_name` = '#__redshopb_user_fk6') THEN
    ALTER TABLE `#__redshopb_user` DROP INDEX `#__redshopb_user_fk6`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_user' AND `constraint_name` = '#__redshopb_user_fk7') THEN
    ALTER TABLE `#__redshopb_user` DROP FOREIGN KEY `#__redshopb_user_fk7`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_user' AND `index_name` = '#__redshopb_user_fk7') THEN
    ALTER TABLE `#__redshopb_user` DROP INDEX `#__redshopb_user_fk7`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_user' AND `constraint_name` = '#__redshopb_user_fk8') THEN
    ALTER TABLE `#__redshopb_user` DROP FOREIGN KEY `#__redshopb_user_fk8`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_user' AND `index_name` = '#__redshopb_user_fk8') THEN
    ALTER TABLE `#__redshopb_user` DROP INDEX `#__redshopb_user_fk8`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_user' AND `index_name` = 'idx_joomla_user_id') THEN
    ALTER TABLE `#__redshopb_user` DROP INDEX `idx_joomla_user_id`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_user' AND `index_name` = 'idx_company_id') THEN
    ALTER TABLE `#__redshopb_user` DROP INDEX `idx_company_id`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_user' AND `index_name` = 'idx_department_id') THEN
    ALTER TABLE `#__redshopb_user` DROP INDEX `idx_department_id`;
  END IF;
END//

DELIMITER ;


-- -----------------------------------------------------
-- Table `#__redshopb_usergroup_sales_person_xref`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_usergroup_sales_person_xref_1_5_0`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_usergroup_sales_person_xref_1_5_0`() BEGIN
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

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_usergroup_sales_person_xref' AND `index_name` = 'idx_user_id') THEN
    ALTER TABLE `#__redshopb_usergroup_sales_person_xref` DROP INDEX `idx_user_id`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_usergroup_sales_person_xref' AND `index_name` = 'idx_usergroup_id') THEN
    ALTER TABLE `#__redshopb_usergroup_sales_person_xref` DROP INDEX `idx_usergroup_id`;
  END IF;

END//

DELIMITER ;



-- -----------------------------------------------------
-- Table `#__redshopb_wallet`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_wallet_1_5_0`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_wallet_1_5_0`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_wallet' AND `constraint_name` = '#__redshopb_wallet_fk1') THEN
    ALTER TABLE `#__redshopb_wallet` DROP FOREIGN KEY `#__redshopb_wallet_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_wallet' AND `index_name` = '#__redshopb_wallet_fk1') THEN
    ALTER TABLE `#__redshopb_wallet` DROP INDEX `#__redshopb_wallet_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_wallet' AND `constraint_name` = '#__redshopb_wallet_fk2') THEN
    ALTER TABLE `#__redshopb_wallet` DROP FOREIGN KEY `#__redshopb_wallet_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_wallet' AND `index_name` = '#__redshopb_wallet_fk2') THEN
    ALTER TABLE `#__redshopb_wallet` DROP INDEX `#__redshopb_wallet_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_wallet' AND `constraint_name` = '#__redshopb_wallet_fk3') THEN
    ALTER TABLE `#__redshopb_wallet` DROP FOREIGN KEY `#__redshopb_wallet_fk3`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_wallet' AND `index_name` = '#__redshopb_wallet_fk3') THEN
    ALTER TABLE `#__redshopb_wallet` DROP INDEX `#__redshopb_wallet_fk3`;
  END IF;
END//

DELIMITER ;


-- -----------------------------------------------------
-- Table `#__redshopb_wallet_money`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_wallet_money_1_5_0`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_wallet_money_1_5_0`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_wallet_money' AND `constraint_name` = '#__redshopb_wallet_money_fk1') THEN
    ALTER TABLE `#__redshopb_wallet_money` DROP FOREIGN KEY `#__redshopb_wallet_money_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_wallet_money' AND `index_name` = '#__redshopb_wallet_money_fk1') THEN
    ALTER TABLE `#__redshopb_wallet_money` DROP INDEX `#__redshopb_wallet_money_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_wallet_money' AND `constraint_name` = '#__redshopb_wallet_money_fk2') THEN
    ALTER TABLE `#__redshopb_wallet_money` DROP FOREIGN KEY `#__redshopb_wallet_money_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_wallet_money' AND `index_name` = '#__redshopb_wallet_money_fk2') THEN
    ALTER TABLE `#__redshopb_wallet_money` DROP INDEX `#__redshopb_wallet_money_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_wallet_money' AND `constraint_name` = '#__redshopb_wallet_money_fk3') THEN
    ALTER TABLE `#__redshopb_wallet_money` DROP FOREIGN KEY `#__redshopb_wallet_money_fk3`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_wallet_money' AND `index_name` = '#__redshopb_wallet_money_fk3') THEN
    ALTER TABLE `#__redshopb_wallet_money` DROP INDEX `#__redshopb_wallet_money_fk3`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_wallet_money' AND `constraint_name` = '#__redshopb_wallet_money_fk4') THEN
    ALTER TABLE `#__redshopb_wallet_money` DROP FOREIGN KEY `#__redshopb_wallet_money_fk4`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_wallet_money' AND `index_name` = '#__redshopb_wallet_money_fk4') THEN
    ALTER TABLE `#__redshopb_wallet_money` DROP INDEX `#__redshopb_wallet_money_fk4`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_wallet_money' AND `constraint_name` = '#__redshopb_wallet_money_fk5') THEN
    ALTER TABLE `#__redshopb_wallet_money` DROP FOREIGN KEY `#__redshopb_wallet_money_fk5`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_wallet_money' AND `index_name` = '#__redshopb_wallet_money_fk5') THEN
    ALTER TABLE `#__redshopb_wallet_money` DROP INDEX `#__redshopb_wallet_money_fk5`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_wallet_money' AND `index_name` = 'idx_wallet_id') THEN
    ALTER TABLE `#__redshopb_wallet_money` DROP INDEX `idx_wallet_id`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_wallet_money' AND `index_name` = 'idx_currency_id') THEN
    ALTER TABLE `#__redshopb_wallet_money` DROP INDEX `idx_currency_id`;
  END IF;
END//

DELIMITER ;


-- -----------------------------------------------------
-- Table `#__redshopb_wardrobe`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_wardrobe_1_5_0`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_wardrobe_1_5_0`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_wardrobe' AND `constraint_name` = '#__redshopb_wardrobe_fk1') THEN
    ALTER TABLE `#__redshopb_wardrobe` DROP FOREIGN KEY `#__redshopb_wardrobe_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_wardrobe' AND `index_name` = '#__redshopb_wardrobe_fk1') THEN
    ALTER TABLE `#__redshopb_wardrobe` DROP INDEX `#__redshopb_wardrobe_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_wardrobe' AND `constraint_name` = '#__redshopb_wardrobe_fk2') THEN
    ALTER TABLE `#__redshopb_wardrobe` DROP FOREIGN KEY `#__redshopb_wardrobe_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_wardrobe' AND `index_name` = '#__redshopb_wardrobe_fk2') THEN
    ALTER TABLE `#__redshopb_wardrobe` DROP INDEX `#__redshopb_wardrobe_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_wardrobe' AND `constraint_name` = '#__redshopb_wardrobe_fk3') THEN
    ALTER TABLE `#__redshopb_wardrobe` DROP FOREIGN KEY `#__redshopb_wardrobe_fk3`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_wardrobe' AND `index_name` = '#__redshopb_wardrobe_fk3') THEN
    ALTER TABLE `#__redshopb_wardrobe` DROP INDEX `#__redshopb_wardrobe_fk3`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_wardrobe' AND `constraint_name` = '#__redshopb_wardrobe_fk4') THEN
    ALTER TABLE `#__redshopb_wardrobe` DROP FOREIGN KEY `#__redshopb_wardrobe_fk4`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_wardrobe' AND `index_name` = '#__redshopb_wardrobe_fk4') THEN
    ALTER TABLE `#__redshopb_wardrobe` DROP INDEX `#__redshopb_wardrobe_fk4`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_wardrobe' AND `constraint_name` = '#__redshopb_wardrobe_fk5') THEN
    ALTER TABLE `#__redshopb_wardrobe` DROP FOREIGN KEY `#__redshopb_wardrobe_fk5`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_wardrobe' AND `index_name` = '#__redshopb_wardrobe_fk5') THEN
    ALTER TABLE `#__redshopb_wardrobe` DROP INDEX `#__redshopb_wardrobe_fk5`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_wardrobe' AND `index_name` = 'idx_company_id') THEN
    ALTER TABLE `#__redshopb_wardrobe` DROP INDEX `idx_company_id`;
  END IF;
END//

DELIMITER ;


-- -----------------------------------------------------
-- Table `#__redshopb_wardrobe_department_xref`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_wardrobe_department_xref_1_5_0`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_wardrobe_department_xref_1_5_0`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_wardrobe_department_xref' AND `constraint_name` = '#__redshopb_wardrobe_department_xref_fk1') THEN
    ALTER TABLE `#__redshopb_wardrobe_department_xref` DROP FOREIGN KEY `#__redshopb_wardrobe_department_xref_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_wardrobe_department_xref' AND `index_name` = '#__redshopb_wardrobe_department_xref_fk1') THEN
    ALTER TABLE `#__redshopb_wardrobe_department_xref` DROP INDEX `#__redshopb_wardrobe_department_xref_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_wardrobe_department_xref' AND `constraint_name` = '#__redshopb_wardrobe_department_xref_fk2') THEN
    ALTER TABLE `#__redshopb_wardrobe_department_xref` DROP FOREIGN KEY `#__redshopb_wardrobe_department_xref_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_wardrobe_department_xref' AND `index_name` = '#__redshopb_wardrobe_department_xref_fk2') THEN
    ALTER TABLE `#__redshopb_wardrobe_department_xref` DROP INDEX `#__redshopb_wardrobe_department_xref_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_wardrobe_department_xref' AND `constraint_name` = '#__redshopb_wardrobe_department_xref_fk3') THEN
    ALTER TABLE `#__redshopb_wardrobe_department_xref` DROP FOREIGN KEY `#__redshopb_wardrobe_department_xref_fk3`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_wardrobe_department_xref' AND `index_name` = '#__redshopb_wardrobe_department_xref_fk3') THEN
    ALTER TABLE `#__redshopb_wardrobe_department_xref` DROP INDEX `#__redshopb_wardrobe_department_xref_fk3`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_wardrobe_department_xref' AND `constraint_name` = '#__redshopb_wardrobe_department_xref_fk4') THEN
    ALTER TABLE `#__redshopb_wardrobe_department_xref` DROP FOREIGN KEY `#__redshopb_wardrobe_department_xref_fk4`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_wardrobe_department_xref' AND `index_name` = '#__redshopb_wardrobe_department_xref_fk4') THEN
    ALTER TABLE `#__redshopb_wardrobe_department_xref` DROP INDEX `#__redshopb_wardrobe_department_xref_fk4`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_wardrobe_department_xref' AND `constraint_name` = '#__redshopb_wardrobe_department_xref_fk5') THEN
    ALTER TABLE `#__redshopb_wardrobe_department_xref` DROP FOREIGN KEY `#__redshopb_wardrobe_department_xref_fk5`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_wardrobe_department_xref' AND `index_name` = '#__redshopb_wardrobe_department_xref_fk5') THEN
    ALTER TABLE `#__redshopb_wardrobe_department_xref` DROP INDEX `#__redshopb_wardrobe_department_xref_fk5`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_wardrobe_department_xref' AND `index_name` = 'idx_wardrobe_id') THEN
    ALTER TABLE `#__redshopb_wardrobe_department_xref` DROP INDEX `idx_wardrobe_id`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_wardrobe_department_xref' AND `index_name` = 'idx_department_id') THEN
    ALTER TABLE `#__redshopb_wardrobe_department_xref` DROP INDEX `idx_department_id`;
  END IF;
END//

DELIMITER ;


-- -----------------------------------------------------
-- Table `#__redshopb_wardrobe_product_item_xref`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_wardrobe_product_item_xref_1_5_0`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_wardrobe_product_item_xref_1_5_0`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_wardrobe_product_item_xref' AND `constraint_name` = '#__redshopb_wardrobe_product_item_xref_fk1') THEN
    ALTER TABLE `#__redshopb_wardrobe_product_item_xref` DROP FOREIGN KEY `#__redshopb_wardrobe_product_item_xref_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_wardrobe_product_item_xref' AND `index_name` = '#__redshopb_wardrobe_product_item_xref_fk1') THEN
    ALTER TABLE `#__redshopb_wardrobe_product_item_xref` DROP INDEX `#__redshopb_wardrobe_product_item_xref_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_wardrobe_product_item_xref' AND `constraint_name` = '#__redshopb_wardrobe_product_item_xref_fk2') THEN
    ALTER TABLE `#__redshopb_wardrobe_product_item_xref` DROP FOREIGN KEY `#__redshopb_wardrobe_product_item_xref_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_wardrobe_product_item_xref' AND `index_name` = '#__redshopb_wardrobe_product_item_xref_fk2') THEN
    ALTER TABLE `#__redshopb_wardrobe_product_item_xref` DROP INDEX `#__redshopb_wardrobe_product_item_xref_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_wardrobe_product_item_xref' AND `index_name` = 'idx_wardrobe_id') THEN
    ALTER TABLE `#__redshopb_wardrobe_product_item_xref` DROP INDEX `idx_wardrobe_id`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_wardrobe_product_item_xref' AND `index_name` = 'idx_product_item_id') THEN
    ALTER TABLE `#__redshopb_wardrobe_product_item_xref` DROP INDEX `idx_product_item_id`;
  END IF;
END//

DELIMITER ;


-- -----------------------------------------------------
-- Table `#__redshopb_wardrobe_product_xref`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `#__redshopb_wardrobe_product_xref_1_5_0`;

DELIMITER //
CREATE PROCEDURE `#__redshopb_wardrobe_product_xref_1_5_0`() BEGIN
  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_wardrobe_product_xref' AND `constraint_name` = '#__redshopb_wardrobe_product_xref_fk1') THEN
    ALTER TABLE `#__redshopb_wardrobe_product_xref` DROP FOREIGN KEY `#__redshopb_wardrobe_product_xref_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_wardrobe_product_xref' AND `index_name` = '#__redshopb_wardrobe_product_xref_fk1') THEN
    ALTER TABLE `#__redshopb_wardrobe_product_xref` DROP INDEX `#__redshopb_wardrobe_product_xref_fk1`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`referential_constraints` WHERE `constraint_schema` = DATABASE() AND `table_name` = '#__redshopb_wardrobe_product_xref' AND `constraint_name` = '#__redshopb_wardrobe_product_xref_fk2') THEN
    ALTER TABLE `#__redshopb_wardrobe_product_xref` DROP FOREIGN KEY `#__redshopb_wardrobe_product_xref_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_wardrobe_product_xref' AND `index_name` = '#__redshopb_wardrobe_product_xref_fk2') THEN
    ALTER TABLE `#__redshopb_wardrobe_product_xref` DROP INDEX `#__redshopb_wardrobe_product_xref_fk2`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_wardrobe_product_xref' AND `index_name` = 'idx_wardrobe_id') THEN
    ALTER TABLE `#__redshopb_wardrobe_product_xref` DROP INDEX `idx_wardrobe_id`;
  END IF;

  IF EXISTS (SELECT * FROM `information_schema`.`statistics` WHERE `index_schema` = DATABASE() AND `table_name` = '#__redshopb_wardrobe_product_xref' AND `index_name` = 'idx_product_id') THEN
    ALTER TABLE `#__redshopb_wardrobe_product_xref` DROP INDEX `idx_product_id`;
  END IF;
END//