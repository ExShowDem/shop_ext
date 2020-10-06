SET FOREIGN_KEY_CHECKS = 0;

UPDATE `#__redshopb_type`
    SET `multiple` = 0
    WHERE `alias` = 'dropdownsingle' AND `field_name` = 'rList';

SET FOREIGN_KEY_CHECKS = 1;