SET FOREIGN_KEY_CHECKS = 0;

ALTER TABLE `#__redshopb_type`
    ADD COLUMN `multiple` TINYINT(4) NOT NULL DEFAULT 0 AFTER `field_name`;

UPDATE `#__redshopb_type`
    SET `multiple` = 1
    WHERE (`alias` = 'dropdownmultiple' AND `field_name` = 'rList')
        OR (`alias` = 'checkbox' AND `field_name` = 'checkboxes')
        OR (`alias` = 'textboxstring' AND `field_name` = 'rText')
        OR (`alias` = 'textboxfloat' AND `field_name` = 'rText')
        OR (`alias` = 'textboxint' AND `field_name` = 'rText')
        OR (`alias` = 'textboxtext' AND `field_name` = 'rText')
        OR (`alias` = 'dropdownsingle' AND `field_name` = 'rList')
        OR (`alias` = 'date' AND `field_name` = 'rdatepicker')
        OR (`alias` = 'documents' AND `field_name` = 'mediaRedshopb')
        OR (`alias` = 'videos' AND `field_name` = 'mediaRedshopb')
        OR (`alias` = 'field-images' AND `field_name` = 'mediaRedshopb');

SET FOREIGN_KEY_CHECKS = 1;
