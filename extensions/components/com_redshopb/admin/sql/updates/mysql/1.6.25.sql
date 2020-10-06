SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS `#__redshopb_shipping_configuration` (
  `id`                INT(10)  UNSIGNED NOT NULL AUTO_INCREMENT,
  `extension_name`    VARCHAR(255)      NOT NULL DEFAULT '',
  `owner_name`        VARCHAR(255)      NOT NULL DEFAULT '',
  `shipping_name`     VARCHAR(50)       NOT NULL DEFAULT '',
  `params`            TEXT              NOT NULL,
  `state`             TINYINT(1)        NOT NULL DEFAULT '1',
  `checked_out`       INT(11)           NULL      DEFAULT NULL,
  `checked_out_time`  DATETIME          NOT NULL  DEFAULT '0000-00-00 00:00:00',
  `created_by`        INT(11)           NULL      DEFAULT NULL,
  `created_date`      DATETIME          NOT NULL  DEFAULT '0000-00-00 00:00:00',
  `modified_by`       INT(11)           NULL      DEFAULT NULL,
  `modified_date`     DATETIME          NOT NULL  DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `idx_extension_config` (`extension_name`, `owner_name`, `shipping_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__redshopb_shipping_rates` (
  `id`                        INT(11)  UNSIGNED NOT NULL AUTO_INCREMENT,
  `shipping_configuration_id` INT(10)  UNSIGNED NOT NULL,
  `name`                      VARCHAR(255)   NOT NULL DEFAULT '',
  `countries`                 TEXT           NOT NULL,
  `zip_start`                 VARCHAR(20)    NOT NULL DEFAULT '',
  `zip_end`                   VARCHAR(20)    NOT NULL DEFAULT '',
  `weight_start`              DECIMAL(10, 2) NOT NULL,
  `weight_end`                DECIMAL(10, 2) NOT NULL,
  `volume_start`              DECIMAL(10, 2) NOT NULL,
  `volume_end`                DECIMAL(10, 2) NOT NULL,
  `length_start`              DECIMAL(10, 2) NOT NULL,
  `length_end`                DECIMAL(10, 2) NOT NULL,
  `width_start`               DECIMAL(10, 2) NOT NULL,
  `width_end`                 DECIMAL(10, 2) NOT NULL,
  `height_start`              DECIMAL(10, 2) NOT NULL,
  `height_end`                DECIMAL(10, 2) NOT NULL,
  `order_total_start`         DECIMAL(10, 2) NOT NULL DEFAULT '0.00',
  `order_total_end`           DECIMAL(10, 2) NOT NULL,
  `on_product`                TEXT           NOT NULL,
  `on_category`               TEXT           NOT NULL,
  `priority`                  TINYINT(4)     NOT NULL DEFAULT '0',
  `price`                     DECIMAL(10, 2) NOT NULL DEFAULT '0.00',
  `shipping_location_info`    TEXT           NOT NULL,
  `state`                     TINYINT(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `idx_name` (`name`),
  KEY `idx_shipping_configuration_id` (`shipping_configuration_id`),
  INDEX `idx_filter` (`zip_start` ASC, `countries`(255) ASC, `zip_end` ASC, `weight_start` ASC, `weight_end` ASC, `volume_start` ASC, `volume_end` ASC, `length_start` ASC, `length_end` ASC, `width_start` ASC, `width_end` ASC, `height_start` ASC, `height_end` ASC, `order_total_start` ASC, `order_total_end` ASC),
  CONSTRAINT `#__rs_sr_config_fk_1`
  FOREIGN KEY (`shipping_configuration_id`)
  REFERENCES `#__redshopb_shipping_configuration` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
) ENGINE =InnoDB DEFAULT CHARSET =utf8;

ALTER TABLE `#__redshopb_order` ADD COLUMN `shipping_price` DECIMAL(10,2) UNSIGNED NOT NULL AFTER `payment_status`;
ALTER TABLE `#__redshopb_order` ADD COLUMN `shipping_rate_id` INT(11) UNSIGNED NULL DEFAULT NULL AFTER `payment_status`;
ALTER TABLE `#__redshopb_order` ADD CONSTRAINT `#__rs_order_fk8`
  FOREIGN KEY (`shipping_rate_id`)
  REFERENCES `#__redshopb_shipping_rates` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION;

SET FOREIGN_KEY_CHECKS = 1;
