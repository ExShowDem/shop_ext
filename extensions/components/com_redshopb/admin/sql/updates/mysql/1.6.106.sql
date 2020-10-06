SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS `#__redshopb_word` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`word` VARCHAR(255) NOT NULL,
	`shared` TINYINT(4) NULL DEFAULT 0,
	PRIMARY KEY (`id`),
	INDEX `idx_word` (`word` ASC),
	INDEX `idx_shared` (`shared` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COLLATE = utf8_general_ci;

CREATE TABLE IF NOT EXISTS `#__redshopb_word_synonym_xref` (
	`synonym_word_id` INT(10) UNSIGNED NOT NULL,
	`main_word_id` INT(10) UNSIGNED NOT NULL,
	INDEX `idx_synonym_word_id` (`synonym_word_id` ASC),
	INDEX `idx_main_word_id` (`main_word_id` ASC),
	CONSTRAINT `#__rs_word_fk1`
	FOREIGN KEY (`synonym_word_id`)
	REFERENCES `#__redshopb_word` (`id`)
		ON DELETE CASCADE
		ON UPDATE CASCADE,
	CONSTRAINT `#__rs_word_fk2`
	FOREIGN KEY (`main_word_id`)
	REFERENCES `#__redshopb_word` (`id`)
		ON DELETE CASCADE
		ON UPDATE CASCADE)
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COLLATE = utf8_general_ci;

SET FOREIGN_KEY_CHECKS = 1;