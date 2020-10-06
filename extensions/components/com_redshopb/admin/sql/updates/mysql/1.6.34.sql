SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;

-- -----------------------------------------------------
-- Table `#__redshopb_tag`
-- -----------------------------------------------------
CALL `#__redshopb_tag_1_6_34`();

DROP PROCEDURE IF EXISTS `#__redshopb_tag_1_6_34`;

ALTER TABLE `#__redshopb_tag`
  ADD INDEX `#__rs_tag_fk5` (`parent_id` ASC),
  ADD CONSTRAINT `#__rs_tag_fk5`
    FOREIGN KEY (`parent_id`)
    REFERENCES `#__redshopb_tag` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE;

SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
