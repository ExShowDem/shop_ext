SET FOREIGN_KEY_CHECKS=0;

-- -----------------------------------------------------
-- Table `#__redshopb_sync`
-- -----------------------------------------------------

ALTER TABLE `#__redshopb_sync`
   ADD INDEX `idx_reference_local_id` (`reference` ASC, `local_id` ASC);

SET FOREIGN_KEY_CHECKS=1;
