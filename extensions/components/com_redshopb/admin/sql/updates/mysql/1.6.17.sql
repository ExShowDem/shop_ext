SET FOREIGN_KEY_CHECKS = 0;

-- -----------------------------------------------------
-- Table `#__redshopb_newsletter_list`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `#__redshopb_newsletter_list` (
  `id` INT(10) UNSIGNED NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `alias` VARCHAR(255) NOT NULL,
  `segmentation_query` TEXT NULL,
  `plugin` VARCHAR(255) NULL,
  `plugin_id` INT(11) NULL,
  `state` TINYINT(4) NOT NULL DEFAULT '1',
  `checked_out` INT(11) NULL,
  `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` INT(11) NULL,
  `created_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` INT(11) NULL,
  `modified_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `#__redshopb_newsletter`
-- -----------------------------------------------------
ALTER TABLE `#__redshopb_newsletter`
  ADD COLUMN `newsletter_list_id` INT(10) UNSIGNED NULL AFTER `alias`,
  ADD COLUMN `plugin` VARCHAR(255) NULL AFTER `template_id`,
  ADD COLUMN `plugin_id` INT(11) NULL AFTER `plugin`,
  ADD INDEX `#__rs_newsletter_fk5` (`newsletter_list_id` ASC),
  ADD CONSTRAINT `#__rs_newsletter_fk5`
    FOREIGN KEY (`newsletter_list_id`)
    REFERENCES `#__redshopb_newsletter_list` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE;


-- -----------------------------------------------------
-- Table `#__redshopb_newsletter_user_xref`
-- -----------------------------------------------------
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
ENGINE = InnoDB;

SET FOREIGN_KEY_CHECKS = 1;
