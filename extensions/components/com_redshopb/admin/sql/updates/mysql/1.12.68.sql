UPDATE `#__redshopb_template` SET `alias` = 'send-to-friend' WHERE `alias` = 'send-to-friend-email';

INSERT IGNORE INTO `#__redshopb_template` (`name`, `alias`, `template_group`, `scope`, `state`, `default`, `editable`)
VALUES ('Generic send-to-friend mail template', 'send-to-friend', 'email', 'send-to-friend', 1, 1, 0);