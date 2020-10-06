SET FOREIGN_KEY_CHECKS = 0;
SET UNIQUE_CHECKS=0;

-- -----------------------------------------------------
-- Table `#__redshopb_category`
-- -----------------------------------------------------
UPDATE `#__redshopb_category`
  SET `parent_id` = NULL, `path` = ''
  WHERE 
    `parent_id` NOT IN (
      SELECT
        `id`
      FROM
        (
          SELECT
            `id`
          FROM
            `#__redshopb_category`
        ) AS `temp`
    )
    AND `alias` = 'root';

-- -----------------------------------------------------
-- Table `#__redshopb_tag`
-- -----------------------------------------------------
UPDATE `#__redshopb_tag`
  SET `parent_id` = NULL, `path` = ''
  WHERE 
    `parent_id` NOT IN (
      SELECT
        `id`
      FROM
        (
          SELECT
            `id`
          FROM
            `#__redshopb_tag`
        ) AS `temp`
    )
    AND `alias` = 'root';

-- -----------------------------------------------------
-- Table `#__redshopb_company`
-- -----------------------------------------------------
UPDATE `#__redshopb_company`
  SET `parent_id` = NULL, `path` = ''
  WHERE 
    `parent_id` NOT IN (
      SELECT
        `id`
      FROM
        (
          SELECT
            `id`
          FROM
            `#__redshopb_company`
        ) AS `temp`
    )
    AND `alias` = 'root';

-- -----------------------------------------------------
-- Table `#__redshopb_department`
-- -----------------------------------------------------
UPDATE `#__redshopb_department`
  SET `parent_id` = NULL, `path` = ''
  WHERE 
    `parent_id` NOT IN (
      SELECT
        `id`
      FROM
        (
          SELECT
            `id`
          FROM
            `#__redshopb_department`
        ) AS `temp`
    )
    AND `alias` = 'root';

-- -----------------------------------------------------
-- Table `#__redshopb_cron`
-- -----------------------------------------------------
ALTER TABLE `#__redshopb_cron`
  MODIFY COLUMN `parent_id` INT(11) NULL DEFAULT '0';

UPDATE `#__redshopb_cron`
  SET `parent_id` = NULL, `path` = ''
  WHERE 
    `parent_id` NOT IN (
      SELECT
        `id`
      FROM
        (
          SELECT
            `id`
          FROM
            `#__redshopb_cron`
        ) AS `temp`
    )
    AND `alias` = 'root';


SET FOREIGN_KEY_CHECKS = 1;
SET UNIQUE_CHECKS=1;
