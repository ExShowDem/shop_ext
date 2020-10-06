SET FOREIGN_KEY_CHECKS = 0;
SET UNIQUE_CHECKS = 0;

-- -----------------------------------------------------
-- Table `#__redshopb_template`
-- -----------------------------------------------------
ALTER TABLE `#__redshopb_template`
  ADD COLUMN `alias` VARCHAR(255) NOT NULL AFTER `name`,
  ADD UNIQUE INDEX `idx_alias` (`alias` ASC);


-- -----------------------------------------------------
-- Table `#__redshopb_stockroom`
-- -----------------------------------------------------
ALTER TABLE `#__redshopb_stockroom`
  CHANGE COLUMN `order` `ordering` INT NULL COMMENT 'Decides the order selection of stockrooms of the company';


-- -----------------------------------------------------
-- Table `#__redshopb_newsletter`
-- -----------------------------------------------------
CALL `#__redshopb_newsletter_1_6_10`();

DROP PROCEDURE `#__redshopb_newsletter_1_6_10`;

ALTER TABLE `#__redshopb_newsletter`
  ADD COLUMN `alias` VARCHAR(255) NOT NULL AFTER `name`,
  ADD `checked_out` INT(11) NULL,
  ADD `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  ADD `created_by` INT(11) NULL,
  ADD `created_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  ADD `modified_by` INT(11) NULL,
  ADD `modified_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  ADD PRIMARY KEY (`id`),
  ADD INDEX `#__rs_newsletter_fk1` (`template_id` ASC),
  ADD UNIQUE INDEX `idx_alias` (`alias` ASC),
  ADD INDEX `#__rs_newsletter_fk2` (`checked_out` ASC),
  ADD INDEX `#__rs_newsletter_fk3` (`created_by` ASC),
  ADD INDEX `#__rs_newsletter_fk4` (`modified_by` ASC),
  ADD CONSTRAINT `#__rs_newsletter_fk1`
    FOREIGN KEY (`template_id`)
    REFERENCES `#__redshopb_template` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_newsletter_fk2`
    FOREIGN KEY (`checked_out`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_newsletter_fk3`
    FOREIGN KEY (`created_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_newsletter_fk4`
    FOREIGN KEY (`modified_by`)
    REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE;

SET FOREIGN_KEY_CHECKS = 1;
SET UNIQUE_CHECKS = 1;