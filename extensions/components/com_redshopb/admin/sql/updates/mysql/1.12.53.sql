ALTER TABLE `#__redshopb_cron`
  ADD `mute_from` TIME NOT NULL DEFAULT '00:00:00' AFTER `state`,
  ADD `mute_to` TIME NOT NULL DEFAULT '00:00:00' AFTER `mute_from`;
