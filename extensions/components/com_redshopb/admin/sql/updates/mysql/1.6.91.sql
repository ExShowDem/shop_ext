-- -----------------------------------------------------
-- Table `#__redshopb_template`
-- -----------------------------------------------------
CALL `#__redshopb_template_1_6_91`();

DROP PROCEDURE IF EXISTS `#__redshopb_template_1_6_91`;

SET FOREIGN_KEY_CHECKS = 0;
ALTER TABLE `#__redshopb_template`
	ADD COLUMN `editable` TINYINT(3) UNSIGNED NOT NULL DEFAULT '1' COMMENT 'This template can be edit or not.' AFTER `default`;

UPDATE `#__redshopb_template`
	SET `editable` = 0
	WHERE `id` IN (1,2,3,4);
SET FOREIGN_KEY_CHECKS = 1;
