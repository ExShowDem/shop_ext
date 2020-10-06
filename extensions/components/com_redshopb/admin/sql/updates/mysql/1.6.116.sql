-- -----------------------------------------------------
-- Table `#__redshopb_word_synonym_search_sets`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `#__redshopb_word_synonym_search_sets` (
  `hash` varchar(255) NOT NULL,
  `cache` datetime NOT NULL,
  `phrase` varchar(255) NOT NULL,
  `product_set` longtext NOT NULL,
  PRIMARY KEY (`hash`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8;
