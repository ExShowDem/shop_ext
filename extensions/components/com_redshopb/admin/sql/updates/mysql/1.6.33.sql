SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS `#__redshopb_reports` (
  `id`              INT(11)  UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`            VARCHAR(255)      NOT NULL DEFAULT '',
  `rows`            INT(11)           NOT NULL DEFAULT '0',
  `params`          TEXT              NOT NULL,
  `created_by`      INT(11)           NULL      DEFAULT NULL,
  `created_date`    DATETIME          NOT NULL  DEFAULT '0000-00-00 00:00:00',
  `modified_by`     INT(11)           NULL      DEFAULT NULL,
  `modified_date`   DATETIME          NOT NULL  DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  UNIQUE INDEX `idx_name` (`name` ASC)
) ENGINE =InnoDB DEFAULT CHARSET =utf8;

SET FOREIGN_KEY_CHECKS = 1;
