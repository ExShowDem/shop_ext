ALTER TABLE `#__redshopb_field` CHANGE `scope` `scope` ENUM('product','order','category','company','department', 'user') CHARACTER SET utf8 NOT NULL DEFAULT 'product';
