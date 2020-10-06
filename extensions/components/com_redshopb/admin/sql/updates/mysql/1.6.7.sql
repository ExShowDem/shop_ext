SET FOREIGN_KEY_CHECKS = 0;
SET UNIQUE_CHECKS=0;

-- -----------------------------------------------------
-- Table `#__redshopb_category`
-- -----------------------------------------------------
CALL `#__redshopb_category_1_6_7`();

DROP PROCEDURE `#__redshopb_category_1_6_7`;

-- Sets temporal aliases to null values
UPDATE `#__redshopb_category`
  SET `alias` = CONCAT('alias-', id)
  WHERE `alias` IS NULL OR `alias` = '';

UPDATE `#__redshopb_category`
  SET `parent_id` = 0
  WHERE `parent_id` IS NULL;

-- Sets temporal aliases to duplicate values
UPDATE
  `#__redshopb_category`
SET
  `alias` = CONCAT('alias-', `id`)
WHERE
  `id` IN (
    SELECT
      `id`
    FROM
      (
        SELECT
          `a1`.`id`
        FROM
          `#__redshopb_category` AS `a1`
        WHERE
          EXISTS (
            SELECT
              1
            FROM
              `#__redshopb_category` AS `a2`
            GROUP BY
              `a2`.`parent_id`, `a2`.`alias`
            HAVING
              COUNT(*) > 1
              AND `a1`.`parent_id` = `a2`.`parent_id`
              AND `a1`.`alias` = `a2`.`alias`
          )
      ) AS `#__redshopb_category_tmp`
  );

ALTER TABLE `#__redshopb_category`
  MODIFY COLUMN `state` TINYINT(4) NOT NULL DEFAULT '1',
  ADD UNIQUE INDEX `idx_alias` (`parent_id` ASC, `alias` ASC);

UPDATE `#__redshopb_category`
  SET `parent_id` = (
    SELECT
      `id`
        FROM
          (
            SELECT
              `id`
            FROM
              `#__redshopb_category`
            WHERE
              `parent_id` = 0
        ) AS `temp`
  LIMIT
    0, 1
   )
  WHERE 
    (`parent_id` IS NULL
    OR `parent_id` = 0
    OR `parent_id` NOT IN (
      SELECT
        `id`
      FROM
        (
          SELECT
            `id`
          FROM
            `#__redshopb_category`
        ) AS `temp`
    ))
    AND `id` <>  (
    SELECT
      `id`
        FROM
          (
            SELECT
              `id`
            FROM
              `#__redshopb_category`
            WHERE
              `alias` = 'root'
        ) AS `temp`
  LIMIT
    0, 1
   );
    

-- -----------------------------------------------------
-- Table `#__redshopb_customer_discount_group`
-- -----------------------------------------------------
ALTER TABLE `#__redshopb_customer_discount_group`
  MODIFY COLUMN `state` TINYINT(4) NOT NULL DEFAULT '1';


-- -----------------------------------------------------
-- Table `#__redshopb_customer_price_group`
-- -----------------------------------------------------
ALTER TABLE `#__redshopb_customer_price_group`
  MODIFY COLUMN `state` TINYINT(4) NOT NULL DEFAULT '1';


-- -----------------------------------------------------
-- Table `#__redshopb_tag`
-- -----------------------------------------------------
ALTER TABLE `#__redshopb_tag`
  ADD COLUMN `parent_id` INT(10) UNSIGNED NULL AFTER `company_id`,
  ADD COLUMN `lft` INT(11) NOT NULL DEFAULT 0 AFTER `parent_id`,
  ADD COLUMN `rgt` INT(11) NOT NULL DEFAULT 0 AFTER `lft`,
  ADD COLUMN `level` INT(11) NOT NULL DEFAULT 0 AFTER `rgt`,
  ADD COLUMN `path` VARCHAR(255) NOT NULL AFTER `level`,
  MODIFY COLUMN `state` TINYINT(4) NOT NULL DEFAULT '1' AFTER `level`,
  ADD INDEX `#__rs_tag_fk5` (`parent_id` ASC),
  ADD CONSTRAINT `#__rs_tag_fk5`
    FOREIGN KEY (`parent_id`)
    REFERENCES `#__redshopb_tag` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION;

CALL `#__redshopb_tag_1_6_7`();

DROP PROCEDURE `#__redshopb_tag_1_6_7`;

UPDATE `#__redshopb_tag`
  SET `parent_id` = (
    SELECT
      `id`
        FROM
          (
            SELECT
              `id`
            FROM
              `#__redshopb_tag`
            WHERE
              `parent_id` = 0
        ) AS `temp`
  LIMIT
    0, 1
   )
  WHERE `parent_id` IS NULL;

-- Sets temporal aliases to null values
UPDATE `#__redshopb_tag`
  SET `alias` = CONCAT('alias-', id)
  WHERE `alias` IS NULL OR `alias` = '';

-- Sets temporal aliases to duplicate values
UPDATE
  `#__redshopb_tag`
SET
  `alias` = CONCAT('alias-', `id`)
WHERE
  `id` IN (
    SELECT
      `id`
    FROM
      (
        SELECT
          `a1`.`id`
        FROM
          `#__redshopb_tag` AS `a1`
        WHERE
          EXISTS (
            SELECT
              1
            FROM
              `#__redshopb_tag` AS `a2`
            GROUP BY
              `a2`.`parent_id`, `a2`.`alias`
            HAVING
              COUNT(*) > 1
              AND `a1`.`parent_id` = `a2`.`parent_id`
              AND `a1`.`alias` = `a2`.`alias`
          )
      ) AS `#__redshopb_tag_tmp`
  );

ALTER TABLE `#__redshopb_tag`
  ADD UNIQUE INDEX `idx_alias` (`parent_id` ASC, `alias` ASC);

UPDATE `#__redshopb_tag`
  SET `parent_id` = (
    SELECT
      `id`
        FROM
          (
            SELECT
              `id`
            FROM
              `#__redshopb_tag`
            WHERE
              `parent_id` = 0
        ) AS `temp`
  LIMIT
    0, 1
   )
  WHERE 
    (`parent_id` IS NULL
    OR `parent_id` = 0
    OR `parent_id` NOT IN (
      SELECT
        `id`
      FROM
        (
          SELECT
            `id`
          FROM
            `#__redshopb_tag`
        ) AS `temp`
    ))
    AND `id` <>  (
    SELECT
      `id`
        FROM
          (
            SELECT
              `id`
            FROM
              `#__redshopb_tag`
            WHERE
              `alias` = 'root'
        ) AS `temp`
  LIMIT
    0, 1
   );

-- -----------------------------------------------------
-- Table `#__redshopb_product_attribute_value`
-- -----------------------------------------------------
CALL `#__redshopb_product_attribute_value_1_6_7`();

DROP PROCEDURE `#__redshopb_product_attribute_value_1_6_7`;

ALTER TABLE `#__redshopb_product_attribute_value`
  ADD INDEX `#__rs_prod_av_fk1` (`product_attribute_id` ASC),
  ADD CONSTRAINT `#__rs_prod_av_fk1`
    FOREIGN KEY (`product_attribute_id`)
    REFERENCES `#__redshopb_product_attribute` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE;


-- -----------------------------------------------------
-- Table `#__redshopb_media`
-- -----------------------------------------------------
ALTER TABLE `#__redshopb_media`
  MODIFY COLUMN `state` TINYINT(4) NOT NULL DEFAULT '1';


-- -----------------------------------------------------
-- Table `#__redshopb_order`
-- -----------------------------------------------------
CALL `#__redshopb_order_1_6_7`();

DROP PROCEDURE `#__redshopb_order_1_6_7`;

ALTER TABLE `#__redshopb_order`
  ADD INDEX `#__rs_order_fk6` (`currency_id` ASC),
  ADD INDEX `idx_currency` (`currency` ASC),
  ADD CONSTRAINT `#__rs_order_fk6`
    FOREIGN KEY (`currency_id`)
    REFERENCES `#__redshopb_currency` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION;


-- -----------------------------------------------------
-- Table `#__redshopb_collection`
-- -----------------------------------------------------
ALTER TABLE `#__redshopb_collection`
  MODIFY COLUMN `state` TINYINT(4) NOT NULL DEFAULT '1';


-- -----------------------------------------------------
-- Table `#__redshopb_product_discount`
-- -----------------------------------------------------
ALTER TABLE `#__redshopb_product_discount`
  MODIFY COLUMN `sales_type` ENUM('all_debtor', 'debtor_discount_group', 'debtor') NOT NULL DEFAULT 'all_debtor',
  MODIFY COLUMN `state` TINYINT(4) NOT NULL DEFAULT '1';


-- -----------------------------------------------------
-- Table `#__redshopb_product_discount_group`
-- -----------------------------------------------------
ALTER TABLE `#__redshopb_product_discount_group`
  MODIFY COLUMN `state` TINYINT(4) NOT NULL DEFAULT '1';


-- -----------------------------------------------------
-- Table `#__redshopb_collection_product_xref`
-- -----------------------------------------------------
ALTER TABLE `#__redshopb_collection_product_xref`
  MODIFY COLUMN `state` TINYINT(4) NOT NULL DEFAULT '1';


-- -----------------------------------------------------
-- Table `#__redshopb_conversion`
-- -----------------------------------------------------
-- Sets temporal aliases to null values
UPDATE `#__redshopb_conversion`
  SET `alias` = CONCAT('alias-', id)
  WHERE `alias` IS NULL OR `alias` = '';

-- Sets temporal aliases to duplicate values
UPDATE
  `#__redshopb_conversion`
SET
  `alias` = CONCAT('alias-', `id`)
WHERE
  `id` IN (
    SELECT
      `id`
    FROM
      (
        SELECT
          `a1`.`id`
        FROM
          `#__redshopb_conversion` AS `a1`
        WHERE
          EXISTS (
            SELECT
              1
            FROM
              `#__redshopb_conversion` AS `a2`
            GROUP BY
              `a2`.`alias`
            HAVING
              COUNT(*) > 1
              AND `a1`.`alias` = `a2`.`alias`
          )
      ) AS `#__redshopb_conversion_tmp`
  );

ALTER TABLE `#__redshopb_conversion`
  ADD UNIQUE INDEX `idx_alias` (`alias` ASC);


-- -----------------------------------------------------
-- Table `#__redshopb_product_price`
-- -----------------------------------------------------
CALL `#__redshopb_product_price_1_6_7`();

DROP PROCEDURE `#__redshopb_product_price_1_6_7`;


-- -----------------------------------------------------
-- Table `#__redshopb_company`
-- -----------------------------------------------------
CALL `#__redshopb_company_1_6_7`();

DROP PROCEDURE `#__redshopb_company_1_6_7`;

-- Sets temporal aliases to null values
UPDATE `#__redshopb_company`
  SET `alias` = CONCAT('alias-', id)
  WHERE `alias` IS NULL OR `alias` = '';

UPDATE `#__redshopb_company`
  SET `parent_id` = 0
  WHERE `parent_id` IS NULL;

-- Sets temporal aliases to duplicate values
UPDATE
  `#__redshopb_company`
SET
  `alias` = CONCAT('alias-', `id`)
WHERE
  `id` IN (
    SELECT
      `id`
    FROM
      (
        SELECT
          `a1`.`id`
        FROM
          `#__redshopb_company` AS `a1`
        WHERE
          EXISTS (
            SELECT
              1
            FROM
              `#__redshopb_company` AS `a2`
            GROUP BY
              `a2`.`parent_id`, `a2`.`alias`
            HAVING
              COUNT(*) > 1
              AND `a1`.`parent_id` = `a2`.`parent_id`
              AND `a1`.`alias` = `a2`.`alias`
          )
      ) AS `#__redshopb_company_tmp`
  );

ALTER TABLE `#__redshopb_company`
  ADD UNIQUE INDEX `idx_alias` (`parent_id` ASC, `alias` ASC);

UPDATE `#__redshopb_company`
  SET `parent_id` = (
    SELECT
      `id`
        FROM
          (
            SELECT
              `id`
            FROM
              `#__redshopb_company`
            WHERE
              `parent_id` = 0
        ) AS `temp`
  LIMIT
    0, 1
   )
  WHERE 
    (`parent_id` IS NULL
    OR `parent_id` = 0
    OR `parent_id` NOT IN (
      SELECT
        `id`
      FROM
        (
          SELECT
            `id`
          FROM
            `#__redshopb_company`
        ) AS `temp`
    ))
    AND `id` <>  (
    SELECT
      `id`
        FROM
          (
            SELECT
              `id`
            FROM
              `#__redshopb_company`
            WHERE
              `alias` = 'root'
        ) AS `temp`
  LIMIT
    0, 1
   );


-- -----------------------------------------------------
-- Table `#__redshopb_product_attribute`
-- -----------------------------------------------------
CALL `#__redshopb_product_attribute_1_6_7`();

DROP PROCEDURE `#__redshopb_product_attribute_1_6_7`;

UPDATE `#__redshopb_product_attribute`
  SET `alias` = CONCAT('alias-', id)
  WHERE `alias` IS NULL OR `alias` = '';

-- Sets temporal aliases to duplicate values
UPDATE
  `#__redshopb_product_attribute`
SET
  `alias` = CONCAT('alias-', `id`)
WHERE
  `id` IN (
    SELECT
      `id`
    FROM
      (
        SELECT
          `a1`.`id`
        FROM
          `#__redshopb_product_attribute` AS `a1`
        WHERE
          EXISTS (
            SELECT
              1
            FROM
              `#__redshopb_product_attribute` AS `a2`
            GROUP BY
              `a2`.`product_id`, `a2`.`alias`
            HAVING
              COUNT(*) > 1
              AND `a1`.`product_id` = `a2`.`product_id`
              AND `a1`.`alias` = `a2`.`alias`
          )
      ) AS `#__redshopb_product_attribute_tmp`
  );

ALTER TABLE `#__redshopb_product_attribute`
  ADD UNIQUE INDEX `idx_alias` (`product_id` ASC, `alias` ASC);


-- -----------------------------------------------------
-- Table `#__redshopb_department`
-- -----------------------------------------------------
-- Sets temporal aliases to null values
UPDATE `#__redshopb_department`
  SET `alias` = CONCAT('alias-', id)
  WHERE `alias` IS NULL OR `alias` = '';

-- Sets temporal aliases to duplicate values
UPDATE
  `#__redshopb_department`
SET
  `alias` = CONCAT('alias-', `id`)
WHERE
  `id` IN (
    SELECT
      `id`
    FROM
      (
        SELECT
          `a1`.`id`
        FROM
          `#__redshopb_department` AS `a1`
        WHERE
          EXISTS (
            SELECT
              1
            FROM
              `#__redshopb_department` AS `a2`
            GROUP BY
              `a2`.`parent_id`, `a2`.`alias`
            HAVING
              COUNT(*) > 1
              AND `a1`.`parent_id` = `a2`.`parent_id`
              AND `a1`.`alias` = `a2`.`alias`
          )
      ) AS `#__redshopb_department_tmp`
  );

ALTER TABLE `#__redshopb_department`
  ADD UNIQUE INDEX `idx_alias` (`parent_id` ASC, `alias` ASC);

UPDATE `#__redshopb_department`
  SET `parent_id` = (
    SELECT
      `id`
        FROM
          (
            SELECT
              `id`
            FROM
              `#__redshopb_department`
            WHERE
              `parent_id` = 0
        ) AS `temp`
  LIMIT
    0, 1
   )
  WHERE 
    (`parent_id` IS NULL
    OR `parent_id` = 0
    OR `parent_id` NOT IN (
      SELECT
        `id`
      FROM
        (
          SELECT
            `id`
          FROM
            `#__redshopb_department`
        ) AS `temp`
    ))
    AND `id` <>  (
    SELECT
      `id`
        FROM
          (
            SELECT
              `id`
            FROM
              `#__redshopb_department`
            WHERE
              `alias` = 'root'
        ) AS `temp`
  LIMIT
    0, 1
   );


-- -----------------------------------------------------
-- Table `#__redshopb_cron`
-- -----------------------------------------------------
-- Sets temporal aliases to null values
UPDATE `#__redshopb_cron`
  SET `alias` = CONCAT('alias-', id)
  WHERE `alias` IS NULL OR `alias` = '';

-- Sets temporal aliases to duplicate values
UPDATE
  `#__redshopb_cron`
SET
  `alias` = CONCAT('alias-', `id`)
WHERE
  `id` IN (
    SELECT
      `id`
    FROM
      (
        SELECT
          `a1`.`id`
        FROM
          `#__redshopb_cron` AS `a1`
        WHERE
          EXISTS (
            SELECT
              1
            FROM
              `#__redshopb_cron` AS `a2`
            GROUP BY
              `a2`.`alias`
            HAVING
              COUNT(*) > 1
              AND `a1`.`alias` = `a2`.`alias`
          )
      ) AS `#__redshopb_cron_tmp`
  );

ALTER TABLE `#__redshopb_cron`
  ADD COLUMN `plugin` VARCHAR(255) NULL AFTER `name`,
  ADD COLUMN `parent_alias` VARCHAR(255) NOT NULL DEFAULT 'root' AFTER `path`,
  ADD UNIQUE INDEX `idx_alias` (`alias` ASC);

UPDATE `#__redshopb_cron`
SET `plugin` = 'fengel'
WHERE
  `name` IN ('GetCustomer', 'GetEndCustomer', 'GetDepartment', 'GetCategory', 'GetProduct', 'GetType', 'GetAttribute', 'GetItem', 'GetWardrobe', 'GetStock', 'GetCustomerDiscountGroup', 'GetCustomerPriceGroup', 'GetProductDiscountGroup', 'GetProductDiscountGroupXref', 'GetProductPicture', 'GetProductPrice', 'GetProductDiscount', 'GetUser', 'GetItemVariantTreshold', 'GetWashCareSpec', 'GetShiptoAddress', 'GetSalesPerson', 'GetItemGroup', 'GetRedItemDetail', 'GetLogos', 'GetFeeSetup', 'GetSizes', 'GetColors', 'GetComposition', 'SetSalesOrder');

UPDATE `#__redshopb_cron`
SET `plugin` = 'ftpsync'
WHERE
  `name` IN ('FTPSync');

UPDATE `#__redshopb_cron`
SET `plugin` = 'pim'
WHERE
  `name` IN ('PimGetProduct', 'PimGetDepartmentCode', 'PimGetBrands', 'PimGetStockUnits', 'PimGetProductType', 'PimGetCategory');

DELETE FROM `#__redshopb_cron`
  WHERE `name` = 'ROOT';

UPDATE `#__redshopb_cron`
  SET `id` = (
    SELECT
      `tempid`
    FROM
      (
        SELECT
          MAX(`id`) + 1 AS `tempid`
        FROM
          `#__redshopb_cron`
      ) AS `temp`
)
WHERE
  `id` = 1;

UPDATE `#__redshopb_cron`
  SET `parent_id` = (
    SELECT
      `tempid`
    FROM
      (
        SELECT
          MAX(`id`) `tempid`
        FROM
          `#__redshopb_cron`
      ) AS `temp`
)
WHERE
  `parent_id` = 1;

INSERT INTO `#__redshopb_cron` (`id`, `name`, `parent_id`, `state`, `start_time`, `finish_time`, `next_start`, `lft`, `rgt`, `level`, `alias`, `path`, `execute_sync`, `mask_time`, `offset_time`, `params`, `checked_out`, `checked_out_time`) VALUES
  (1, 'ROOT', 0, 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 'root', 'root', 0, '', '', '', 0, '0000-00-00 00:00:00');

UPDATE `#__redshopb_cron`
  SET `parent_id` = (
    SELECT
      `id`
        FROM
          (
            SELECT
              `id`
            FROM
              `#__redshopb_cron`
            WHERE
              `parent_id` = 0
        ) AS `temp`
  LIMIT
    0, 1
   )
  WHERE 
    (`parent_id` IS NULL
    OR `parent_id` = 0
    OR `parent_id` NOT IN (
      SELECT
        `id`
      FROM
        (
          SELECT
            `id`
          FROM
            `#__redshopb_cron`
        ) AS `temp`
    ))
    AND `id` <>  (
    SELECT
      `id`
        FROM
          (
            SELECT
              `id`
            FROM
              `#__redshopb_cron`
            WHERE
              `alias` = 'root'
        ) AS `temp`
  LIMIT
    0, 1
   );

UPDATE
  `#__redshopb_cron` AS `c`, `#__redshopb_cron` AS `cp`
SET
  `c`.`parent_alias` = NULL
WHERE
  `c`.`parent_id` = 0;

CALL `#__redshopb_cron_1_6_7`();

DROP PROCEDURE `#__redshopb_cron_1_6_7`;

-- -----------------------------------------------------
-- Table `#__redshopb_product`
-- -----------------------------------------------------
ALTER TABLE `#__redshopb_product`
  ADD `category_id` INT(10) UNSIGNED NULL COMMENT 'Main category of the product' AFTER `company_id`,
  ADD INDEX `#__rs_prod_fk7` (`category_id` ASC),
  ADD CONSTRAINT `#__rs_prod_fk7`
    FOREIGN KEY (`category_id`)
    REFERENCES `#__redshopb_category` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION;

-- Sets temporal aliases to null values
UPDATE `#__redshopb_product`
  SET `alias` = CONCAT('alias-', id)
  WHERE `alias` IS NULL OR `alias` = '';

-- Sets temporal aliases to duplicate values
UPDATE
  `#__redshopb_product`
SET
  `alias` = CONCAT('alias-', `id`)
WHERE
  `id` IN (
    SELECT
      `id`
    FROM
      (
        SELECT
          `a1`.`id`
        FROM
          `#__redshopb_product` AS `a1`
        WHERE
          EXISTS (
            SELECT
              1
            FROM
              `#__redshopb_product` AS `a2`
            GROUP BY
              `a2`.`company_id`, `a2`.`category_id`, `a2`.`alias`
            HAVING
              COUNT(*) > 1
              AND `a1`.`company_id` = `a2`.`company_id`
              AND `a1`.`category_id` = `a2`.`category_id`
              AND `a1`.`alias` = `a2`.`alias`
          )
      ) AS `#__redshopb_product_tmp`
  );

ALTER TABLE `#__redshopb_product`
  ADD UNIQUE INDEX `idx_alias` (`company_id` ASC, `category_id` ASC, `alias` ASC);


-- -----------------------------------------------------
-- Table `#__redshopb_type`
-- -----------------------------------------------------
CALL `#__redshopb_type_1_6_7`();

DROP PROCEDURE `#__redshopb_type_1_6_7`;

-- Sets temporal aliases to null values
UPDATE `#__redshopb_type`
  SET `alias` = CONCAT('alias-', id)
  WHERE `alias` IS NULL OR `alias` = '';

-- Sets temporal aliases to duplicate values
UPDATE
  `#__redshopb_type`
SET
  `alias` = CONCAT('alias-', `id`)
WHERE
  `id` IN (
    SELECT
      `id`
    FROM
      (
        SELECT
          `a1`.`id`
        FROM
          `#__redshopb_type` AS `a1`
        WHERE
          EXISTS (
            SELECT
              1
            FROM
              `#__redshopb_type` AS `a2`
            GROUP BY
              `a2`.`alias`
            HAVING
              COUNT(*) > 1
              AND `a1`.`alias` = `a2`.`alias`
          )
      ) AS `#__redshopb_type_tmp`
  );

ALTER TABLE `#__redshopb_type`
  ADD UNIQUE INDEX `idx_alias` (`alias` ASC);


-- -----------------------------------------------------
-- Table `#__redshopb_product_category_xref`
-- -----------------------------------------------------

UPDATE
  `#__redshopb_product` AS `p`, `#__redshopb_product_category_xref` AS `pcx`
SET
  `p`.`category_id` = `pcx`.`category_id`
WHERE
  `pcx`.`product_main` = 1
  AND `p`.`id` = `pcx`.`product_id`;


ALTER TABLE `#__redshopb_product_category_xref`
   DROP COLUMN `product_main`;


-- -----------------------------------------------------
-- Table `#__redshopb_stockroom`
-- -----------------------------------------------------
-- Sets temporal aliases to null values
UPDATE `#__redshopb_stockroom`
  SET `alias` = CONCAT('alias-', id)
  WHERE `alias` IS NULL OR `alias` = '';

-- Sets temporal aliases to duplicate values
UPDATE
  `#__redshopb_stockroom`
SET
  `alias` = CONCAT('alias-', `id`)
WHERE
  `id` IN (
    SELECT
      `id`
    FROM
      (
        SELECT
          `a1`.`id`
        FROM
          `#__redshopb_stockroom` AS `a1`
        WHERE
          EXISTS (
            SELECT
              1
            FROM
              `#__redshopb_stockroom` AS `a2`
            GROUP BY
              `a2`.`company_id`, `a2`.`alias`
            HAVING
              COUNT(*) > 1
              AND `a1`.`company_id` = `a2`.`company_id`
              AND `a1`.`alias` = `a2`.`alias`
          )
      ) AS `#__redshopb_stockroom_tmp`
  );

ALTER TABLE `#__redshopb_stockroom`
  ADD UNIQUE INDEX `idx_alias` (`company_id` ASC, `alias` ASC);


-- -----------------------------------------------------
-- Table `#__redshopb_field`
-- -----------------------------------------------------
CALL `#__redshopb_field_1_6_7`();

DROP PROCEDURE `#__redshopb_field_1_6_7`;

-- Sets temporal aliases to null values
UPDATE `#__redshopb_field`
  SET `alias` = CONCAT('alias-', id)
  WHERE `alias` IS NULL OR `alias` = '';

-- Sets temporal aliases to duplicate values
UPDATE
  `#__redshopb_field`
SET
  `alias` = CONCAT('alias-', `id`)
WHERE
  `id` IN (
    SELECT
      `id`
    FROM
      (
        SELECT
          `a1`.`id`
        FROM
          `#__redshopb_field` AS `a1`
        WHERE
          EXISTS (
            SELECT
              1
            FROM
              `#__redshopb_field` AS `a2`
            GROUP BY
              `a2`.`alias`
            HAVING
              COUNT(*) > 1
              AND `a1`.`alias` = `a2`.`alias`
          )
      ) AS `#__redshopb_field_tmp`
  );

ALTER TABLE `#__redshopb_field`
  ADD UNIQUE INDEX `idx_alias` (`alias` ASC);


-- -----------------------------------------------------
-- Table `#__redshopb_favoritelist`
-- -----------------------------------------------------
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
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_favlist_fk2`
    FOREIGN KEY (`department_id`)
    REFERENCES `#__redshopb_department` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `#__rs_favlist_fk3`
    FOREIGN KEY (`user_id`)
    REFERENCES `#__redshopb_user` (`id`)
    ON DELETE RESTRICT
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
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `#__redshopb_favoritelist_product_xref`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `#__redshopb_favoritelist_product_xref` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `favoritelist_id` INT(11) UNSIGNED NOT NULL,
  `product_id` INT(10) UNSIGNED NOT NULL,
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
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `#__redshopb_favoritelist_product_item_xref`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `#__redshopb_favoritelist_product_item_xref` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `favoritelist_id` INT(11) UNSIGNED NOT NULL,
  `product_item_id` INT(10) UNSIGNED NOT NULL,
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
ENGINE = InnoDB;



SET FOREIGN_KEY_CHECKS = 1;
SET UNIQUE_CHECKS=1;
