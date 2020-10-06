-- -----------------------------------------------------
-- Table `#__redshopb_favoritelist`
-- -----------------------------------------------------
CALL `#__redshopb_favoritelist_1_6_16_2`();

DROP PROCEDURE IF EXISTS `#__redshopb_favoritelist_1_6_16_2`;

ALTER TABLE `#__redshopb_favoritelist`
  ADD INDEX `#__rs_favlist_fk1` (`company_id` ASC),
  ADD INDEX `#__rs_favlist_fk2` (`department_id` ASC),
  ADD INDEX `#__rs_favlist_fk3` (`user_id` ASC),
  ADD CONSTRAINT `#__rs_favlist_fk1`
    FOREIGN KEY (`company_id`)
    REFERENCES `#__redshopb_company` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_favlist_fk2`
    FOREIGN KEY (`department_id`)
    REFERENCES `#__redshopb_department` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_favlist_fk3`
    FOREIGN KEY (`user_id`)
    REFERENCES `#__redshopb_user` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE;
