SET FOREIGN_KEY_CHECKS = 0;

-- -----------------------------------------------------
-- Table `#__redshopb_offer`
-- -----------------------------------------------------
CALL `#__redshopb_offer_1_6_92`();

DROP PROCEDURE IF EXISTS `#__redshopb_offer_1_6_92`;

ALTER TABLE `#__redshopb_offer`
  ADD INDEX `#__rs_offer_fk1` (`company_id` ASC),
  ADD INDEX `#__rs_offer_fk2` (`department_id` ASC),
  ADD INDEX `#__rs_offer_fk3` (`user_id` ASC),
  ADD INDEX `#__rs_offer_fk8` (`collection_id` ASC),
  ADD CONSTRAINT `#__rs_offer_fk1`
    FOREIGN KEY (`company_id`)
    REFERENCES `#__redshopb_company` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_offer_fk2`
    FOREIGN KEY (`department_id`)
    REFERENCES `#__redshopb_department` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_offer_fk3`
    FOREIGN KEY (`user_id`)
    REFERENCES `#__redshopb_user` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  ADD CONSTRAINT `#__rs_offer_fk8`
    FOREIGN KEY (`collection_id`)
    REFERENCES `#__redshopb_collection` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE;

-- -----------------------------------------------------
-- Table `#__redshopb_order`
-- -----------------------------------------------------
CALL `#__redshopb_order_1_6_92`();

DROP PROCEDURE IF EXISTS `#__redshopb_order_1_6_92`;

ALTER TABLE `#__redshopb_order`
	ADD INDEX `#__rs_order_fk8` (`shipping_rate_id` ASC),
	ADD INDEX `#__rs_order_fk9` (`offer_id` ASC);


-- -----------------------------------------------------
-- Table `#__redshopb_order_item`
-- -----------------------------------------------------
CALL `#__redshopb_order_item_1_6_92`();

DROP PROCEDURE IF EXISTS `#__redshopb_order_item_1_6_92`;

ALTER TABLE `#__redshopb_order_item`
	ADD INDEX `#__rs_orderitem_fk3` (`collection_id` ASC);

SET FOREIGN_KEY_CHECKS = 1;
