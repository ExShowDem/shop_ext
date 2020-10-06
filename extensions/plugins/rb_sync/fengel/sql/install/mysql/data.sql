SET FOREIGN_KEY_CHECKS = 0;

INSERT INTO `#__redshopb_cron` (`name`, `plugin`, `state`, `start_time`, `finish_time`, `next_start`, `alias`, `parent_alias`, `execute_sync`, `mask_time`, `offset_time`, `params`, `checked_out`, `checked_out_time`) VALUES
	('GetCategory', 'fengel', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'fengel-getcategory', 'root', 0, 'Y-m-d H:00:00', '+1 hour', '', 0, '0000-00-00 00:00:00'),
	('GetItemGroup', 'fengel', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'fengel-getitemgroup', 'fengel-getcategory', 0, 'Y-m-d H:00:00', '+1 hour', '', 0, '0000-00-00 00:00:00'),
	('GetWashCareSpec', 'fengel', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'fengel-getwashcarespec', 'fengel-getitemgroup', 0, 'Y-m-d 00:00:00', '+1 day', '', 0, '0000-00-00 00:00:00'),
	('GetProduct', 'fengel', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'fengel-getproduct', 'fengel-getwashcarespec', 0, 'Y-m-d H:00:00', '+1 hour', '', 0, '0000-00-00 00:00:00'),
	('GetCustomerDiscountGroup', 'fengel', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'fengel-getcustomerdiscountgroup', 'fengel-getproduct', 0, 'Y-m-d H:00:00', '+1 hour', '', 0, '0000-00-00 00:00:00'),
	('GetCustomerPriceGroup', 'fengel', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'fengel-getcustomerpricegroup', 'fengel-getcustomerdiscountgroup', 0, 'Y-m-d H:00:00', '+1 hour', '', 0, '0000-00-00 00:00:00'),
	('GetCustomer', 'fengel', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'fengel-getcustomer', 'fengel-getcustomerpricegroup', 0, 'Y-m-d H:00:00', '+1 hour', '{"CurrentDateTime":"","url":"","login":"","password":"","lang":"","can_use_full_sync":"1"}', 0, '0000-00-00 00:00:00'),
	('GetEndCustomer', 'fengel', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'fengel-getendcustomer', 'fengel-getcustomer', 0, 'Y-m-d H:00:00', '+1 hour', '{"CurrentDateTime":"","url":"","login":"","password":"","lang":"","can_use_full_sync":"1"}', 0, '0000-00-00 00:00:00'),
	('GetDepartment', 'fengel', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'fengel-getdepartment', 'fengel-getendcustomer', 0, 'Y-m-d H:00:00', '+1 hour', '{"CurrentDateTime":"","url":"","login":"","password":"","lang":"","can_use_full_sync":"1"}', 0, '0000-00-00 00:00:00'),
	('GetUser', 'fengel', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'fengel-getuser', 'fengel-getdepartment', 0, 'Y-m-d H:00:00', '+1 hour', '{"CurrentDateTime":"","url":"","login":"","password":"","lang":"","can_use_full_sync":"1"}', 0, '0000-00-00 00:00:00'),
	('GetShiptoAddress', 'fengel', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'fengel-getshiptoaddress', 'fengel-getuser', 0, 'Y-m-d H:00:00', '+1 hour', '', 0, '0000-00-00 00:00:00'),
	('GetSalesPerson', 'fengel', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'fengel-getsalesperson', 'fengel-getuser', 0, 'Y-m-d H:00:00', '+1 hour', '{"CurrentDateTime":"","url":"","login":"","password":"","lang":"","can_use_full_sync":"1"}', 0, '0000-00-00 00:00:00'),
	('GetProductDiscountGroup', 'fengel', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'fengel-getproductdiscountgroup', 'fengel-getproduct', 0, 'Y-m-d H:00:00', '+1 hour', '', 0, '0000-00-00 00:00:00'),
	('GetProductDiscountGroupXref', 'fengel', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'fengel-getproductdiscountgroupxref', 'fengel-getproductdiscountgroup', 0, 'Y-m-d H:00:00', '+1 hour', '', 0, '0000-00-00 00:00:00'),
	('GetProductDiscount', 'fengel', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'fengel-getproductdiscount', 'fengel-getproductdiscountgroupxref', 0, 'Y-m-d H:00:00', '+1 hour', '', 0, '0000-00-00 00:00:00'),
	('GetType', 'fengel', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'fengel-gettype', 'fengel-getproduct', 0, 'Y-m-d H:00:00', '+1 hour', '', 0, '0000-00-00 00:00:00'),
	('GetAttribute', 'fengel', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'fengel-getattribute', 'fengel-gettype', 0, 'Y-m-d H:00:00', '+1 hour', '', 0, '0000-00-00 00:00:00'),
	('GetItem', 'fengel', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'fengel-getitem', 'fengel-getattribute', 0, 'Y-m-d H:00:00', '+1 hour', '{"CurrentDateTime":"","url":"","login":"","password":"","lang":"","can_use_full_sync":"1"}', 0, '0000-00-00 00:00:00'),
	('GetProductPrice', 'fengel', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'fengel-getproductprice', 'fengel-getitem', 0, 'Y-m-d H:00:00', '+1 hour', '{"CurrentDateTime":"","url":"","login":"","password":"","lang":"","can_use_full_sync":"1"}', 0, '0000-00-00 00:00:00'),
	('GetWardrobe', 'fengel', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'fengel-getwardrobe', 'fengel-getproductprice', 0, 'Y-m-d H:00:00', '+1 hour', '', 0, '0000-00-00 00:00:00'),
	('SetSalesOrder', 'fengel', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'fengel-setsalesorder', 'fengel-getwardrobe', 0, 'Y-m-d H:00:00', '+1 hour', '{"CurrentDateTime":"","url":"","login":"","password":"","lang":"","can_use_full_sync":"0"}', 0, '0000-00-00 00:00:00'),
	('GetStock', 'fengel', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'fengel-getstock', 'fengel-getitem', 0, 'Y-m-d H:00:00', '+1 hour', '', 0, '0000-00-00 00:00:00'),
	('GetItemVariantTreshold', 'fengel', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'fengel-getitemvarianttreshold', 'fengel-getitem', 0, 'Y-m-d 00:00:00', '+1 day', '', 0, '0000-00-00 00:00:00'),
	('GetProductPicture', 'fengel', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'fengel-getproductpicture', 'fengel-getattribute', 0, 'Y-m-d 00:00:00', '+1 day', '', 0, '0000-00-00 00:00:00'),
	('GetRedItemDetail', 'fengel', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'fengel-getreditemdetail', 'fengel-getattribute', 0, 'Y-m-d 00:00:00', '+1 day', '{"CurrentDateTime":"","source":"wsdl","url":"http:\\/\\/webservice.f-engel.com\\/server.php?wsdl","login":"1313","password":"Bk1eZ034"}', 0, '0000-00-00 00:00:00'),
	('GetSizes', 'fengel', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'fengel-getsizes', 'fengel-getattribute', 0, 'Y-m-d H:00:00', '+1 hour', '', 0, '0000-00-00 00:00:00'),
	('GetColors', 'fengel', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'fengel-getcolors', 'fengel-getattribute', 0, 'Y-m-d H:00:00', '+1 hour', '', 0, '0000-00-00 00:00:00'),
	('GetComposition', 'fengel', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'fengel-getcomposition', 'fengel-getattribute', 0, 'Y-m-d 00:00:00', '+1 day', '{"CurrentDateTime":"","url":"","login":"","password":"","lang":"","can_use_full_sync":"0"}', 0, '0000-00-00 00:00:00'),
	('GetFeeSetup', 'fengel', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'fengel-getfeesetup', 'fengel-getproduct', 0, 'Y-m-d 00:00:00', '+1 day', '', 0, '0000-00-00 00:00:00'),
	('GetLogos', 'fengel', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'fengel-getlogos', 'fengel-getcategory', 0, 'Y-m-d 00:00:00', '+1 day', '', 0, '0000-00-00 00:00:00');
SET FOREIGN_KEY_CHECKS = 1;