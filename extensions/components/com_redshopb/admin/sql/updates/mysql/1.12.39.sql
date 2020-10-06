-- -----------------------------------------------------
-- Table `#__redshopb_holiday`
-- -----------------------------------------------------
ALTER TABLE `#__redshopb_holiday`
    ADD COLUMN `title` VARCHAR(45) NOT NULL AFTER `id`,
    CHANGE COLUMN `fixed_date` `year` INT NULL;

INSERT INTO `#__redshopb_holiday` (`id`, `title`, `day`, `month`, `year`, `country_id`, `checked_out`, `checked_out_time`, `created_by`, `created_date`, `modified_by`, `modified_date`) VALUES
    (1, 'New Years Day', 1, 1, NULL, 59, NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00'),
    (2, 'Maundy Thursday', 13, 4, 2017, 59, NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00'),
    (3, 'Good Friday', 14, 4, 2017, 59, NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00'),
    (4, 'Easter Monday', 17, 4, 2017, 59, NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00'),
    (5, 'Great Prayer Day', 12, 5, 2017, 59, NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00'),
    (6, 'Ascension Day', 25, 5, 2017, 59, NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00'),
    (7, 'Whit Sunday', 4, 6, 2017, 59, NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00'),
    (8, 'White Monday', 5, 6, 2017, 59, NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00'),
    (9, 'Christmas Eve Day', 24, 12, NULL, 59, NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00'),
    (10, 'Christmas Day', 25, 12, NULL, 59, NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00'),
    (11, '2nd Christmas Day', 26, 12, NULL, 59, NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00');
