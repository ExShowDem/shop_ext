SET FOREIGN_KEY_CHECKS = 0;

INSERT INTO `#__redshopb_company` (`parent_id`, `lft`, `rgt`, `level`, `path`, `name`, `alias`, `state`, `customer_number`)
  VALUES (NULL, 0, 1, 0, '', 'ROOT', 'root', 1, 'root');

INSERT INTO `#__redshopb_department` (`parent_id`, `lft`, `rgt`, `level`, `path`, `name`, `alias`, `company_id`)
  VALUES (NULL, 0, 1, 0, '', 'ROOT', 'root', 1);

INSERT INTO `#__redshopb_manufacturer` (`parent_id`, `lft`, `rgt`, `level`, `path`, `name`, `alias`)
  VALUES (NULL, 0, 1, 0, '', 'ROOT', 'root');

INSERT INTO `#__redshopb_country` (`id`, `name`, `alpha2`, `alpha3`, `numeric`) VALUES
	(1, 'COM_REDSHOPB_COUNTRY_AFG', 'AF', 'AFG', 4),
	(2, 'COM_REDSHOPB_COUNTRY_ALA', 'AX', 'ALA', 248),
	(3, 'COM_REDSHOPB_COUNTRY_ALB', 'AL', 'ALB', 8),
	(4, 'COM_REDSHOPB_COUNTRY_DZA', 'DZ', 'DZA', 12),
	(5, 'COM_REDSHOPB_COUNTRY_ASM', 'AS', 'ASM', 16),
	(6, 'COM_REDSHOPB_COUNTRY_AND', 'AD', 'AND', 20),
	(7, 'COM_REDSHOPB_COUNTRY_AGO', 'AO', 'AGO', 24),
	(8, 'COM_REDSHOPB_COUNTRY_AIA', 'AI', 'AIA', 660),
	(9, 'COM_REDSHOPB_COUNTRY_ATA', 'AQ', 'ATA', 10),
	(10, 'COM_REDSHOPB_COUNTRY_ATG', 'AG', 'ATG', 28),
	(11, 'COM_REDSHOPB_COUNTRY_ARG', 'AR', 'ARG', 32),
	(12, 'COM_REDSHOPB_COUNTRY_ARM', 'AM', 'ARM', 51),
	(13, 'COM_REDSHOPB_COUNTRY_ABW', 'AW', 'ABW', 533),
	(14, 'COM_REDSHOPB_COUNTRY_AUS', 'AU', 'AUS', 36),
	(15, 'COM_REDSHOPB_COUNTRY_AUT', 'AT', 'AUT', 40),
	(16, 'COM_REDSHOPB_COUNTRY_AZE', 'AZ', 'AZE', 31),
	(17, 'COM_REDSHOPB_COUNTRY_BHS', 'BS', 'BHS', 44),
	(18, 'COM_REDSHOPB_COUNTRY_BHR', 'BH', 'BHR', 48),
	(19, 'COM_REDSHOPB_COUNTRY_BGD', 'BD', 'BGD', 50),
	(20, 'COM_REDSHOPB_COUNTRY_BRB', 'BB', 'BRB', 52),
	(21, 'COM_REDSHOPB_COUNTRY_BLR', 'BY', 'BLR', 112),
	(22, 'COM_REDSHOPB_COUNTRY_BEL', 'BE', 'BEL', 56),
	(23, 'COM_REDSHOPB_COUNTRY_BLZ', 'BZ', 'BLZ', 84),
	(24, 'COM_REDSHOPB_COUNTRY_BEN', 'BJ', 'BEN', 204),
	(25, 'COM_REDSHOPB_COUNTRY_BMU', 'BM', 'BMU', 60),
	(26, 'COM_REDSHOPB_COUNTRY_BTN', 'BT', 'BTN', 64),
	(27, 'COM_REDSHOPB_COUNTRY_BOL', 'BO', 'BOL', 68),
	(28, 'COM_REDSHOPB_COUNTRY_BIH', 'BA', 'BIH', 70),
	(29, 'COM_REDSHOPB_COUNTRY_BWA', 'BW', 'BWA', 72),
	(30, 'COM_REDSHOPB_COUNTRY_BVT', 'BV', 'BVT', 74),
	(31, 'COM_REDSHOPB_COUNTRY_BRA', 'BR', 'BRA', 76),
	(32, 'COM_REDSHOPB_COUNTRY_IOT', 'IO', 'IOT', 86),
	(33, 'COM_REDSHOPB_COUNTRY_BRN', 'BN', 'BRN', 96),
	(34, 'COM_REDSHOPB_COUNTRY_BGR', 'BG', 'BGR', 100),
	(35, 'COM_REDSHOPB_COUNTRY_BFA', 'BF', 'BFA', 854),
	(36, 'COM_REDSHOPB_COUNTRY_BDI', 'BI', 'BDI', 108),
	(37, 'COM_REDSHOPB_COUNTRY_KHM', 'KH', 'KHM', 116),
	(38, 'COM_REDSHOPB_COUNTRY_CMR', 'CM', 'CMR', 120),
	(39, 'COM_REDSHOPB_COUNTRY_CAN', 'CA', 'CAN', 124),
	(40, 'COM_REDSHOPB_COUNTRY_CPV', 'CV', 'CPV', 132),
	(41, 'COM_REDSHOPB_COUNTRY_CYM', 'KY', 'CYM', 136),
	(42, 'COM_REDSHOPB_COUNTRY_CAF', 'CF', 'CAF', 140),
	(43, 'COM_REDSHOPB_COUNTRY_TCD', 'TD', 'TCD', 148),
	(44, 'COM_REDSHOPB_COUNTRY_CHL', 'CL', 'CHL', 152),
	(45, 'COM_REDSHOPB_COUNTRY_CHN', 'CN', 'CHN', 156),
	(46, 'COM_REDSHOPB_COUNTRY_CXR', 'CX', 'CXR', 162),
	(47, 'COM_REDSHOPB_COUNTRY_CCK', 'CC', 'CCK', 166),
	(48, 'COM_REDSHOPB_COUNTRY_COL', 'CO', 'COL', 170),
	(49, 'COM_REDSHOPB_COUNTRY_COM', 'KM', 'COM', 174),
	(50, 'COM_REDSHOPB_COUNTRY_COG', 'CG', 'COG', 178),
	(51, 'COM_REDSHOPB_COUNTRY_COD', 'CD', 'COD', 180),
	(52, 'COM_REDSHOPB_COUNTRY_COK', 'CK', 'COK', 184),
	(53, 'COM_REDSHOPB_COUNTRY_CRI', 'CR', 'CRI', 188),
	(54, 'COM_REDSHOPB_COUNTRY_CIV', 'CI', 'CIV', 384),
	(55, 'COM_REDSHOPB_COUNTRY_HRV', 'HR', 'HRV', 191),
	(56, 'COM_REDSHOPB_COUNTRY_CUB', 'CU', 'CUB', 192),
	(57, 'COM_REDSHOPB_COUNTRY_CYP', 'CY', 'CYP', 196),
	(58, 'COM_REDSHOPB_COUNTRY_CZE', 'CZ', 'CZE', 203),
	(59, 'COM_REDSHOPB_COUNTRY_DNK', 'DK', 'DNK', 208),
	(60, 'COM_REDSHOPB_COUNTRY_DJI', 'DJ', 'DJI', 262),
	(61, 'COM_REDSHOPB_COUNTRY_DMA', 'DM', 'DMA', 212),
	(62, 'COM_REDSHOPB_COUNTRY_DOM', 'DO', 'DOM', 214),
	(63, 'COM_REDSHOPB_COUNTRY_ECU', 'EC', 'ECU', 218),
	(64, 'COM_REDSHOPB_COUNTRY_EGY', 'EG', 'EGY', 818),
	(65, 'COM_REDSHOPB_COUNTRY_SLV', 'SV', 'SLV', 222),
	(66, 'COM_REDSHOPB_COUNTRY_GNQ', 'GQ', 'GNQ', 226),
	(67, 'COM_REDSHOPB_COUNTRY_ERI', 'ER', 'ERI', 232),
	(68, 'COM_REDSHOPB_COUNTRY_EST', 'EE', 'EST', 233),
	(69, 'COM_REDSHOPB_COUNTRY_ETH', 'ET', 'ETH', 231),
	(70, 'COM_REDSHOPB_COUNTRY_FLK', 'FK', 'FLK', 238),
	(71, 'COM_REDSHOPB_COUNTRY_FRO', 'FO', 'FRO', 234),
	(72, 'COM_REDSHOPB_COUNTRY_FJI', 'FJ', 'FJI', 242),
	(73, 'COM_REDSHOPB_COUNTRY_FIN', 'FI', 'FIN', 246),
	(74, 'COM_REDSHOPB_COUNTRY_FRA', 'FR', 'FRA', 250),
	(75, 'COM_REDSHOPB_COUNTRY_GUF', 'GF', 'GUF', 254),
	(76, 'COM_REDSHOPB_COUNTRY_PYF', 'PF', 'PYF', 258),
	(77, 'COM_REDSHOPB_COUNTRY_ATF', 'TF', 'ATF', 260),
	(78, 'COM_REDSHOPB_COUNTRY_GAB', 'GA', 'GAB', 266),
	(79, 'COM_REDSHOPB_COUNTRY_GMB', 'GM', 'GMB', 270),
	(80, 'COM_REDSHOPB_COUNTRY_GEO', 'GE', 'GEO', 268),
	(81, 'COM_REDSHOPB_COUNTRY_DEU', 'DE', 'DEU', 276),
	(82, 'COM_REDSHOPB_COUNTRY_GHA', 'GH', 'GHA', 288),
	(83, 'COM_REDSHOPB_COUNTRY_GIB', 'GI', 'GIB', 292),
	(84, 'COM_REDSHOPB_COUNTRY_GRC', 'EL', 'GRC', 300),
	(85, 'COM_REDSHOPB_COUNTRY_GRL', 'GL', 'GRL', 304),
	(86, 'COM_REDSHOPB_COUNTRY_GRD', 'GD', 'GRD', 308),
	(87, 'COM_REDSHOPB_COUNTRY_GLP', 'GP', 'GLP', 312),
	(88, 'COM_REDSHOPB_COUNTRY_GUM', 'GU', 'GUM', 316),
	(89, 'COM_REDSHOPB_COUNTRY_GTM', 'GT', 'GTM', 320),
	(90, 'COM_REDSHOPB_COUNTRY_GGY', 'GG', 'GGY', 831),
	(91, 'COM_REDSHOPB_COUNTRY_GIN', 'GN', 'GIN', 324),
	(92, 'COM_REDSHOPB_COUNTRY_GNB', 'GW', 'GNB', 624),
	(93, 'COM_REDSHOPB_COUNTRY_GUY', 'GY', 'GUY', 328),
	(94, 'COM_REDSHOPB_COUNTRY_HTI', 'HT', 'HTI', 332),
	(95, 'COM_REDSHOPB_COUNTRY_HMD', 'HM', 'HMD', 334),
	(96, 'COM_REDSHOPB_COUNTRY_VAT', 'VA', 'VAT', 336),
	(97, 'COM_REDSHOPB_COUNTRY_HND', 'HN', 'HND', 340),
	(98, 'COM_REDSHOPB_COUNTRY_HKG', 'HK', 'HKG', 344),
	(99, 'COM_REDSHOPB_COUNTRY_HUN', 'HU', 'HUN', 348),
	(100, 'COM_REDSHOPB_COUNTRY_ISL', 'IS', 'ISL', 352),
	(101, 'COM_REDSHOPB_COUNTRY_IND', 'IN', 'IND', 356),
	(102, 'COM_REDSHOPB_COUNTRY_IDN', 'ID', 'IDN', 360),
	(103, 'COM_REDSHOPB_COUNTRY_IRN', 'IR', 'IRN', 364),
	(104, 'COM_REDSHOPB_COUNTRY_IRQ', 'IQ', 'IRQ', 368),
	(105, 'COM_REDSHOPB_COUNTRY_IRL', 'IE', 'IRL', 372),
	(106, 'COM_REDSHOPB_COUNTRY_IMN', 'IM', 'IMN', 833),
	(107, 'COM_REDSHOPB_COUNTRY_ISR', 'IL', 'ISR', 376),
	(108, 'COM_REDSHOPB_COUNTRY_ITA', 'IT', 'ITA', 380),
	(109, 'COM_REDSHOPB_COUNTRY_JAM', 'JM', 'JAM', 388),
	(110, 'COM_REDSHOPB_COUNTRY_JPN', 'JP', 'JPN', 392),
	(111, 'COM_REDSHOPB_COUNTRY_JEY', 'JE', 'JEY', 832),
	(112, 'COM_REDSHOPB_COUNTRY_JOR', 'JO', 'JOR', 400),
	(113, 'COM_REDSHOPB_COUNTRY_KAZ', 'KZ', 'KAZ', 398),
	(114, 'COM_REDSHOPB_COUNTRY_KEN', 'KE', 'KEN', 404),
	(115, 'COM_REDSHOPB_COUNTRY_KIR', 'KI', 'KIR', 296),
	(116, 'COM_REDSHOPB_COUNTRY_PRK', 'KP', 'PRK', 408),
	(117, 'COM_REDSHOPB_COUNTRY_KOR', 'KR', 'KOR', 410),
	(118, 'COM_REDSHOPB_COUNTRY_KWT', 'KW', 'KWT', 414),
	(119, 'COM_REDSHOPB_COUNTRY_KGZ', 'KG', 'KGZ', 417),
	(120, 'COM_REDSHOPB_COUNTRY_LAO', 'LA', 'LAO', 418),
	(121, 'COM_REDSHOPB_COUNTRY_LVA', 'LV', 'LVA', 428),
	(122, 'COM_REDSHOPB_COUNTRY_LBN', 'LB', 'LBN', 422),
	(123, 'COM_REDSHOPB_COUNTRY_LSO', 'LS', 'LSO', 426),
	(124, 'COM_REDSHOPB_COUNTRY_LBR', 'LR', 'LBR', 430),
	(125, 'COM_REDSHOPB_COUNTRY_LBY', 'LY', 'LBY', 434),
	(126, 'COM_REDSHOPB_COUNTRY_LIE', 'LI', 'LIE', 438),
	(127, 'COM_REDSHOPB_COUNTRY_LTU', 'LT', 'LTU', 440),
	(128, 'COM_REDSHOPB_COUNTRY_LUX', 'LU', 'LUX', 442),
	(129, 'COM_REDSHOPB_COUNTRY_MAC', 'MO', 'MAC', 446),
	(130, 'COM_REDSHOPB_COUNTRY_MKD', 'MK', 'MKD', 807),
	(131, 'COM_REDSHOPB_COUNTRY_MDG', 'MG', 'MDG', 450),
	(132, 'COM_REDSHOPB_COUNTRY_MWI', 'MW', 'MWI', 454),
	(133, 'COM_REDSHOPB_COUNTRY_MYS', 'MY', 'MYS', 458),
	(134, 'COM_REDSHOPB_COUNTRY_MDV', 'MV', 'MDV', 462),
	(135, 'COM_REDSHOPB_COUNTRY_MLI', 'ML', 'MLI', 466),
	(136, 'COM_REDSHOPB_COUNTRY_MLT', 'MT', 'MLT', 470),
	(137, 'COM_REDSHOPB_COUNTRY_MHL', 'MH', 'MHL', 584),
	(138, 'COM_REDSHOPB_COUNTRY_MTQ', 'MQ', 'MTQ', 474),
	(139, 'COM_REDSHOPB_COUNTRY_MRT', 'MR', 'MRT', 478),
	(140, 'COM_REDSHOPB_COUNTRY_MUS', 'MU', 'MUS', 480),
	(141, 'COM_REDSHOPB_COUNTRY_MYT', 'YT', 'MYT', 175),
	(142, 'COM_REDSHOPB_COUNTRY_MEX', 'MX', 'MEX', 484),
	(143, 'COM_REDSHOPB_COUNTRY_FSM', 'FM', 'FSM', 583),
	(144, 'COM_REDSHOPB_COUNTRY_MDA', 'MD', 'MDA', 498),
	(145, 'COM_REDSHOPB_COUNTRY_MCO', 'MC', 'MCO', 492),
	(146, 'COM_REDSHOPB_COUNTRY_MNG', 'MN', 'MNG', 496),
	(147, 'COM_REDSHOPB_COUNTRY_MNE', 'ME', 'MNE', 499),
	(148, 'COM_REDSHOPB_COUNTRY_MSR', 'MS', 'MSR', 500),
	(149, 'COM_REDSHOPB_COUNTRY_MAR', 'MA', 'MAR', 504),
	(150, 'COM_REDSHOPB_COUNTRY_MOZ', 'MZ', 'MOZ', 508),
	(151, 'COM_REDSHOPB_COUNTRY_MMR', 'MM', 'MMR', 104),
	(152, 'COM_REDSHOPB_COUNTRY_NAM', 'NA', 'NAM', 516),
	(153, 'COM_REDSHOPB_COUNTRY_NRU', 'NR', 'NRU', 520),
	(154, 'COM_REDSHOPB_COUNTRY_NPL', 'NP', 'NPL', 524),
	(155, 'COM_REDSHOPB_COUNTRY_NLD', 'NL', 'NLD', 528),
	(156, 'COM_REDSHOPB_COUNTRY_ANT', 'AN', 'ANT', 530),
	(157, 'COM_REDSHOPB_COUNTRY_NCL', 'NC', 'NCL', 540),
	(158, 'COM_REDSHOPB_COUNTRY_NZL', 'NZ', 'NZL', 554),
	(159, 'COM_REDSHOPB_COUNTRY_NIC', 'NI', 'NIC', 558),
	(160, 'COM_REDSHOPB_COUNTRY_NER', 'NE', 'NER', 562),
	(161, 'COM_REDSHOPB_COUNTRY_NGA', 'NG', 'NGA', 566),
	(162, 'COM_REDSHOPB_COUNTRY_NIU', 'NU', 'NIU', 570),
	(163, 'COM_REDSHOPB_COUNTRY_NFK', 'NF', 'NFK', 574),
	(164, 'COM_REDSHOPB_COUNTRY_MNP', 'MP', 'MNP', 580),
	(165, 'COM_REDSHOPB_COUNTRY_NOR', 'NO', 'NOR', 578),
	(166, 'COM_REDSHOPB_COUNTRY_OMN', 'OM', 'OMN', 512),
	(167, 'COM_REDSHOPB_COUNTRY_PAK', 'PK', 'PAK', 586),
	(168, 'COM_REDSHOPB_COUNTRY_PLW', 'PW', 'PLW', 585),
	(169, 'COM_REDSHOPB_COUNTRY_PSE', 'PS', 'PSE', 275),
	(170, 'COM_REDSHOPB_COUNTRY_PAN', 'PA', 'PAN', 591),
	(171, 'COM_REDSHOPB_COUNTRY_PNG', 'PG', 'PNG', 598),
	(172, 'COM_REDSHOPB_COUNTRY_PRY', 'PY', 'PRY', 600),
	(173, 'COM_REDSHOPB_COUNTRY_PER', 'PE', 'PER', 604),
	(174, 'COM_REDSHOPB_COUNTRY_PHL', 'PH', 'PHL', 608),
	(175, 'COM_REDSHOPB_COUNTRY_PCN', 'PN', 'PCN', 612),
	(176, 'COM_REDSHOPB_COUNTRY_POL', 'PL', 'POL', 616),
	(177, 'COM_REDSHOPB_COUNTRY_PRT', 'PT', 'PRT', 620),
	(178, 'COM_REDSHOPB_COUNTRY_PRI', 'PR', 'PRI', 630),
	(179, 'COM_REDSHOPB_COUNTRY_QAT', 'QA', 'QAT', 634),
	(180, 'COM_REDSHOPB_COUNTRY_REU', 'RE', 'REU', 638),
	(181, 'COM_REDSHOPB_COUNTRY_ROU', 'RO', 'ROU', 642),
	(182, 'COM_REDSHOPB_COUNTRY_RUS', 'RU', 'RUS', 643),
	(183, 'COM_REDSHOPB_COUNTRY_RWA', 'RW', 'RWA', 646),
	(184, 'COM_REDSHOPB_COUNTRY_BLM', 'BL', 'BLM', 652),
	(185, 'COM_REDSHOPB_COUNTRY_SHN', 'SH', 'SHN', 654),
	(186, 'COM_REDSHOPB_COUNTRY_KNA', 'KN', 'KNA', 659),
	(187, 'COM_REDSHOPB_COUNTRY_LCA', 'LC', 'LCA', 662),
	(188, 'COM_REDSHOPB_COUNTRY_MAF', 'MF', 'MAF', 663),
	(189, 'COM_REDSHOPB_COUNTRY_SPM', 'PM', 'SPM', 666),
	(190, 'COM_REDSHOPB_COUNTRY_VCT', 'VC', 'VCT', 670),
	(191, 'COM_REDSHOPB_COUNTRY_WSM', 'WS', 'WSM', 882),
	(192, 'COM_REDSHOPB_COUNTRY_SMR', 'SM', 'SMR', 674),
	(193, 'COM_REDSHOPB_COUNTRY_STP', 'ST', 'STP', 678),
	(194, 'COM_REDSHOPB_COUNTRY_SAU', 'SA', 'SAU', 682),
	(195, 'COM_REDSHOPB_COUNTRY_SEN', 'SN', 'SEN', 686),
	(196, 'COM_REDSHOPB_COUNTRY_SRB', 'RS', 'SRB', 688),
	(197, 'COM_REDSHOPB_COUNTRY_SYC', 'SC', 'SYC', 690),
	(198, 'COM_REDSHOPB_COUNTRY_SLE', 'SL', 'SLE', 694),
	(199, 'COM_REDSHOPB_COUNTRY_SGP', 'SG', 'SGP', 702),
	(200, 'COM_REDSHOPB_COUNTRY_SVK', 'SK', 'SVK', 703),
	(201, 'COM_REDSHOPB_COUNTRY_SVN', 'SI', 'SVN', 705),
	(202, 'COM_REDSHOPB_COUNTRY_SLB', 'SB', 'SLB', 90),
	(203, 'COM_REDSHOPB_COUNTRY_SOM', 'SO', 'SOM', 706),
	(204, 'COM_REDSHOPB_COUNTRY_ZAF', 'ZA', 'ZAF', 710),
	(205, 'COM_REDSHOPB_COUNTRY_SGS', 'GS', 'SGS', 239),
	(206, 'COM_REDSHOPB_COUNTRY_ESP', 'ES', 'ESP', 724),
	(207, 'COM_REDSHOPB_COUNTRY_LKA', 'LK', 'LKA', 144),
	(208, 'COM_REDSHOPB_COUNTRY_SDN', 'SD', 'SDN', 736),
	(209, 'COM_REDSHOPB_COUNTRY_SUR', 'SR', 'SUR', 740),
	(210, 'COM_REDSHOPB_COUNTRY_SJM', 'SJ', 'SJM', 744),
	(211, 'COM_REDSHOPB_COUNTRY_SWZ', 'SZ', 'SWZ', 748),
	(212, 'COM_REDSHOPB_COUNTRY_SWE', 'SE', 'SWE', 752),
	(213, 'COM_REDSHOPB_COUNTRY_CHE', 'CH', 'CHE', 756),
	(214, 'COM_REDSHOPB_COUNTRY_SYR', 'SY', 'SYR', 760),
	(215, 'COM_REDSHOPB_COUNTRY_TWN', 'TW', 'TWN', 158),
	(216, 'COM_REDSHOPB_COUNTRY_TJK', 'TJ', 'TJK', 762),
	(217, 'COM_REDSHOPB_COUNTRY_TZA', 'TZ', 'TZA', 834),
	(218, 'COM_REDSHOPB_COUNTRY_THA', 'TH', 'THA', 764),
	(219, 'COM_REDSHOPB_COUNTRY_TLS', 'TL', 'TLS', 626),
	(220, 'COM_REDSHOPB_COUNTRY_TGO', 'TG', 'TGO', 768),
	(221, 'COM_REDSHOPB_COUNTRY_TKL', 'TK', 'TKL', 772),
	(222, 'COM_REDSHOPB_COUNTRY_TON', 'TO', 'TON', 776),
	(223, 'COM_REDSHOPB_COUNTRY_TTO', 'TT', 'TTO', 780),
	(224, 'COM_REDSHOPB_COUNTRY_TUN', 'TN', 'TUN', 788),
	(225, 'COM_REDSHOPB_COUNTRY_TUR', 'TR', 'TUR', 792),
	(226, 'COM_REDSHOPB_COUNTRY_TKM', 'TM', 'TKM', 795),
	(227, 'COM_REDSHOPB_COUNTRY_TCA', 'TC', 'TCA', 796),
	(228, 'COM_REDSHOPB_COUNTRY_TUV', 'TV', 'TUV', 798),
	(229, 'COM_REDSHOPB_COUNTRY_UGA', 'UG', 'UGA', 800),
	(230, 'COM_REDSHOPB_COUNTRY_UKR', 'UA', 'UKR', 804),
	(231, 'COM_REDSHOPB_COUNTRY_ARE', 'AE', 'ARE', 784),
	(232, 'COM_REDSHOPB_COUNTRY_GBR', 'GB', 'GBR', 826),
	(233, 'COM_REDSHOPB_COUNTRY_USA', 'US', 'USA', 840),
	(234, 'COM_REDSHOPB_COUNTRY_UMI', 'UM', 'UMI', 581),
	(235, 'COM_REDSHOPB_COUNTRY_URY', 'UY', 'URY', 858),
	(236, 'COM_REDSHOPB_COUNTRY_UZB', 'UZ', 'UZB', 860),
	(237, 'COM_REDSHOPB_COUNTRY_VUT', 'VU', 'VUT', 548),
	(238, 'COM_REDSHOPB_COUNTRY_VEN', 'VE', 'VEN', 862),
	(239, 'COM_REDSHOPB_COUNTRY_VNM', 'VN', 'VNM', 704),
	(240, 'COM_REDSHOPB_COUNTRY_VGB', 'VG', 'VGB', 92),
	(241, 'COM_REDSHOPB_COUNTRY_VIR', 'VI', 'VIR', 850),
	(242, 'COM_REDSHOPB_COUNTRY_WLF', 'WF', 'WLF', 876),
	(243, 'COM_REDSHOPB_COUNTRY_ESH', 'EH', 'ESH', 732),
	(244, 'COM_REDSHOPB_COUNTRY_YEM', 'YE', 'YEM', 887),
	(245, 'COM_REDSHOPB_COUNTRY_ZMB', 'ZM', 'ZMB', 894),
	(246, 'COM_REDSHOPB_COUNTRY_ZWE', 'ZW', 'ZWE', 716);

UPDATE `#__redshopb_country`
SET `eu_zone` = '1'
WHERE `alpha2` IN ('BE','LU','MT','AT','CZ','DK','FI','FR','HU','IT','EL','BG','LV','HR','DE','LT','NL','CY','EE','IE','ES','NL','CZ','GB','CY','HR','AT','PL');

INSERT INTO `#__redshopb_state` (`id`, `country_id`, `name`, `alpha2`, `alpha3`) VALUES
	(1, 156, 'St. Maarten', 'SM', 'STM'),
	(2, 156, 'Bonaire', 'BN', 'BNR'),
	(3, 156, 'Curacao', 'CR', 'CUR'),
	(4, 12, 'Aragatsotn', 'AG', 'ARG'),
	(5, 12, 'Ararat', 'AR', 'ARR'),
	(6, 12, 'Armavir', 'AV', 'ARM'),
	(7, 12, 'Gegharkunik', 'GR', 'GEG'),
	(8, 12, 'Kotayk', 'KT', 'KOT'),
	(9, 12, 'Lori', 'LO', 'LOR'),
	(10, 12, 'Shirak', 'SH', 'SHI'),
	(11, 12, 'Syunik', 'SU', 'SYU'),
	(12, 12, 'Tavush', 'TV', 'TAV'),
	(13, 12, 'Vayots-Dzor', 'VD', 'VAD'),
	(14, 12, 'Yerevan', 'ER', 'YER'),
	(15, 14, 'Australian Capital Territory', 'AT', 'ACT'),
	(16, 14, 'New South Wales', 'NW', 'NSW'),
	(17, 14, 'Northern Territory', 'NT', 'NOT'),
	(18, 14, 'Queensland', 'QL', 'QLD'),
	(19, 14, 'South Australia', 'SA', 'SOA'),
	(20, 14, 'Tasmania', 'TA', 'TAS'),
	(21, 14, 'Victoria', 'VI', 'VIC'),
	(22, 14, 'Western Australia', 'WA', 'WEA'),
	(23, 31, 'Acre', 'AC', 'ACR'),
	(24, 31, 'Alagoas', 'AL', 'ALG'),
	(25, 31, 'Amapá', 'AP', 'AMP'),
	(26, 31, 'Amazonas', 'AM', 'AMZ'),
	(27, 31, 'Bahía', 'BA', 'BAH'),
	(28, 31, 'Ceará', 'CE', 'CEA'),
	(29, 31, 'Distrito Federal', 'DF', 'DFB'),
	(30, 31, 'Espirito Santo', 'ES', 'ESS'),
	(31, 31, 'Goiás', 'GO', 'GOI'),
	(32, 31, 'Maranhão', 'MA', 'MAR'),
	(33, 31, 'Mato Grosso', 'MT', 'MAT'),
	(34, 31, 'Mato Grosso do Sul', 'MS', 'MGS'),
	(35, 31, 'Minas Geraís', 'MG', 'MIG'),
	(36, 31, 'Paraná', 'PR', 'PAR'),
	(37, 31, 'Paraíba', 'PB', 'PRB'),
	(38, 31, 'Pará', 'PA', 'PAB'),
	(39, 31, 'Pernambuco', 'PE', 'PER'),
	(40, 31, 'Piauí', 'PI', 'PIA'),
	(41, 31, 'Rio Grande do Norte', 'RN', 'RGN'),
	(42, 31, 'Rio Grande do Sul', 'RS', 'RGS'),
	(43, 31, 'Rio de Janeiro', 'RJ', 'RDJ'),
	(44, 31, 'Rondônia', 'RO', 'RON'),
	(45, 31, 'Roraima', 'RR', 'ROR'),
	(46, 31, 'Santa Catarina', 'SC', 'SAC'),
	(47, 31, 'Sergipe', 'SE', 'SER'),
	(48, 31, 'São Paulo', 'SP', 'SAP'),
	(49, 31, 'Tocantins', 'TO', 'TOC'),
	(50, 39, 'Alberta', 'AB', 'ALB'),
	(51, 39, 'British Columbia', 'BC', 'BRC'),
	(52, 39, 'Manitoba', 'MB', 'MAB'),
	(53, 39, 'New Brunswick', 'NB', 'NEB'),
	(54, 39, 'Newfoundland and Labrador', 'NL', 'NFL'),
	(55, 39, 'Northwest Territories', 'NT', 'NWT'),
	(56, 39, 'Nova Scotia', 'NS', 'NOS'),
	(57, 39, 'Nunavut', 'NU', 'NUT'),
	(58, 39, 'Ontario', 'ON', 'ONT'),
	(59, 39, 'Prince Edward Island', 'PE', 'PEI'),
	(60, 39, 'Quebec', 'QC', 'QEC'),
	(61, 39, 'Saskatchewan', 'SK', 'SAK'),
	(62, 39, 'Yukon', 'YT', 'YUT'),
	(63, 45, 'Anhui', '34', 'ANH'),
	(64, 45, 'Beijing', '11', 'BEI'),
	(65, 45, 'Chongqing', '50', 'CHO'),
	(66, 45, 'Fujian', '35', 'FUJ'),
	(67, 45, 'Gansu', '62', 'GAN'),
	(68, 45, 'Guangdong', '44', 'GUA'),
	(69, 45, 'Guangxi Zhuang', '45', 'GUZ'),
	(70, 45, 'Guizhou', '52', 'GUI'),
	(71, 45, 'Hainan', '46', 'HAI'),
	(72, 45, 'Hebei', '13', 'HEB'),
	(73, 45, 'Heilongjiang', '23', 'HEI'),
	(74, 45, 'Henan', '41', 'HEN'),
	(75, 45, 'Hubei', '42', 'HUB'),
	(76, 45, 'Hunan', '43', 'HUN'),
	(77, 45, 'Jiangsu', '32', 'JIA'),
	(78, 45, 'Jiangxi', '36', 'JIX'),
	(79, 45, 'Jilin', '22', 'JIL'),
	(80, 45, 'Liaoning', '21', 'LIA'),
	(81, 45, 'Nei Mongol', '15', 'NML'),
	(82, 45, 'Ningxia Hui', '64', 'NIH'),
	(83, 45, 'Qinghai', '63', 'QIN'),
	(84, 45, 'Shandong', '37', 'SNG'),
	(85, 45, 'Shanghai', '31', 'SHH'),
	(86, 45, 'Shaanxi', '61', 'SHX'),
	(87, 45, 'Sichuan', '51', 'SIC'),
	(88, 45, 'Tianjin', '12', 'TIA'),
	(89, 45, 'Xinjiang Uygur', '65', 'XIU'),
	(90, 45, 'Xizang', '54', 'XIZ'),
	(91, 45, 'Yunnan', '53', 'YUN'),
	(92, 45, 'Zhejiang', '33', 'ZHE'),
	(93, 206, 'A Coruña', '15', 'ACO'),
	(94, 206, 'Alava', '01', 'ALA'),
	(95, 206, 'Albacete', '02', 'ALB'),
	(96, 206, 'Alicante', '03', 'ALI'),
	(97, 206, 'Almeria', '04', 'ALM'),
	(98, 206, 'Asturias', '33', 'AST'),
	(99, 206, 'Avila', '05', 'AVI'),
	(100, 206, 'Badajoz', '06', 'BAD'),
	(101, 206, 'Baleares', '07', 'BAL'),
	(102, 206, 'Barcelona', '08', 'BAR'),
	(103, 206, 'Burgos', '09', 'BUR'),
	(104, 206, 'Caceres', '10', 'CAC'),
	(105, 206, 'Cadiz', '11', 'CAD'),
	(106, 206, 'Cantabria', '39', 'CAN'),
	(107, 206, 'Castellon', '12', 'CAS'),
	(108, 206, 'Ceuta', '51', 'CEU'),
	(109, 206, 'Ciudad Real', '13', 'CIU'),
	(110, 206, 'Cordoba', '14', 'COR'),
	(111, 206, 'Cuenca', '16', 'CUE'),
	(112, 206, 'Girona', '17', 'GIR'),
	(113, 206, 'Granada', '18', 'GRA'),
	(114, 206, 'Guadalajara', '19', 'GUA'),
	(115, 206, 'Guipuzcoa', '20', 'GUI'),
	(116, 206, 'Huelva', '21', 'HUL'),
	(117, 206, 'Huesca', '22', 'HUS'),
	(118, 206, 'Jaen', '23', 'JAE'),
	(119, 206, 'La Rioja', '26', 'LRI'),
	(120, 206, 'Las Palmas', '35', 'LPA'),
	(121, 206, 'Leon', '24', 'LEO'),
	(122, 206, 'Lleida', '25', 'LLE'),
	(123, 206, 'Lugo', '27', 'LUG'),
	(124, 206, 'Madrid', '28', 'MAD'),
	(125, 206, 'Malaga', '29', 'MAL'),
	(126, 206, 'Melilla', '52', 'MEL'),
	(127, 206, 'Murcia', '30', 'MUR'),
	(128, 206, 'Navarra', '31', 'NAV'),
	(129, 206, 'Ourense', '32', 'OUR'),
	(130, 206, 'Palencia', '34', 'PAL'),
	(131, 206, 'Pontevedra', '36', 'PON'),
	(132, 206, 'Salamanca', '37', 'SAL'),
	(133, 206, 'Santa Cruz de Tenerife', '38', 'SCT'),
	(134, 206, 'Segovia', '40', 'SEG'),
	(135, 206, 'Sevilla', '41', 'SEV'),
	(136, 206, 'Soria', '42', 'SOR'),
	(137, 206, 'Tarragona', '43', 'TAR'),
	(138, 206, 'Teruel', '44', 'TER'),
	(139, 206, 'Toledo', '45', 'TOL'),
	(140, 206, 'Valencia', '46', 'VAL'),
	(141, 206, 'Valladolid', '47', 'VLL'),
	(142, 206, 'Vizcaya', '48', 'VIZ'),
	(143, 206, 'Zamora', '49', 'ZAM'),
	(144, 206, 'Zaragoza', '50', 'ZAR'),
	(145, 232, 'England', 'EN', 'ENG'),
	(146, 232, 'Northern Ireland', 'NI', 'NOI'),
	(147, 232, 'Scotland', 'SD', 'SCO'),
	(148, 232, 'Wales', 'WS', 'WLS'),
	(149, 101, 'Andaman & Nicobar Islands', 'AI', 'ANI'),
	(150, 101, 'Andhra Pradesh', 'AN', 'AND'),
	(151, 101, 'Arunachal Pradesh', 'AR', 'ARU'),
	(152, 101, 'Assam', 'AS', 'ASS'),
	(153, 101, 'Bihar', 'BI', 'BIH'),
	(154, 101, 'Chandigarh', 'CA', 'CHA'),
	(155, 101, 'Chhatisgarh', 'CH', 'CHH'),
	(156, 101, 'Dadra & Nagar Haveli', 'DD', 'DAD'),
	(157, 101, 'Daman & Diu', 'DA', 'DAM'),
	(158, 101, 'Delhi', 'DE', 'DEL'),
	(159, 101, 'Goa', 'GO', 'GOA'),
	(160, 101, 'Gujarat', 'GU', 'GUJ'),
	(161, 101, 'Haryana', 'HA', 'HAR'),
	(162, 101, 'Himachal Pradesh', 'HI', 'HIM'),
	(163, 101, 'Jammu & Kashmir', 'JA', 'JAM'),
	(164, 101, 'Jharkhand', 'JH', 'JHA'),
	(165, 101, 'Karnataka', 'KA', 'KAR'),
	(166, 101, 'Kerala', 'KE', 'KER'),
	(167, 101, 'Lakshadweep', 'LA', 'LAK'),
	(168, 101, 'Madhya Pradesh', 'MD', 'MAD'),
	(169, 101, 'Maharashtra', 'MH', 'MAH'),
	(170, 101, 'Manipur', 'MN', 'MAN'),
	(171, 101, 'Meghalaya', 'ME', 'MEG'),
	(172, 101, 'Mizoram', 'MI', 'MIZ'),
	(173, 101, 'Nagaland', 'NA', 'NAG'),
	(174, 101, 'Orissa', 'OR', 'ORI'),
	(175, 101, 'Pondicherry', 'PO', 'PON'),
	(176, 101, 'Punjab', 'PU', 'PUN'),
	(177, 101, 'Rajasthan', 'RA', 'RAJ'),
	(178, 101, 'Sikkim', 'SI', 'SIK'),
	(179, 101, 'Tamil Nadu', 'TA', 'TAM'),
	(180, 101, 'Tripura', 'TR', 'TRI'),
	(181, 101, 'Uttaranchal', 'UA', 'UAR'),
	(182, 101, 'Uttar Pradesh', 'UT', 'UTT'),
	(183, 101, 'West Bengal', 'WE', 'WES'),
	(184, 103, 'Ahmadi va Kohkiluyeh', 'BO', 'BOK'),
	(185, 103, 'Ardabil', 'AR', 'ARD'),
	(186, 103, 'Azarbayjan-e Gharbi', 'AG', 'AZG'),
	(187, 103, 'Azarbayjan-e Sharqi', 'AS', 'AZS'),
	(188, 103, 'Bushehr', 'BU', 'BUS'),
	(189, 103, 'Chaharmahal va Bakhtiari', 'CM', 'CMB'),
	(190, 103, 'Esfahan', 'ES', 'ESF'),
	(191, 103, 'Fars', 'FA', 'FAR'),
	(192, 103, 'Gilan', 'GI', 'GIL'),
	(193, 103, 'Gorgan', 'GO', 'GOR'),
	(194, 103, 'Hamadan', 'HA', 'HAM'),
	(195, 103, 'Hormozgan', 'HO', 'HOR'),
	(196, 103, 'Ilam', 'IL', 'ILA'),
	(197, 103, 'Kerman', 'KE', 'KER'),
	(198, 103, 'Kermanshah', 'BA', 'BAK'),
	(199, 103, 'Khorasan-e Junoubi', 'KJ', 'KHJ'),
	(200, 103, 'Khorasan-e Razavi', 'KR', 'KHR'),
	(201, 103, 'Khorasan-e Shomali', 'KS', 'KHS'),
	(202, 103, 'Khuzestan', 'KH', 'KHU'),
	(203, 103, 'Kordestan', 'KO', 'KOR'),
	(204, 103, 'Lorestan', 'LO', 'LOR'),
	(205, 103, 'Markazi', 'MR', 'MAR'),
	(206, 103, 'Mazandaran', 'MZ', 'MAZ'),
	(207, 103, 'Qazvin', 'QA', 'QAS'),
	(208, 103, 'Qom', 'QO', 'QOM'),
	(209, 103, 'Semnan', 'SE', 'SEM'),
	(210, 103, 'Sistan va Baluchestan', 'SB', 'SBA'),
	(211, 103, 'Tehran', 'TE', 'TEH'),
	(212, 103, 'Yazd', 'YA', 'YAZ'),
	(213, 103, 'Zanjan', 'ZA', 'ZAN'),
	(214, 107, 'Gaza Strip', 'GZ', 'GZS'),
	(215, 107, 'West Bank', 'WB', 'WBK'),
	(216, 107, 'Other', 'OT', 'OTH'),
	(217, 108, 'Agrigento', 'AG', 'AGR'),
	(218, 108, 'Alessandria', 'AL', 'ALE'),
	(219, 108, 'Ancona', 'AN', 'ANC'),
	(220, 108, 'Aosta', 'AO', 'AOS'),
	(221, 108, 'Arezzo', 'AR', 'ARE'),
	(222, 108, 'Ascoli Piceno', 'AP', 'API'),
	(223, 108, 'Asti', 'AT', 'AST'),
	(224, 108, 'Avellino', 'AV', 'AVE'),
	(225, 108, 'Bari', 'BA', 'BAR'),
	(226, 108, 'Belluno', 'BL', 'BEL'),
	(227, 108, 'Benevento', 'BN', 'BEN'),
	(228, 108, 'Bergamo', 'BG', 'BEG'),
	(229, 108, 'Biella', 'BI', 'BIE'),
	(230, 108, 'Bologna', 'BO', 'BOL'),
	(231, 108, 'Bolzano', 'BZ', 'BOZ'),
	(232, 108, 'Brescia', 'BS', 'BRE'),
	(233, 108, 'Brindisi', 'BR', 'BRI'),
	(234, 108, 'Cagliari', 'CA', 'CAG'),
	(235, 108, 'Caltanissetta', 'CL', 'CAL'),
	(236, 108, 'Campobasso', 'CB', 'CBO'),
	(237, 108, 'Carbonia-Iglesias', 'CI', 'CAR'),
	(238, 108, 'Caserta', 'CE', 'CAS'),
	(239, 108, 'Catania', 'CT', 'CAT'),
	(240, 108, 'Catanzaro', 'CZ', 'CTZ'),
	(241, 108, 'Chieti', 'CH', 'CHI'),
	(242, 108, 'Como', 'CO', 'COM'),
	(243, 108, 'Cosenza', 'CS', 'COS'),
	(244, 108, 'Cremona', 'CR', 'CRE'),
	(245, 108, 'Crotone', 'KR', 'CRO'),
	(246, 108, 'Cuneo', 'CN', 'CUN'),
	(247, 108, 'Enna', 'EN', 'ENN'),
	(248, 108, 'Ferrara', 'FE', 'FER'),
	(249, 108, 'Firenze', 'FI', 'FIR'),
	(250, 108, 'Foggia', 'FG', 'FOG'),
	(251, 108, 'Forli-Cesena', 'FC', 'FOC'),
	(252, 108, 'Frosinone', 'FR', 'FRO'),
	(253, 108, 'Genova', 'GE', 'GEN'),
	(254, 108, 'Gorizia', 'GO', 'GOR'),
	(255, 108, 'Grosseto', 'GR', 'GRO'),
	(256, 108, 'Imperia', 'IM', 'IMP'),
	(257, 108, 'Isernia', 'IS', 'ISE'),
	(258, 108, 'L''Aquila', 'AQ', 'AQU'),
	(259, 108, 'La Spezia', 'SP', 'LAS'),
	(260, 108, 'Latina', 'LT', 'LAT'),
	(261, 108, 'Lecce', 'LE', 'LEC'),
	(262, 108, 'Lecco', 'LC', 'LCC'),
	(263, 108, 'Livorno', 'LI', 'LIV'),
	(264, 108, 'Lodi', 'LO', 'LOD'),
	(265, 108, 'Lucca', 'LU', 'LUC'),
	(266, 108, 'Macerata', 'MC', 'MAC'),
	(267, 108, 'Mantova', 'MN', 'MAN'),
	(268, 108, 'Massa-Carrara', 'MS', 'MAS'),
	(269, 108, 'Matera', 'MT', 'MAA'),
	(270, 108, 'Medio Campidano', 'VS', 'MED'),
	(271, 108, 'Messina', 'ME', 'MES'),
	(272, 108, 'Milano', 'MI', 'MIL'),
	(273, 108, 'Modena', 'MO', 'MOD'),
	(274, 108, 'Napoli', 'NA', 'NAP'),
	(275, 108, 'Novara', 'NO', 'NOV'),
	(276, 108, 'Nuoro', 'NU', 'NUR'),
	(277, 108, 'Ogliastra', 'OG', 'OGL'),
	(278, 108, 'Olbia-Tempio', 'OT', 'OLB'),
	(279, 108, 'Oristano', 'OR', 'ORI'),
	(280, 108, 'Padova', 'PD', 'PDA'),
	(281, 108, 'Palermo', 'PA', 'PAL'),
	(282, 108, 'Parma', 'PR', 'PAA'),
	(283, 108, 'Pavia', 'PV', 'PAV'),
	(284, 108, 'Perugia', 'PG', 'PER'),
	(285, 108, 'Pesaro e Urbino', 'PU', 'PES'),
	(286, 108, 'Pescara', 'PE', 'PSC'),
	(287, 108, 'Piacenza', 'PC', 'PIA'),
	(288, 108, 'Pisa', 'PI', 'PIS'),
	(289, 108, 'Pistoia', 'PT', 'PIT'),
	(290, 108, 'Pordenone', 'PN', 'POR'),
	(291, 108, 'Potenza', 'PZ', 'PTZ'),
	(292, 108, 'Prato', 'PO', 'PRA'),
	(293, 108, 'Ragusa', 'RG', 'RAG'),
	(294, 108, 'Ravenna', 'RA', 'RAV'),
	(295, 108, 'Reggio Calabria', 'RC', 'REG'),
	(296, 108, 'Reggio Emilia', 'RE', 'REE'),
	(297, 108, 'Rieti', 'RI', 'RIE'),
	(298, 108, 'Rimini', 'RN', 'RIM'),
	(299, 108, 'Roma', 'RM', 'ROM'),
	(300, 108, 'Rovigo', 'RO', 'ROV'),
	(301, 108, 'Salerno', 'SA', 'SAL'),
	(302, 108, 'Sassari', 'SS', 'SAS'),
	(303, 108, 'Savona', 'SV', 'SAV'),
	(304, 108, 'Siena', 'SI', 'SIE'),
	(305, 108, 'Siracusa', 'SR', 'SIR'),
	(306, 108, 'Sondrio', 'SO', 'SOO'),
	(307, 108, 'Taranto', 'TA', 'TAR'),
	(308, 108, 'Teramo', 'TE', 'TER'),
	(309, 108, 'Terni', 'TR', 'TRN'),
	(310, 108, 'Torino', 'TO', 'TOR'),
	(311, 108, 'Trapani', 'TP', 'TRA'),
	(312, 108, 'Trento', 'TN', 'TRE'),
	(313, 108, 'Treviso', 'TV', 'TRV'),
	(314, 108, 'Trieste', 'TS', 'TRI'),
	(315, 108, 'Udine', 'UD', 'UDI'),
	(316, 108, 'Varese', 'VA', 'VAR'),
	(317, 108, 'Venezia', 'VE', 'VEN'),
	(318, 108, 'Verbano Cusio Ossola', 'VB', 'VCO'),
	(319, 108, 'Vercelli', 'VC', 'VER'),
	(320, 108, 'Verona', 'VR', 'VRN'),
	(321, 108, 'Vibo Valenzia', 'VV', 'VIV'),
	(322, 108, 'Vicenza', 'VI', 'VII'),
	(323, 108, 'Viterbo', 'VT', 'VIT'),
	(324, 142, 'Aguascalientes', 'AG', 'AGS'),
	(325, 142, 'Baja California Norte', 'BN', 'BCN'),
	(326, 142, 'Baja California Sur', 'BS', 'BCS'),
	(327, 142, 'Campeche', 'CA', 'CAM'),
	(328, 142, 'Chiapas', 'CS', 'CHI'),
	(329, 142, 'Chihuahua', 'CH', 'CHA'),
	(330, 142, 'Coahuila', 'CO', 'COA'),
	(331, 142, 'Colima', 'CM', 'COL'),
	(332, 142, 'Distrito Federal', 'DF', 'DFM'),
	(333, 142, 'Durango', 'DO', 'DGO'),
	(334, 142, 'Guanajuato', 'GO', 'GTO'),
	(335, 142, 'Guerrero', 'GU', 'GRO'),
	(336, 142, 'Hidalgo', 'HI', 'HGO'),
	(337, 142, 'Jalisco', 'JA', 'JAL'),
	(338, 142, 'México (Estado de)', 'EM', 'EDM'),
	(339, 142, 'Michoacán', 'MI', 'MCN'),
	(340, 142, 'Morelos', 'MO', 'MOR'),
	(341, 142, 'Nayarit', 'NY', 'NAY'),
	(342, 142, 'Nuevo León', 'NL', 'NUL'),
	(343, 142, 'Oaxaca', 'OA', 'OAX'),
	(344, 142, 'Puebla', 'PU', 'PUE'),
	(345, 142, 'Querétaro', 'QU', 'QRO'),
	(346, 142, 'Quintana Roo', 'QR', 'QUR'),
	(347, 142, 'San Luis Potosí', 'SP', 'SLP'),
	(348, 142, 'Sinaloa', 'SI', 'SIN'),
	(349, 142, 'Sonora', 'SO', 'SON'),
	(350, 142, 'Tabasco', 'TA', 'TAB'),
	(351, 142, 'Tamaulipas', 'TM', 'TAM'),
	(352, 142, 'Tlaxcala', 'TX', 'TLX'),
	(353, 142, 'Veracruz', 'VZ', 'VER'),
	(354, 142, 'Yucatán', 'YU', 'YUC'),
	(355, 142, 'Zacatecas', 'ZA', 'ZAC'),
	(356, 176, 'Dolnośląskie', 'DO', 'DOL'),
	(357, 176, 'Kujawsko-Pomorskie', 'KU', 'KUJ'),
	(358, 176, 'Lubelskie', 'LU', 'LUB'),
	(359, 176, 'Lubuskie', 'LB', 'LBU'),
	(360, 176, 'Łódzkie', 'LO', 'LOD'),
	(361, 176, 'Małopolskie', 'MP', 'MAL'),
	(362, 176, 'Mazowieckie', 'MZ', 'MAZ'),
	(363, 176, 'Opolskie', 'OP', 'OPO'),
	(364, 176, 'Podkarpackie', 'PK', 'PDK'),
	(365, 176, 'Podlaskie', 'PL', 'PDL'),
	(366, 176, 'Pomorskie', 'PO', 'POM'),
	(367, 176, 'Śląskie', 'SL', 'SLA'),
	(368, 176, 'Świętokrzyskie', 'SW', 'SWI'),
	(369, 176, 'Warmińsko-Mazurskie', 'WA', 'WAR'),
	(370, 176, 'Wielkopolskie', 'WI', 'WIE'),
	(371, 176, 'Zachodniopomorskie', 'ZA', 'ZAC'),
	(372, 181, 'Alba', 'AB', 'ABA'),
	(373, 181, 'Arad', 'AR', 'ARD'),
	(374, 181, 'Arges', 'AG', 'ARG'),
	(375, 181, 'Bacau', 'BC', 'BAC'),
	(376, 181, 'Bihor', 'BH', 'BIH'),
	(377, 181, 'Bistrita-Nasaud', 'BN', 'BIS'),
	(378, 181, 'Botosani', 'BT', 'BOT'),
	(379, 181, 'Braila', 'BR', 'BRL'),
	(380, 181, 'Brasov', 'BV', 'BRA'),
	(381, 181, 'Bucuresti', 'B', 'BUC'),
	(382, 181, 'Buzau', 'BZ', 'BUZ'),
	(383, 181, 'Calarasi', 'CL', 'CAL'),
	(384, 181, 'Caras Severin', 'CS', 'CRS'),
	(385, 181, 'Cluj', 'CJ', 'CLJ'),
	(386, 181, 'Constanta', 'CT', 'CST'),
	(387, 181, 'Covasna', 'CV', 'COV'),
	(388, 181, 'Dambovita', 'DB', 'DAM'),
	(389, 181, 'Dolj', 'DJ', 'DLJ'),
	(390, 181, 'Galati', 'GL', 'GAL'),
	(391, 181, 'Giurgiu', 'GR', 'GIU'),
	(392, 181, 'Gorj', 'GJ', 'GOR'),
	(393, 181, 'Hargita', 'HR', 'HRG'),
	(394, 181, 'Hunedoara', 'HD', 'HUN'),
	(395, 181, 'Ialomita', 'IL', 'IAL'),
	(396, 181, 'Iasi', 'IS', 'IAS'),
	(397, 181, 'Ilfov', 'IF', 'ILF'),
	(398, 181, 'Maramures', 'MM', 'MAR'),
	(399, 181, 'Mehedinti', 'MH', 'MEH'),
	(400, 181, 'Mures', 'MS', 'MUR'),
	(401, 181, 'Neamt', 'NT', 'NEM'),
	(402, 181, 'Olt', 'OT', 'OLT'),
	(403, 181, 'Prahova', 'PH', 'PRA'),
	(404, 181, 'Salaj', 'SJ', 'SAL'),
	(405, 181, 'Satu Mare', 'SM', 'SAT'),
	(406, 181, 'Sibiu', 'SB', 'SIB'),
	(407, 181, 'Suceava', 'SV', 'SUC'),
	(408, 181, 'Teleorman', 'TR', 'TEL'),
	(409, 181, 'Timis', 'TM', 'TIM'),
	(410, 181, 'Tulcea', 'TL', 'TUL'),
	(411, 181, 'Valcea', 'VL', 'VAL'),
	(412, 181, 'Vaslui', 'VS', 'VAS'),
	(413, 181, 'Vrancea', 'VN', 'VRA'),
	(414, 233, 'Alabama', 'AL', 'ALA'),
	(415, 233, 'Alaska', 'AK', 'ALK'),
	(416, 233, 'Arizona', 'AZ', 'ARZ'),
	(417, 233, 'Arkansas', 'AR', 'ARK'),
	(418, 233, 'California', 'CA', 'CAL'),
	(419, 233, 'Colorado', 'CO', 'COL'),
	(420, 233, 'Connecticut', 'CT', 'CCT'),
	(421, 233, 'Delaware', 'DE', 'DEL'),
	(422, 233, 'District Of Columbia', 'DC', 'DOC'),
	(423, 233, 'Florida', 'FL', 'FLO'),
	(424, 233, 'Georgia', 'GA', 'GEA'),
	(425, 233, 'Hawaii', 'HI', 'HWI'),
	(426, 233, 'Idaho', 'ID', 'IDA'),
	(427, 233, 'Illinois', 'IL', 'ILL'),
	(428, 233, 'Indiana', 'IN', 'IND'),
	(429, 233, 'Iowa', 'IA', 'IOA'),
	(430, 233, 'Kansas', 'KS', 'KAS'),
	(431, 233, 'Kentucky', 'KY', 'KTY'),
	(432, 233, 'Louisiana', 'LA', 'LOA'),
	(433, 233, 'Maine', 'ME', 'MAI'),
	(434, 233, 'Maryland', 'MD', 'MLD'),
	(435, 233, 'Massachusetts', 'MA', 'MSA'),
	(436, 233, 'Michigan', 'MI', 'MIC'),
	(437, 233, 'Minnesota', 'MN', 'MIN'),
	(438, 233, 'Mississippi', 'MS', 'MIS'),
	(439, 233, 'Missouri', 'MO', 'MIO'),
	(440, 233, 'Montana', 'MT', 'MOT'),
	(441, 233, 'Nebraska', 'NE', 'NEB'),
	(442, 233, 'Nevada', 'NV', 'NEV'),
	(443, 233, 'New Hampshire', 'NH', 'NEH'),
	(444, 233, 'New Jersey', 'NJ', 'NEJ'),
	(445, 233, 'New Mexico', 'NM', 'NEM'),
	(446, 233, 'New York', 'NY', 'NEY'),
	(447, 233, 'North Carolina', 'NC', 'NOC'),
	(448, 233, 'North Dakota', 'ND', 'NOD'),
	(449, 233, 'Ohio', 'OH', 'OHI'),
	(450, 233, 'Oklahoma', 'OK', 'OKL'),
	(451, 233, 'Oregon', 'OR', 'ORN'),
	(452, 233, 'Pennsylvania', 'PA', 'PEA'),
	(453, 233, 'Rhode Island', 'RI', 'RHI'),
	(454, 233, 'South Carolina', 'SC', 'SOC'),
	(455, 233, 'South Dakota', 'SD', 'SOD'),
	(456, 233, 'Tennessee', 'TN', 'TEN'),
	(457, 233, 'Texas', 'TX', 'TXS'),
	(458, 233, 'Utah', 'UT', 'UTA'),
	(459, 233, 'Vermont', 'VT', 'VMT'),
	(460, 233, 'Virginia', 'VA', 'VIA'),
	(461, 233, 'Washington', 'WA', 'WAS'),
	(462, 233, 'West Virginia', 'WV', 'WEV'),
	(463, 233, 'Wisconsin', 'WI', 'WIS'),
	(464, 233, 'Wyoming', 'WY', 'WYO');

INSERT INTO `#__redshopb_currency` (`id`, `alpha3`, `name`, `symbol`, `numeric`, `symbol_position`, `decimals`, `state`, `blank_space`, `decimal_separator`, `thousands_separator`) VALUES
	(1, 'AED', 'UAE Dirham', 'د.إ', 784, 1, 2, 1, 1, ',', '.'),
	(2, 'AFN', 'Afghani', '؋', 971, 1, 2, 1, 1, ',', '.'),
	(3, 'ALL', 'Lek', 'Lek', 008, 1, 2, 1, 1, ',', '.'),
	(4, 'AMD', 'Armenian Dram', 'AMD', 051, 1, 2, 1, 1, ',', '.'),
	(5, 'ANG', 'Netherlands Antillean Guilder', 'ƒ', 532, 1, 2, 1, 1, ',', '.'),
	(6, 'AOA', 'Kwanza', 'Kz', 973, 1, 2, 1, 1, ',', '.'),
	(7, 'ARS', 'Argentine Peso', '$', 032, 1, 2, 1, 1, ',', '.'),
	(8, 'AUD', 'Australian Dollar', '$', 036, 1, 2, 1, 1, ',', '.'),
	(9, 'AWG', 'Aruban Florin', 'ƒ', 533, 1, 2, 1, 1, ',', '.'),
	(10, 'AZN', 'Azerbaijanian Manat', 'ман', 944, 1, 2, 1, 1, ',', '.'),
	(11, 'BAM', 'Convertible Mark', 'KM', 977, 1, 2, 1, 1, ',', '.'),
	(12, 'BBD', 'Barbados Dollar', '$', 052, 1, 2, 1, 1, ',', '.'),
	(13, 'BDT', 'Taka', 'Tk', 050, 1, 2, 1, 1, ',', '.'),
	(14, 'BGN', 'Bulgarian Lev', 'лв', 975, 1, 2, 1, 1, ',', '.'),
	(15, 'BHD', 'Bahraini Dinar', 'BD', 048, 1, 2, 1, 1, ',', '.'),
	(16, 'BIF', 'Burundi Franc', 'BIF', 108, 1, 2, 1, 1, ',', '.'),
	(17, 'BMD', 'Bermudian Dollar', '$', 060, 1, 2, 1, 1, ',', '.'),
	(18, 'BND', 'Brunei Dollar', '$', 096, 1, 2, 1, 1, ',', '.'),
	(19, 'BOB', 'Boliviano', '$b', 068, 1, 2, 1, 1, ',', '.'),
	(20, 'BRL', 'Brazilian Real', 'R$', 986, 1, 2, 1, 1, ',', '.'),
	(21, 'BSD', 'Bahamian Dollar', '$', 044, 1, 2, 1, 1, ',', '.'),
	(22, 'BTN', 'Ngultrum', 'BTN', 064, 1, 2, 1, 1, ',', '.'),
	(23, 'BWP', 'Pula', 'P', 072, 1, 2, 1, 1, ',', '.'),
	(24, 'BYR', 'Belarussian Ruble', 'p.', 974, 1, 2, 1, 1, ',', '.'),
	(25, 'BZD', 'Belize Dollar', 'BZ$', 084, 1, 2, 1, 1, ',', '.'),
	(26, 'CAD', 'Canadian Dollar', '$', 124, 1, 2, 1, 1, ',', '.'),
	(27, 'CDF', 'Congolese Franc', 'CDF', 976, 1, 2, 1, 1, ',', '.'),
	(28, 'CHF', 'Swiss Franc', 'CHF', 756, 1, 2, 1, 1, ',', '.'),
	(29, 'CLP', 'Chilean Peso', '$', 152, 1, 2, 1, 1, ',', '.'),
	(30, 'CNY', 'Yuan Renminbi', '¥', 156, 1, 2, 1, 1, ',', '.'),
	(31, 'COP', 'Colombian Peso', '$', 170, 1, 2, 1, 1, ',', '.'),
	(32, 'CRC', 'Costa Rican Colon', '₡', 188, 1, 2, 1, 1, ',', '.'),
	(33, 'CUC', 'Peso Convertible', 'CUC$', 931, 1, 2, 1, 1, ',', '.'),
	(34, 'CUP', 'Cuban Peso', '₱', 192, 1, 2, 1, 1, ',', '.'),
	(35, 'CVE', 'Cape Verde Escudo', '$', 132, 1, 2, 1, 1, ',', '.'),
	(36, 'CZK', 'Czech Koruna', 'Kč', 203, 1, 2, 1, 1, ',', '.'),
	(37, 'DJF', 'Djibouti Franc', 'DJF', 262, 1, 2, 1, 1, ',', '.'),
	(38, 'DKK', 'Danish Krone', 'kr. ', 208, 0, 2, 1, 1, ',', '.'),
	(39, 'DOP', 'Dominican Peso', 'RD$', 214, 1, 2, 1, 1, ',', '.'),
	(40, 'DZD', 'Algerian Dinar', 'DZD', 012, 1, 2, 1, 1, ',', '.'),
	(41, 'EGP', 'Egyptian Pound', '£', 818, 1, 2, 1, 1, ',', '.'),
	(42, 'ERN', 'Nakfa', 'ERN', 232, 1, 2, 1, 1, ',', '.'),
	(43, 'ETB', 'Ethiopian Birr', 'Br', 230, 1, 2, 1, 1, ',', '.'),
	(44, 'EUR', 'Euro', '€', 978, 1, 2, 1, 1, ',', '.'),
	(45, 'FJD', 'Fiji Dollar', '$', 242, 1, 2, 1, 1, ',', '.'),
	(46, 'FKP', 'Falkland Islands Pound', '£', 238, 1, 2, 1, 1, ',', '.'),
	(47, 'GBP', 'Pound Sterling', '£', 826, 1, 2, 1, 1, ',', '.'),
	(48, 'GEL', 'Lari', 'GEL', 981, 1, 2, 1, 1, ',', '.'),
	(49, 'GHS', 'Ghana Cedi', 'GH¢', 936, 1, 2, 1, 1, ',', '.'),
	(50, 'GIP', 'Gibraltar Pound', '£', 292, 1, 2, 1, 1, ',', '.'),
	(51, 'GMD', 'Gambian Dalasi', 'GMD', 270, 1, 2, 1, 1, ',', '.'),
	(52, 'GNF', 'Guinean Franc', 'GNF', 324, 1, 2, 1, 1, ',', '.'),
	(53, 'GTQ', 'Quetzal', 'Q', 320, 1, 2, 1, 1, ',', '.'),
	(54, 'GYD', 'Guyana Dollar', '$', 328, 1, 2, 1, 1, ',', '.'),
	(55, 'HKD', 'Hong Kong Dollar', '$', 344, 1, 2, 1, 1, ',', '.'),
	(56, 'HNL', 'Lempira', 'L', 340, 1, 2, 1, 1, ',', '.'),
	(57, 'HRK', 'Croatian Kuna', 'kn', 191, 1, 2, 1, 1, ',', '.'),
	(58, 'HTG', 'Haitian Gourde', 'G', 332, 1, 2, 1, 1, ',', '.'),
	(59, 'HUF', 'Forint', 'Ft', 348, 1, 2, 1, 1, ',', '.'),
	(60, 'IDR', 'Rupiah', 'Rp', 360, 1, 2, 1, 1, ',', '.'),
	(61, 'ILS', 'New Israeli Sheqel', '₪', 376, 1, 2, 1, 1, ',', '.'),
	(62, 'INR', 'Indian Rupee', 'INR', 356, 1, 2, 1, 1, ',', '.'),
	(63, 'IQD', 'Iraqi Dinar', 'د.ع ', 368, 1, 2, 1, 1, ',', '.'),
	(64, 'IRR', 'Iranian Rial', '﷼', 364, 1, 2, 1, 1, ',', '.'),
	(65, 'ISK', 'Iceland Krona', 'kr', 352, 1, 2, 1, 1, ',', '.'),
	(66, 'JMD', 'Jamaican Dollar', 'J$', 388, 1, 2, 1, 1, ',', '.'),
	(67, 'JOD', 'Jordanian Dinar', 'JOD', 400, 1, 2, 1, 1, ',', '.'),
	(68, 'JPY', 'Yen', '¥', 392, 1, 2, 1, 1, ',', '.'),
	(69, 'KES', 'Kenyan Shilling', 'KSh', 404, 1, 2, 1, 1, ',', '.'),
	(70, 'KGS', 'Som', 'лв', 417, 1, 2, 1, 1, ',', '.'),
	(71, 'KHR', 'Riel', '៛', 116, 1, 2, 1, 1, ',', '.'),
	(72, 'KMF', 'Comoro Franc', 'KMF', 174, 1, 2, 1, 1, ',', '.'),
	(73, 'KPW', 'North Korean Won', '₩', 408, 1, 2, 1, 1, ',', '.'),
	(74, 'KRW', 'Won', '₩', 410, 1, 2, 1, 1, ',', '.'),
	(75, 'KWD', 'Kuwaiti Dinar', 'ك', 414, 1, 2, 1, 1, ',', '.'),
	(76, 'KYD', 'Cayman Islands Dollar', '$', 136, 1, 2, 1, 1, ',', '.'),
	(77, 'KZT', 'Tenge', 'лв', 398, 1, 2, 1, 1, ',', '.'),
	(78, 'LAK', 'Kip', '₭', 418, 1, 2, 1, 1, ',', '.'),
	(79, 'LBP', 'Lebanese Pound', '£', 422, 1, 2, 1, 1, ',', '.'),
	(80, 'LKR', 'Sri Lanka Rupee', '₨', 144, 1, 2, 1, 1, ',', '.'),
	(81, 'LRD', 'Liberian Dollar', '$', 430, 1, 2, 1, 1, ',', '.'),
	(82, 'LSL', 'Loti', 'LSL', 426, 1, 2, 1, 1, ',', '.'),
	(83, 'LTL', 'Lithuanian Litas', 'Lt', 440, 1, 2, 1, 1, ',', '.'),
	(84, 'LVL', 'Latvian Lats', 'Ls', 428, 1, 2, 1, 1, ',', '.'),
	(85, 'LYD', 'Libyan Dinar', 'LD', 434, 1, 2, 1, 1, ',', '.'),
	(86, 'MAD', 'Moroccan Dirham', 'MAD', 504, 1, 2, 1, 1, ',', '.'),
	(87, 'MDL', 'Moldovan Leu', 'MDL', 498, 1, 2, 1, 1, ',', '.'),
	(88, 'MGA', 'Malagasy Ariary', 'Ar', 969, 1, 2, 1, 1, ',', '.'),
	(89, 'MKD', 'Denar', 'ден', 807, 1, 2, 1, 1, ',', '.'),
	(90, 'MMK', 'Kyat', 'K', 104, 1, 2, 1, 1, ',', '.'),
	(91, 'MNT', 'Tugrik', '₮', 496, 1, 2, 1, 1, ',', '.'),
	(92, 'MOP', 'Pataca', 'MOP$', 446, 1, 2, 1, 1, ',', '.'),
	(93, 'MRO', 'Ouguiya', 'MRO', 478, 1, 2, 1, 1, ',', '.'),
	(94, 'MUR', 'Mauritius Rupee', '₨', 480, 1, 2, 1, 1, ',', '.'),
	(95, 'MVR', 'Rufiyaa', 'MVR', 462, 1, 2, 1, 1, ',', '.'),
	(96, 'MWK', 'Kwacha', 'MK', 454, 1, 2, 1, 1, ',', '.'),
	(97, 'MXN', 'Mexican Peso', '$', 484, 1, 2, 1, 1, ',', '.'),
	(98, 'MYR', 'Malaysian Ringgit', 'RM', 458, 1, 2, 1, 1, ',', '.'),
	(99, 'MZN', 'Mozambique Metical', 'MT', 943, 1, 2, 1, 1, ',', '.'),
	(100, 'NAD', 'Namibia Dollar', '$', 516, 1, 2, 1, 1, ',', '.'),
	(101, 'NGN', 'Naira', '₦', 566, 1, 2, 1, 1, ',', '.'),
	(102, 'NIO', 'Cordoba Oro', 'C$', 558, 1, 2, 1, 1, ',', '.'),
	(103, 'NOK', 'Norwegian Krone', 'kr', 578, 1, 2, 1, 1, ',', '.'),
	(104, 'NPR', 'Nepalese Rupee', '₨', 524, 1, 2, 1, 1, ',', '.'),
	(105, 'NZD', 'New Zealand Dollar', '$', 554, 1, 2, 1, 1, ',', '.'),
	(106, 'OMR', 'Rial Omani', '﷼', 512, 1, 2, 1, 1, ',', '.'),
	(107, 'PAB', 'Balboa', 'B/.', 590, 1, 2, 1, 1, ',', '.'),
	(108, 'PEN', 'Nuevo Sol', 'S/.', 604, 1, 2, 1, 1, ',', '.'),
	(109, 'PGK', 'Kina', 'K', 598, 1, 2, 1, 1, ',', '.'),
	(110, 'PHP', 'Philippine Peso', '₱', 608, 1, 2, 1, 1, ',', '.'),
	(111, 'PKR', 'Pakistan Rupee', '₨', 586, 1, 2, 1, 1, ',', '.'),
	(112, 'PLN', 'Zloty', 'zł', 985, 1, 2, 1, 1, ',', '.'),
	(113, 'PYG', 'Guarani', 'Gs', 600, 1, 2, 1, 1, ',', '.'),
	(114, 'QAR', 'Qatari Rial', '﷼', 634, 1, 2, 1, 1, ',', '.'),
	(115, 'RON', 'New Romanian Leu', 'lei', 946, 1, 2, 1, 1, ',', '.'),
	(116, 'RSD', 'Serbian Dinar', 'Дин.', 941, 1, 2, 1, 1, ',', '.'),
	(117, 'RUB', 'Russian Ruble', 'руб', 643, 1, 2, 1, 1, ',', '.'),
	(118, 'RWF', 'Rwanda Franc', 'RWF', 646, 1, 2, 1, 1, ',', '.'),
	(119, 'SAR', 'Saudi Riyal', '﷼', 682, 1, 2, 1, 1, ',', '.'),
	(120, 'SBD', 'Solomon Islands Dollar', '$', 090, 1, 2, 1, 1, ',', '.'),
	(121, 'SCR', 'Seychelles Rupee', '₨', 690, 1, 2, 1, 1, ',', '.'),
	(122, 'SDG', 'Sudanese Pound', 'SDG', 938, 1, 2, 1, 1, ',', '.'),
	(123, 'SEK', 'Swedish Krona', 'kr', 752, 1, 2, 1, 1, ',', '.'),
	(124, 'SGD', 'Singapore Dollar', '$', 702, 1, 2, 1, 1, ',', '.'),
	(125, 'SHP', 'Saint Helena Pound', '£', 654, 1, 2, 1, 1, ',', '.'),
	(126, 'SLL', 'Leone', 'Le', 694, 1, 2, 1, 1, ',', '.'),
	(127, 'SOS', 'Somali Shilling', 'S', 706, 1, 2, 1, 1, ',', '.'),
	(128, 'SRD', 'Surinam Dollar', '$', 968, 1, 2, 1, 1, ',', '.'),
	(129, 'STD', 'Dobra', 'STD', 678, 1, 2, 1, 1, ',', '.'),
	(130, 'SVC', 'El Salvador Colon', '$', 222, 1, 2, 1, 1, ',', '.'),
	(131, 'SYP', 'Syrian Pound', '£', 760, 1, 2, 1, 1, ',', '.'),
	(132, 'SZL', 'Lilangeni', 'SZL', 748, 1, 2, 1, 1, ',', '.'),
	(133, 'THB', 'Baht', '฿', 764, 1, 2, 1, 1, ',', '.'),
	(134, 'TJS', 'Somoni', 'TJS', 972, 1, 2, 1, 1, ',', '.'),
	(135, 'TMT', 'Turkmenistan New Manat', 'TMT', 934, 1, 2, 1, 1, ',', '.'),
	(136, 'TND', 'Tunisian Dinar', 'TND', 788, 1, 2, 1, 1, ',', '.'),
	(137, 'TOP', 'Pa\'anga', 'T$', 776, 1, 2, 1, 1, ',', '.'),
	(138, 'TRY', 'Turkish Lira', 'TL', 949, 1, 2, 1, 1, ',', '.'),
	(139, 'TTD', 'Trinidad and Tobago Dollar', 'TT$', 780, 1, 2, 1, 1, ',', '.'),
	(140, 'TWD', 'New Taiwan Dollar', 'NT$', 901, 1, 2, 1, 1, ',', '.'),
	(141, 'TZS', 'Tanzanian Shilling', 'TZS', 834, 1, 2, 1, 1, ',', '.'),
	(142, 'UAH', 'Hryvnia', '₴', 980, 1, 2, 1, 1, ',', '.'),
	(143, 'UGX', 'Uganda Shilling', 'USh', 800, 1, 2, 1, 1, ',', '.'),
	(144, 'USD', 'US Dollar', '$', 840, 0, 2, 1, 1, ',', '.'),
	(145, 'UYU', 'Peso Uruguayo', '$U', 858, 1, 2, 1, 1, ',', '.'),
	(146, 'UZS', 'Uzbekistan Sum', 'лв', 860, 1, 2, 1, 1, ',', '.'),
	(147, 'VEF', 'Bolivar', 'Bs', 937, 1, 2, 1, 1, ',', '.'),
	(148, 'VND', 'Dong', '₫', 704, 1, 2, 1, 1, ',', '.'),
	(149, 'VUV', 'Vatu', 'VT', 548, 1, 2, 1, 1, ',', '.'),
	(150, 'WST', 'Tala', '$', 882, 1, 2, 1, 1, ',', '.'),
	(151, 'XAF', 'CFA Franc BEAC', 'XAF', 950, 1, 2, 1, 1, ',', '.'),
	(152, 'XCD', 'East Caribbean Dollar', '$', 951, 1, 2, 1, 1, ',', '.'),
	(153, 'XDR', 'SDR (Special Drawing Right)', 'XDR', 960, 1, 2, 1, 1, ',', '.'),
	(154, 'XOF', 'CFA Franc BCEAO', 'XOF', 952, 1, 2, 1, 1, ',', '.'),
	(155, 'XPF', 'CFP Franc', 'XPF', 953, 1, 2, 1, 1, ',', '.'),
	(156, 'YER', 'Yemeni Rial', '﷼', 886, 1, 2, 1, 1, ',', '.'),
	(157, 'ZAR', 'Rand', 'R', 710, 1, 2, 1, 1, ',', '.'),
	(158, 'ZMK', 'Zambian Kwacha', 'ZK', 894, 1, 2, 1, 1, ',', '.'),
	(159, 'PTS', 'Points', 'Pts', 999, 1, 2, 1, 1, ',', '.');

INSERT INTO `#__redshopb_role_type` (`id`, `name`, `company_role`, `allow_access`, `type`, `limited`, `allowed_rules`, `allowed_rules_main_company`, `allowed_rules_customers`, `allowed_rules_company`, `allowed_rules_department`, `checked_out`, `checked_out_time`, `created_by`, `created_date`, `modified_by`, `modified_date`)
  VALUES (1, 'Company', 1, 0, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00');

INSERT INTO `#__redshopb_role_type` (`id`, `name`, `company_role`, `allow_access`, `type`, `limited`, `allowed_rules`, `allowed_rules_main_company`, `allowed_rules_customers`, `allowed_rules_company`)
  VALUES (2, '01 :: Administrator', 0, 1, 'admin', 0,
    '["core.manage","core.create","core.edit","core.edit.state","core.edit.own","core.delete","redshopb.company.manage.own","redshopb.user.manage.own","redshopb.department.manage.own","redshopb.collection.manage.own","redshopb.order.manage.own","redshopb.order.place","redshopb.user.points","redshopb.user.points.own","redshopb.address.manage.own","redshopb.shopprice.view"]',
    '["redshopb.mainwarehouse.manage"]',
    '["redshopb.layout.manage.own","redshopb.currency.view","redshopb.currency.manage","redshopb.product.view","redshopb.product.manage","redshopb.product.manage.own","redshopb.category.view","redshopb.category.manage","redshopb.category.manage.own","redshopb.company.changetype","redshopb.tag.manage","redshopb.tag.view","redshopb.tag.manage.own"]',
    '["redshopb.company.manage","redshopb.user.manage","redshopb.department.manage","redshopb.collection.manage","redshopb.order.manage","redshopb.user.view","redshopb.user.points","redshopb.company.view","redshopb.department.view","redshopb.collection.view","redshopb.order.view","redshopb.order.impersonate","redshopb.order.history","redshopb.order.comment","redshopb.order.addrequisition","redshopb.order.statusupdate","redshopb.layout.view","redshopb.layout.manage","redshopb.address.manage","redshopb.address.view","redshopb.user.negativewallet"]');

INSERT INTO `#__redshopb_role_type` (`id`, `name`, `company_role`, `allow_access`, `type`, `limited`, `allowed_rules`, `allowed_rules_main_company`, `allowed_rules_own_company`, `allowed_rules_department`)
  VALUES (3, '02 :: Head of Department', 0, 1, 'hod', 0,
    '["core.manage","core.create","core.edit","core.edit.state","core.edit.own","redshopb.user.manage.own","redshopb.department.manage.own","redshopb.order.manage.own","redshopb.order.place","redshopb.address.manage.own","redshopb.shopprice.view"]',
    '["redshopb.user.negativewallet"]',
    '["redshopb.company.view"]',
    '["redshopb.user.manage","redshopb.department.manage","redshopb.order.manage","redshopb.user.view","redshopb.user.points","redshopb.department.view","redshopb.order.view","redshopb.order.impersonate","redshopb.order.history","redshopb.order.comment","redshopb.order.addrequisition","redshopb.order.statusupdate","redshopb.address.manage","redshopb.address.view"]');

INSERT INTO `#__redshopb_role_type` (`id`, `name`, `company_role`, `allow_access`, `type`, `limited`, `allowed_rules`, `allowed_rules_company`)
  VALUES (4, '03 :: Sales Person', 0, 1, 'sales', 1,
    '["core.manage","core.create","core.edit","core.edit.own","redshopb.order.place","redshopb.address.manage.own","redshopb.shopprice.view","redshopb.order.manage.own"]',
    '["redshopb.company.view","redshopb.order.impersonate","redshopb.order.history","redshopb.order.view","redshopb.department.view","redshopb.order.statusupdate","redshopb.order.manage"]');

INSERT INTO `#__redshopb_role_type` (`id`, `name`, `company_role`, `allow_access`, `type`, `limited`, `allowed_rules`, `allowed_rules_own_company`, `allowed_rules_department`)
  VALUES (5, '04 :: Purchaser', 0, 1, 'purchaser', 0,
    '["core.manage","core.create","core.edit","core.edit.own","redshopb.order.place","redshopb.address.manage.own","redshopb.shopprice.view"]',
    '["redshopb.company.view","redshopb.order.impersonate","redshopb.order.history","redshopb.order.view"]',
    '["redshopb.department.view","redshopb.order.statusupdate"]');

INSERT INTO `#__redshopb_role_type` (`id`, `name`, `company_role`, `allow_access`, `type`, `limited`, `allowed_rules`, `allowed_rules_own_company`, `allowed_rules_department`)
  VALUES (6, '05 :: Employee with login', 0, 1, 'employee', 0,
    '["core.manage","core.create","core.edit","core.edit.own","redshopb.address.manage.own","redshopb.order.manage.own","redshopb.order.place","redshopb.shopprice.view"]',
    '["redshopb.company.view"]',
    '["redshopb.department.view"]');

INSERT INTO `#__redshopb_role_type` (`id`, `name`, `company_role`, `allow_access`, `type`, `limited`, `allowed_rules`, `allowed_rules_own_company`, `allowed_rules_department`)
  VALUES (7, '06 :: Employee', 0, 0, 'employee', 0,
		'["redshopb.order.place","redshopb.shopprice.view"]',
    '["redshopb.company.view"]',
    '["redshopb.department.view"]');

INSERT INTO `#__redshopb_config` (`id`, `name`, `value`)
  VALUES
    (NULL, 'thumbnail_width', 144),
    (NULL, 'thumbnail_height', 144),
    (NULL, 'default_currency', 38),
    (NULL, 'default_country_id', 59),
    (NULL, 'date_new_product', 14),
    (NULL, 'encryption_key', 'redshopb');

INSERT INTO `#__redshopb_cron` (`id`, `name`, `parent_id`, `state`, `start_time`, `finish_time`, `next_start`, `lft`, `rgt`, `level`, `alias`, `path`, `parent_alias`, `execute_sync`, `mask_time`, `offset_time`, `params`, `checked_out`, `checked_out_time`) VALUES
	(0, 'ROOT', NULL, 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 'root', '', '', 0, '', '', '', 0, '0000-00-00 00:00:00');

INSERT INTO `#__redshopb_category` (`id`, `parent_id`, `lft`, `rgt`, `level`, `path`, `image`, `name`, `alias`, `state`)
VALUES
	(1, NULL, 0, 1, 0, '', '', 'ROOT', 'root', 1);

INSERT INTO `#__redshopb_tag` (`id`, `parent_id`, `lft`, `rgt`, `level`, `path`, `name`, `alias`, `state`)
VALUES
	(1, NULL, 0, 1, 0, '', 'ROOT', 'root', 1);

INSERT INTO `#__redshopb_acl_section` (`id`, `name`) VALUES
  (1, 'component'),
  (2, 'company');

INSERT INTO `#__redshopb_acl_access` (`id`, `section_id`, `name`, `title`, `description`, `simple`) VALUES
  (null, 1, 'redshopb.company.manage.own', 'COM_REDSHOP_ACTION_COMPANY_MANAGE_OWN', 'COM_REDSHOP_ACTION_COMPANY_MANAGE_OWN_DESC', 0),
  (null, 1, 'redshopb.user.manage.own', 'COM_REDSHOP_ACTION_USER_MANAGE_OWN', 'COM_REDSHOP_ACTION_USER_MANAGE_OWN_DESC', 0),
  (null, 1, 'redshopb.department.manage.own', 'COM_REDSHOP_ACTION_DEPARTMENT_MANAGE_OWN', 'COM_REDSHOP_ACTION_DEPARTMENT_MANAGE_OWN_DESC', 0),
  (null, 1, 'redshopb.collection.manage.own', 'COM_REDSHOP_ACTION_COLLECTION_MANAGE_OWN', 'COM_REDSHOP_ACTION_COLLECTION_MANAGE_DESC_OWN', 0),
  (null, 1, 'redshopb.product.manage.own', 'COM_REDSHOP_ACTION_PRODUCT_MANAGE_OWN', 'COM_REDSHOP_ACTION_PRODUCT_MANAGE_OWN_DESC', 0),
  (null, 1, 'redshopb.order.manage.own', 'COM_REDSHOP_ACTION_ORDER_MANAGE_OWN', 'COM_REDSHOP_ACTION_ORDER_MANAGE_OWN_DESC', 0),
  (null, 1, 'redshopb.category.manage.own', 'COM_REDSHOP_ACTION_CATEGORY_MANAGE_OWN', 'COM_REDSHOP_ACTION_CATEGORY_MANAGE_OWN_DESC', 0),
  (null, 1, 'redshopb.layout.manage.own', 'COM_REDSHOP_ACTION_LAYOUT_MANAGE_OWN', 'COM_REDSHOP_ACTION_LAYOUT_MANAGE_OWN_DESC', 0),
  (null, 1, 'redshopb.currency.view', 'COM_REDSHOP_ACTION_CURRENCY_VIEW', 'COM_REDSHOP_ACTION_CURRENCY_VIEW_DESC', 0),
  (null, 1, 'redshopb.currency.manage', 'COM_REDSHOP_ACTION_CURRENCY_MANAGE', 'COM_REDSHOP_ACTION_CURRENCY_MANAGE_DESC', 1),
  (null, 1, 'redshopb.mainwarehouse.manage', 'COM_REDSHOP_ACTION_MAINWAREHOUSE_MANAGE', 'COM_REDSHOP_ACTION_MAINWAREHOUSE_MANAGE_DESC', 1),
  (null, 1, 'redshopb.tag.manage.own', 'COM_REDSHOP_ACTION_TAG_MANAGE_OWN', 'COM_REDSHOP_ACTION_TAG_MANAGE_OWN_DESC', 0),
  (null, 1, 'redshopb.address.manage.own', 'COM_REDSHOP_ACTION_ADDRESS_MANAGE_OWN', 'COM_REDSHOP_ACTION_ADDRESS_MANAGE_OWN_DESC', 0),
	(null, 1, 'redshopb.shopprice.view', 'COM_REDSHOP_ACTION_SHOPPRICE_VIEW', 'COM_REDSHOP_ACTION_SHOPPRICE_VIEW_DESC', 1);

INSERT INTO `#__redshopb_acl_access` (`id`, `section_id`, `name`, `title`, `description`, `simple`) VALUES
  (null, 2, 'redshopb.company.view', 'COM_REDSHOP_ACTION_COMPANY_VIEW', 'COM_REDSHOP_ACTION_COMPANY_VIEW_DESC', 0),
  (null, 2, 'redshopb.company.manage', 'COM_REDSHOP_ACTION_COMPANY_MANAGE', 'COM_REDSHOP_ACTION_COMPANY_MANAGE_DESC', 1),
  (null, 2, 'redshopb.company.changetype', 'COM_REDSHOP_ACTION_COMPANY_CHANGE_TYPE', 'COM_REDSHOP_ACTION_COMPANY_CHANGE_TYPE_DESC', 0),
  (null, 2, 'redshopb.user.view', 'COM_REDSHOP_ACTION_USER_VIEW', 'COM_REDSHOP_ACTION_USER_VIEW_DESC', 0),
  (null, 2, 'redshopb.user.manage', 'COM_REDSHOP_ACTION_USER_MANAGE', 'COM_REDSHOP_ACTION_USER_MANAGE_DESC', 1),
  (null, 2, 'redshopb.user.points', 'COM_REDSHOP_ACTION_USER_POINTS', 'COM_REDSHOP_ACTION_USER_POINTS_DESC', 1),
  (null, 2, 'redshopb.department.view', 'COM_REDSHOP_ACTION_DEPARTMENT_VIEW', 'COM_REDSHOP_ACTION_DEPARTMENT_VIEW_DESC', 0),
  (null, 2, 'redshopb.department.manage', 'COM_REDSHOP_ACTION_DEPARTMENT_MANAGE', 'COM_REDSHOP_ACTION_DEPARTMENT_MANAGE_DESC', 1),
  (null, 2, 'redshopb.collection.view', 'COM_REDSHOP_ACTION_COLLECTION_VIEW', 'COM_REDSHOP_ACTION_COLLECTION_VIEW_DESC', 0),
  (null, 2, 'redshopb.collection.manage', 'COM_REDSHOP_ACTION_COLLECTION_MANAGE', 'COM_REDSHOP_ACTION_COLLECTION_MANAGE_DESC', 1),
  (null, 2, 'redshopb.product.view', 'COM_REDSHOP_ACTION_PRODUCT_VIEW', 'COM_REDSHOP_ACTION_PRODUCT_VIEW_DESC', 0),
  (null, 2, 'redshopb.product.manage', 'COM_REDSHOP_ACTION_PRODUCT_MANAGE', 'COM_REDSHOP_ACTION_PRODUCT_MANAGE_DESC', 1),
  (null, 2, 'redshopb.order.view', 'COM_REDSHOP_ACTION_ORDER_VIEW', 'COM_REDSHOP_ACTION_ORDER_VIEW_DESC', 0),
  (null, 2, 'redshopb.order.place', 'COM_REDSHOP_ACTION_ORDER_PLACE', 'COM_REDSHOP_ACTION_ORDER_PLACE_DESC', 1),
  (null, 2, 'redshopb.order.manage', 'COM_REDSHOP_ACTION_ORDER_MANAGE', 'COM_REDSHOP_ACTION_ORDER_MANAGE_DESC', 1),
  (null, 2, 'redshopb.order.history', 'COM_REDSHOP_ACTION_ORDER_HISTORY', 'COM_REDSHOP_ACTION_ORDER_HISTORY_DESC', 0),
  (null, 2, 'redshopb.order.comment', 'COM_REDSHOP_ACTION_ORDER_COMMENT', 'COM_REDSHOP_ACTION_ORDER_COMMENT_DESC', 0),
  (null, 2, 'redshopb.order.addrequisition', 'COM_REDSHOP_ACTION_ORDER_ADDREQUISITION', 'COM_REDSHOP_ACTION_ORDER_ADDREQUISITION_DESC', 0),
  (null, 2, 'redshopb.order.statusupdate', 'COM_REDSHOP_ACTION_ORDER_STATUSUPDATE', 'COM_REDSHOP_ACTION_ORDER_STATUSUPDATE_DESC', 1),
  (null, 2, 'redshopb.order.impersonate', 'COM_REDSHOP_ACTION_ORDER_IMPERSONATE', 'COM_REDSHOP_ACTION_ORDER_IMPERSONATE_DESC', 1),
  (null, 2, 'redshopb.category.view', 'COM_REDSHOP_ACTION_CATEGORY_VIEW', 'COM_REDSHOP_ACTION_CATEGORY_VIEW_DESC', 0),
  (null, 2, 'redshopb.category.manage', 'COM_REDSHOP_ACTION_CATEGORY_MANAGE', 'COM_REDSHOP_ACTION_CATEGORY_MANAGE_DESC', 1),
  (null, 2, 'redshopb.layout.view', 'COM_REDSHOP_ACTION_LAYOUT_VIEW', 'COM_REDSHOP_ACTION_LAYOUT_VIEW_DESC', 0),
  (null, 2, 'redshopb.layout.manage', 'COM_REDSHOP_ACTION_LAYOUT_MANAGE', 'COM_REDSHOP_ACTION_LAYOUT_MANAGE_DESC', 1),
  (null, 2, 'redshopb.tag.view', 'COM_REDSHOP_ACTION_TAG_VIEW', 'COM_REDSHOP_ACTION_TAG_VIEW_DESC', 0),
  (null, 2, 'redshopb.tag.manage', 'COM_REDSHOP_ACTION_TAG_MANAGE', 'COM_REDSHOP_ACTION_TAG_MANAGE_DESC', 1),
  (null, 2, 'redshopb.address.view', 'COM_REDSHOP_ACTION_ADDRESS_VIEW', 'COM_REDSHOP_ACTION_ADDRESS_VIEW_DESC', 0),
  (null, 2, 'redshopb.address.manage', 'COM_REDSHOP_ACTION_ADDRESS_MANAGE', 'COM_REDSHOP_ACTION_ADDRESS_MANAGE_DESC', 1),
  (null, 2, 'redshopb.user.negativewallet', 'COM_REDSHOP_ACTION_USER_NEGATIVEWALLET', 'COM_REDSHOP_ACTION_USER_NEGATIVEWALLET_DESC', 1),
  (null, 2, 'redshopb.order.import', 'COM_REDSHOP_ACTION_ORDER_IMPORT', 'COM_REDSHOP_ACTION_ORDER_IMPORT_DESC', 1);

INSERT INTO `#__redshopb_acl_simple_access_xref` (`simple_access_id`, `access_id`, `role_type_id`, `scope`)
	SELECT `sa`.`id`, `a`.`id`, `rt`.`id`, 'company'
	FROM `#__redshopb_acl_access` AS `sa`, `#__redshopb_acl_access` AS `a`, `#__redshopb_role_type` AS `rt`
	WHERE `sa`.`name` = 'redshopb.company.manage' AND `a`.`name` = 'redshopb.company.manage' AND `rt`.`type` IN ('admin', 'sales');
INSERT INTO `#__redshopb_acl_simple_access_xref` (`simple_access_id`, `access_id`, `role_type_id`, `scope`)
	SELECT `sa`.`id`, `a`.`id`, `rt`.`id`, 'global'
	FROM `#__redshopb_acl_access` AS `sa`, `#__redshopb_acl_access` AS `a`, `#__redshopb_role_type` AS `rt`
	WHERE `sa`.`name` = 'redshopb.company.manage' AND `a`.`name` = 'redshopb.company.manage.own' AND `rt`.`type` IN ('admin', 'sales');
INSERT INTO `#__redshopb_acl_simple_access_xref` (`simple_access_id`, `access_id`, `role_type_id`, `scope`)
	SELECT `sa`.`id`, `a`.`id`, `rt`.`id`, 'company'
	FROM `#__redshopb_acl_access` AS `sa`, `#__redshopb_acl_access` AS `a`, `#__redshopb_role_type` AS `rt`
	WHERE `sa`.`name` = 'redshopb.company.manage' AND `a`.`name` = 'redshopb.company.view' AND `rt`.`type` IN ('admin', 'sales');

INSERT INTO `#__redshopb_acl_simple_access_xref` (`simple_access_id`, `access_id`, `role_type_id`, `scope`)
	SELECT `sa`.`id`, `a`.`id`, `rt`.`id`, 'company'
	FROM `#__redshopb_acl_access` AS `sa`, `#__redshopb_acl_access` AS `a`, `#__redshopb_role_type` AS `rt`
	WHERE `sa`.`name` = 'redshopb.user.manage' AND `a`.`name` = 'redshopb.user.manage' AND `rt`.`type` IN ('admin', 'sales');
INSERT INTO `#__redshopb_acl_simple_access_xref` (`simple_access_id`, `access_id`, `role_type_id`, `scope`)
	SELECT `sa`.`id`, `a`.`id`, `rt`.`id`, 'global'
	FROM `#__redshopb_acl_access` AS `sa`, `#__redshopb_acl_access` AS `a`, `#__redshopb_role_type` AS `rt`
	WHERE `sa`.`name` = 'redshopb.user.manage' AND `a`.`name` = 'redshopb.user.manage.own' AND `rt`.`type` IN ('admin', 'sales');
INSERT INTO `#__redshopb_acl_simple_access_xref` (`simple_access_id`, `access_id`, `role_type_id`, `scope`)
	SELECT `sa`.`id`, `a`.`id`, `rt`.`id`, 'company'
	FROM `#__redshopb_acl_access` AS `sa`, `#__redshopb_acl_access` AS `a`, `#__redshopb_role_type` AS `rt`
	WHERE `sa`.`name` = 'redshopb.user.manage' AND `a`.`name` = 'redshopb.user.view' AND `rt`.`type` IN ('admin', 'sales');
INSERT INTO `#__redshopb_acl_simple_access_xref` (`simple_access_id`, `access_id`, `role_type_id`, `scope`)
	SELECT `sa`.`id`, `sa`.`id`, `rt`.`id`, 'company'
	FROM `#__redshopb_acl_access` AS `sa`, `#__redshopb_acl_access` AS `a`, `#__redshopb_role_type` AS `rt`
	WHERE `sa`.`name` = 'redshopb.user.negativewallet' AND `rt`.`type` IN ('admin', 'hod');

INSERT INTO `#__redshopb_acl_simple_access_xref` (`simple_access_id`, `access_id`, `role_type_id`, `scope`)
	SELECT `sa`.`id`, `a`.`id`, `rt`.`id`, 'company'
	FROM `#__redshopb_acl_access` AS `sa`, `#__redshopb_acl_access` AS `a`, `#__redshopb_role_type` AS `rt`
	WHERE `sa`.`name` = 'redshopb.department.manage' AND `a`.`name` = 'redshopb.department.manage' AND `rt`.`type` IN ('admin', 'sales');
INSERT INTO `#__redshopb_acl_simple_access_xref` (`simple_access_id`, `access_id`, `role_type_id`, `scope`)
	SELECT `sa`.`id`, `a`.`id`, `rt`.`id`, 'global'
	FROM `#__redshopb_acl_access` AS `sa`, `#__redshopb_acl_access` AS `a`, `#__redshopb_role_type` AS `rt`
	WHERE `sa`.`name` = 'redshopb.department.manage' AND `a`.`name` = 'redshopb.department.manage.own' AND `rt`.`type` IN ('admin', 'sales');
INSERT INTO `#__redshopb_acl_simple_access_xref` (`simple_access_id`, `access_id`, `role_type_id`, `scope`)
	SELECT `sa`.`id`, `a`.`id`, `rt`.`id`, 'company'
	FROM `#__redshopb_acl_access` AS `sa`, `#__redshopb_acl_access` AS `a`, `#__redshopb_role_type` AS `rt`
	WHERE `sa`.`name` = 'redshopb.department.manage' AND `a`.`name` = 'redshopb.department.view' AND `rt`.`type` IN ('admin', 'sales');

INSERT INTO `#__redshopb_acl_simple_access_xref` (`simple_access_id`, `access_id`, `role_type_id`, `scope`)
	SELECT `sa`.`id`, `a`.`id`, `rt`.`id`, 'company'
	FROM `#__redshopb_acl_access` AS `sa`, `#__redshopb_acl_access` AS `a`, `#__redshopb_role_type` AS `rt`
	WHERE `sa`.`name` = 'redshopb.collection.manage' AND `a`.`name` = 'redshopb.collection.manage' AND `rt`.`type` IN ('admin', 'sales');
INSERT INTO `#__redshopb_acl_simple_access_xref` (`simple_access_id`, `access_id`, `role_type_id`, `scope`)
	SELECT `sa`.`id`, `a`.`id`, `rt`.`id`, 'global'
	FROM `#__redshopb_acl_access` AS `sa`, `#__redshopb_acl_access` AS `a`, `#__redshopb_role_type` AS `rt`
	WHERE `sa`.`name` = 'redshopb.collection.manage' AND `a`.`name` = 'redshopb.collection.manage.own' AND `rt`.`type` IN ('admin', 'sales');
INSERT INTO `#__redshopb_acl_simple_access_xref` (`simple_access_id`, `access_id`, `role_type_id`, `scope`)
	SELECT `sa`.`id`, `a`.`id`, `rt`.`id`, 'company'
	FROM `#__redshopb_acl_access` AS `sa`, `#__redshopb_acl_access` AS `a`, `#__redshopb_role_type` AS `rt`
	WHERE `sa`.`name` = 'redshopb.collection.manage' AND `a`.`name` = 'redshopb.collection.view' AND `rt`.`type` IN ('admin', 'sales');

INSERT INTO `#__redshopb_acl_simple_access_xref` (`simple_access_id`, `access_id`, `role_type_id`, `scope`)
	SELECT `sa`.`id`, `a`.`id`, `rt`.`id`, 'global'
	FROM `#__redshopb_acl_access` AS `sa`, `#__redshopb_acl_access` AS `a`, `#__redshopb_role_type` AS `rt`
	WHERE `sa`.`name` = 'redshopb.product.manage' AND `a`.`name` = 'redshopb.product.manage' AND `rt`.`type` IN ('admin', 'sales');
INSERT INTO `#__redshopb_acl_simple_access_xref` (`simple_access_id`, `access_id`, `role_type_id`, `scope`)
	SELECT `sa`.`id`, `a`.`id`, `rt`.`id`, 'global'
	FROM `#__redshopb_acl_access` AS `sa`, `#__redshopb_acl_access` AS `a`, `#__redshopb_role_type` AS `rt`
	WHERE `sa`.`name` = 'redshopb.product.manage' AND `a`.`name` = 'redshopb.product.manage.own' AND `rt`.`type` IN ('admin', 'sales');
INSERT INTO `#__redshopb_acl_simple_access_xref` (`simple_access_id`, `access_id`, `role_type_id`, `scope`)
	SELECT `sa`.`id`, `a`.`id`, `rt`.`id`, 'global'
	FROM `#__redshopb_acl_access` AS `sa`, `#__redshopb_acl_access` AS `a`, `#__redshopb_role_type` AS `rt`
	WHERE `sa`.`name` = 'redshopb.product.manage' AND `a`.`name` = 'redshopb.product.view' AND `rt`.`type` IN ('admin', 'sales');

INSERT INTO `#__redshopb_acl_simple_access_xref` (`simple_access_id`, `access_id`, `role_type_id`, `scope`)
	SELECT `sa`.`id`, `a`.`id`, `rt`.`id`, 'company'
	FROM `#__redshopb_acl_access` AS `sa`, `#__redshopb_acl_access` AS `a`, `#__redshopb_role_type` AS `rt`
	WHERE `sa`.`name` = 'redshopb.order.manage' AND `a`.`name` = 'redshopb.order.manage' AND `rt`.`type` IN ('admin', 'sales');
INSERT INTO `#__redshopb_acl_simple_access_xref` (`simple_access_id`, `access_id`, `role_type_id`, `scope`)
	SELECT `sa`.`id`, `a`.`id`, `rt`.`id`, 'global'
	FROM `#__redshopb_acl_access` AS `sa`, `#__redshopb_acl_access` AS `a`, `#__redshopb_role_type` AS `rt`
	WHERE `sa`.`name` = 'redshopb.order.manage' AND `a`.`name` = 'redshopb.order.manage.own' AND `rt`.`type` IN ('admin', 'sales');
INSERT INTO `#__redshopb_acl_simple_access_xref` (`simple_access_id`, `access_id`, `role_type_id`, `scope`)
	SELECT `sa`.`id`, `a`.`id`, `rt`.`id`, 'company'
	FROM `#__redshopb_acl_access` AS `sa`, `#__redshopb_acl_access` AS `a`, `#__redshopb_role_type` AS `rt`
	WHERE `sa`.`name` = 'redshopb.order.manage' AND `a`.`name` = 'redshopb.order.view' AND `rt`.`type` IN ('admin', 'sales', 'purchaser');

INSERT INTO `#__redshopb_acl_simple_access_xref` (`simple_access_id`, `access_id`, `role_type_id`, `scope`)
	SELECT `sa`.`id`, `a`.`id`, `rt`.`id`, 'global'
	FROM `#__redshopb_acl_access` AS `sa`, `#__redshopb_acl_access` AS `a`, `#__redshopb_role_type` AS `rt`
	WHERE `sa`.`name` = 'redshopb.category.manage' AND `a`.`name` = 'redshopb.category.manage' AND `rt`.`type` IN ('admin', 'sales');
INSERT INTO `#__redshopb_acl_simple_access_xref` (`simple_access_id`, `access_id`, `role_type_id`, `scope`)
	SELECT `sa`.`id`, `a`.`id`, `rt`.`id`, 'global'
	FROM `#__redshopb_acl_access` AS `sa`, `#__redshopb_acl_access` AS `a`, `#__redshopb_role_type` AS `rt`
	WHERE `sa`.`name` = 'redshopb.category.manage' AND `a`.`name` = 'redshopb.category.manage.own' AND `rt`.`type` IN ('admin', 'sales');
INSERT INTO `#__redshopb_acl_simple_access_xref` (`simple_access_id`, `access_id`, `role_type_id`, `scope`)
	SELECT `sa`.`id`, `a`.`id`, `rt`.`id`, 'global'
	FROM `#__redshopb_acl_access` AS `sa`, `#__redshopb_acl_access` AS `a`, `#__redshopb_role_type` AS `rt`
	WHERE `sa`.`name` = 'redshopb.category.manage' AND `a`.`name` = 'redshopb.category.view' AND `rt`.`type` IN ('admin', 'sales');

INSERT INTO `#__redshopb_acl_simple_access_xref` (`simple_access_id`, `access_id`, `role_type_id`, `scope`)
	SELECT `sa`.`id`, `a`.`id`, `rt`.`id`, 'company'
	FROM `#__redshopb_acl_access` AS `sa`, `#__redshopb_acl_access` AS `a`, `#__redshopb_role_type` AS `rt`
	WHERE `sa`.`name` = 'redshopb.layout.manage' AND `a`.`name` = 'redshopb.layout.manage' AND `rt`.`type` IN ('admin', 'sales');
INSERT INTO `#__redshopb_acl_simple_access_xref` (`simple_access_id`, `access_id`, `role_type_id`, `scope`)
	SELECT `sa`.`id`, `a`.`id`, `rt`.`id`, 'global'
	FROM `#__redshopb_acl_access` AS `sa`, `#__redshopb_acl_access` AS `a`, `#__redshopb_role_type` AS `rt`
	WHERE `sa`.`name` = 'redshopb.layout.manage' AND `a`.`name` = 'redshopb.layout.manage.own' AND `rt`.`type` IN ('admin', 'sales');
INSERT INTO `#__redshopb_acl_simple_access_xref` (`simple_access_id`, `access_id`, `role_type_id`, `scope`)
	SELECT `sa`.`id`, `a`.`id`, `rt`.`id`, 'company'
	FROM `#__redshopb_acl_access` AS `sa`, `#__redshopb_acl_access` AS `a`, `#__redshopb_role_type` AS `rt`
	WHERE `sa`.`name` = 'redshopb.layout.manage' AND `a`.`name` = 'redshopb.layout.view' AND `rt`.`type` IN ('admin', 'sales');

INSERT INTO `#__redshopb_acl_simple_access_xref` (`simple_access_id`, `access_id`, `role_type_id`, `scope`)
	SELECT `sa`.`id`, `a`.`id`, `rt`.`id`, 'global'
	FROM `#__redshopb_acl_access` AS `sa`, `#__redshopb_acl_access` AS `a`, `#__redshopb_role_type` AS `rt`
	WHERE `sa`.`name` = 'redshopb.currency.manage' AND `a`.`name` = 'redshopb.currency.manage' AND `rt`.`type` IN ('admin', 'sales');
INSERT INTO `#__redshopb_acl_simple_access_xref` (`simple_access_id`, `access_id`, `role_type_id`, `scope`)
	SELECT `sa`.`id`, `a`.`id`, `rt`.`id`, 'global'
	FROM `#__redshopb_acl_access` AS `sa`, `#__redshopb_acl_access` AS `a`, `#__redshopb_role_type` AS `rt`
	WHERE `sa`.`name` = 'redshopb.currency.manage' AND `a`.`name` = 'redshopb.currency.view' AND `rt`.`type` IN ('admin', 'sales');

INSERT INTO `#__redshopb_acl_simple_access_xref` (`simple_access_id`, `access_id`, `role_type_id`, `scope`)
	SELECT `sa`.`id`, `a`.`id`, `rt`.`id`, 'global'
	FROM `#__redshopb_acl_access` AS `sa`, `#__redshopb_acl_access` AS `a`, `#__redshopb_role_type` AS `rt`
	WHERE `sa`.`name` = 'redshopb.mainwarehouse.manage' AND `a`.`name` = 'redshopb.mainwarehouse.manage' AND `rt`.`type` IN ('admin', 'sales');

INSERT INTO `#__redshopb_acl_simple_access_xref` (`simple_access_id`, `access_id`, `role_type_id`, `scope`)
	SELECT `sa`.`id`, `a`.`id`, `rt`.`id`, 'global'
	FROM `#__redshopb_acl_access` AS `sa`, `#__redshopb_acl_access` AS `a`, `#__redshopb_role_type` AS `rt`
	WHERE `sa`.`name` = 'redshopb.tag.manage' AND `a`.`name` = 'redshopb.tag.manage' AND `rt`.`type` IN ('admin', 'sales');
INSERT INTO `#__redshopb_acl_simple_access_xref` (`simple_access_id`, `access_id`, `role_type_id`, `scope`)
	SELECT `sa`.`id`, `a`.`id`, `rt`.`id`, 'global'
	FROM `#__redshopb_acl_access` AS `sa`, `#__redshopb_acl_access` AS `a`, `#__redshopb_role_type` AS `rt`
	WHERE `sa`.`name` = 'redshopb.tag.manage' AND `a`.`name` = 'redshopb.tag.manage.own' AND `rt`.`type` IN ('admin', 'sales');
INSERT INTO `#__redshopb_acl_simple_access_xref` (`simple_access_id`, `access_id`, `role_type_id`, `scope`)
	SELECT `sa`.`id`, `a`.`id`, `rt`.`id`, 'global'
	FROM `#__redshopb_acl_access` AS `sa`, `#__redshopb_acl_access` AS `a`, `#__redshopb_role_type` AS `rt`
	WHERE `sa`.`name` = 'redshopb.tag.manage' AND `a`.`name` = 'redshopb.tag.view' AND `rt`.`type` IN ('admin', 'sales');

INSERT INTO `#__redshopb_acl_simple_access_xref` (`simple_access_id`, `access_id`, `role_type_id`, `scope`)
	SELECT `sa`.`id`, `a`.`id`, `rt`.`id`, 'company'
	FROM `#__redshopb_acl_access` AS `sa`, `#__redshopb_acl_access` AS `a`, `#__redshopb_role_type` AS `rt`
	WHERE `sa`.`name` = 'redshopb.address.manage' AND `a`.`name` = 'redshopb.address.manage' AND `rt`.`type` IN ('admin', 'sales');
INSERT INTO `#__redshopb_acl_simple_access_xref` (`simple_access_id`, `access_id`, `role_type_id`, `scope`)
	SELECT `sa`.`id`, `a`.`id`, `rt`.`id`, 'global'
	FROM `#__redshopb_acl_access` AS `sa`, `#__redshopb_acl_access` AS `a`, `#__redshopb_role_type` AS `rt`
	WHERE `sa`.`name` = 'redshopb.address.manage' AND `a`.`name` = 'redshopb.address.manage.own' AND `rt`.`type` IN ('admin', 'sales');
INSERT INTO `#__redshopb_acl_simple_access_xref` (`simple_access_id`, `access_id`, `role_type_id`, `scope`)
	SELECT `sa`.`id`, `a`.`id`, `rt`.`id`, 'company'
	FROM `#__redshopb_acl_access` AS `sa`, `#__redshopb_acl_access` AS `a`, `#__redshopb_role_type` AS `rt`
	WHERE `sa`.`name` = 'redshopb.address.manage' AND `a`.`name` = 'redshopb.address.view' AND `rt`.`type` IN ('admin', 'sales');

INSERT INTO `#__redshopb_acl_simple_access_xref` (`simple_access_id`, `access_id`, `role_type_id`, `scope`)
	SELECT `sa`.`id`, `a`.`id`, `rt`.`id`, 'company'
	FROM `#__redshopb_acl_access` AS `sa`, `#__redshopb_acl_access` AS `a`, `#__redshopb_role_type` AS `rt`
	WHERE `sa`.`name` = 'redshopb.user.points' AND `a`.`name` = 'redshopb.user.points' AND `rt`.`type` IN ('admin', 'sales');

INSERT INTO `#__redshopb_acl_simple_access_xref` (`simple_access_id`, `access_id`, `role_type_id`, `scope`)
	SELECT `sa`.`id`, `a`.`id`, `rt`.`id`, 'company'
	FROM `#__redshopb_acl_access` AS `sa`, `#__redshopb_acl_access` AS `a`, `#__redshopb_role_type` AS `rt`
	WHERE `sa`.`name` = 'redshopb.order.impersonate' AND `a`.`name` = 'redshopb.order.impersonate' AND `rt`.`type` IN ('admin', 'sales', 'purchaser');

INSERT INTO `#__redshopb_acl_simple_access_xref` (`simple_access_id`, `access_id`, `role_type_id`, `scope`)
	SELECT `sa`.`id`, `a`.`id`, `rt`.`id`, 'company'
	FROM `#__redshopb_acl_access` AS `sa`, `#__redshopb_acl_access` AS `a`, `#__redshopb_role_type` AS `rt`
	WHERE `sa`.`name` = 'redshopb.order.statusupdate' AND `a`.`name` = 'redshopb.order.statusupdate' AND `rt`.`type` IN ('admin', 'sales');

INSERT INTO `#__redshopb_acl_simple_access_xref` (`simple_access_id`, `access_id`, `role_type_id`, `scope`)
	SELECT `sa`.`id`, `a`.`id`, `rt`.`id`, 'global'
	FROM `#__redshopb_acl_access` AS `sa`, `#__redshopb_acl_access` AS `a`, `#__redshopb_role_type` AS `rt`
	WHERE `sa`.`name` = 'redshopb.order.place' AND `a`.`name` = 'redshopb.order.place' AND `rt`.`type` IN ('admin', 'sales');

INSERT INTO `#__redshopb_acl_simple_access_xref` (`simple_access_id`, `access_id`, `role_type_id`, `scope`)
	SELECT `sa`.`id`, `a`.`id`, `rt`.`id`, 'global'
	FROM `#__redshopb_acl_access` AS `sa`, `#__redshopb_acl_access` AS `a`, `#__redshopb_role_type` AS `rt`
	WHERE `sa`.`name` = 'redshopb.shopprice.view' AND `a`.`name` = 'redshopb.shopprice.view' AND `rt`.`type` IN ('admin', 'sales');


INSERT INTO `#__redshopb_acl_simple_access_xref` (`simple_access_id`, `access_id`, `role_type_id`, `scope`)
	SELECT `sa`.`id`, `a`.`id`, `rt`.`id`, 'company'
	FROM `#__redshopb_acl_access` AS `sa`, `#__redshopb_acl_access` AS `a`, `#__redshopb_role_type` AS `rt`
	WHERE `sa`.`name` = 'redshopb.company.manage' AND `a`.`name` = 'redshopb.company.manage' AND `rt`.`type` IN ('hod', 'purchaser', 'employee');
INSERT INTO `#__redshopb_acl_simple_access_xref` (`simple_access_id`, `access_id`, `role_type_id`, `scope`)
	SELECT `sa`.`id`, `a`.`id`, `rt`.`id`, 'global'
	FROM `#__redshopb_acl_access` AS `sa`, `#__redshopb_acl_access` AS `a`, `#__redshopb_role_type` AS `rt`
	WHERE `sa`.`name` = 'redshopb.company.manage' AND `a`.`name` = 'redshopb.company.manage.own' AND `rt`.`type` IN ('hod', 'purchaser', 'employee');
INSERT INTO `#__redshopb_acl_simple_access_xref` (`simple_access_id`, `access_id`, `role_type_id`, `scope`)
	SELECT `sa`.`id`, `a`.`id`, `rt`.`id`, 'company'
	FROM `#__redshopb_acl_access` AS `sa`, `#__redshopb_acl_access` AS `a`, `#__redshopb_role_type` AS `rt`
	WHERE `sa`.`name` = 'redshopb.company.manage' AND `a`.`name` = 'redshopb.company.view' AND `rt`.`type` IN ('hod', 'purchaser', 'employee');

INSERT INTO `#__redshopb_acl_simple_access_xref` (`simple_access_id`, `access_id`, `role_type_id`, `scope`)
	SELECT `sa`.`id`, `a`.`id`, `rt`.`id`, 'department'
	FROM `#__redshopb_acl_access` AS `sa`, `#__redshopb_acl_access` AS `a`, `#__redshopb_role_type` AS `rt`
	WHERE `sa`.`name` = 'redshopb.user.manage' AND `a`.`name` = 'redshopb.user.manage' AND `rt`.`type` IN ('hod', 'purchaser', 'employee');
INSERT INTO `#__redshopb_acl_simple_access_xref` (`simple_access_id`, `access_id`, `role_type_id`, `scope`)
	SELECT `sa`.`id`, `a`.`id`, `rt`.`id`, 'global'
	FROM `#__redshopb_acl_access` AS `sa`, `#__redshopb_acl_access` AS `a`, `#__redshopb_role_type` AS `rt`
	WHERE `sa`.`name` = 'redshopb.user.manage' AND `a`.`name` = 'redshopb.user.manage.own' AND `rt`.`type` IN ('hod', 'purchaser', 'employee');
INSERT INTO `#__redshopb_acl_simple_access_xref` (`simple_access_id`, `access_id`, `role_type_id`, `scope`)
	SELECT `sa`.`id`, `a`.`id`, `rt`.`id`, 'department'
	FROM `#__redshopb_acl_access` AS `sa`, `#__redshopb_acl_access` AS `a`, `#__redshopb_role_type` AS `rt`
	WHERE `sa`.`name` = 'redshopb.user.manage' AND `a`.`name` = 'redshopb.user.view' AND `rt`.`type` IN ('hod', 'purchaser', 'employee');

INSERT INTO `#__redshopb_acl_simple_access_xref` (`simple_access_id`, `access_id`, `role_type_id`, `scope`)
	SELECT `sa`.`id`, `a`.`id`, `rt`.`id`, 'department'
	FROM `#__redshopb_acl_access` AS `sa`, `#__redshopb_acl_access` AS `a`, `#__redshopb_role_type` AS `rt`
	WHERE `sa`.`name` = 'redshopb.department.manage' AND `a`.`name` = 'redshopb.department.manage' AND `rt`.`type` IN ('hod', 'purchaser', 'employee');
INSERT INTO `#__redshopb_acl_simple_access_xref` (`simple_access_id`, `access_id`, `role_type_id`, `scope`)
	SELECT `sa`.`id`, `a`.`id`, `rt`.`id`, 'global'
	FROM `#__redshopb_acl_access` AS `sa`, `#__redshopb_acl_access` AS `a`, `#__redshopb_role_type` AS `rt`
	WHERE `sa`.`name` = 'redshopb.department.manage' AND `a`.`name` = 'redshopb.department.manage.own' AND `rt`.`type` IN ('hod', 'purchaser', 'employee');
INSERT INTO `#__redshopb_acl_simple_access_xref` (`simple_access_id`, `access_id`, `role_type_id`, `scope`)
	SELECT `sa`.`id`, `a`.`id`, `rt`.`id`, 'department'
	FROM `#__redshopb_acl_access` AS `sa`, `#__redshopb_acl_access` AS `a`, `#__redshopb_role_type` AS `rt`
	WHERE `sa`.`name` = 'redshopb.department.manage' AND `a`.`name` = 'redshopb.department.view' AND `rt`.`type` IN ('hod', 'purchaser', 'employee');

INSERT INTO `#__redshopb_acl_simple_access_xref` (`simple_access_id`, `access_id`, `role_type_id`, `scope`)
	SELECT `sa`.`id`, `a`.`id`, `rt`.`id`, 'department'
	FROM `#__redshopb_acl_access` AS `sa`, `#__redshopb_acl_access` AS `a`, `#__redshopb_role_type` AS `rt`
	WHERE `sa`.`name` = 'redshopb.collection.manage' AND `a`.`name` = 'redshopb.collection.manage' AND `rt`.`type` IN ('hod', 'purchaser', 'employee');
INSERT INTO `#__redshopb_acl_simple_access_xref` (`simple_access_id`, `access_id`, `role_type_id`, `scope`)
	SELECT `sa`.`id`, `a`.`id`, `rt`.`id`, 'global'
	FROM `#__redshopb_acl_access` AS `sa`, `#__redshopb_acl_access` AS `a`, `#__redshopb_role_type` AS `rt`
	WHERE `sa`.`name` = 'redshopb.collection.manage' AND `a`.`name` = 'redshopb.collection.manage.own' AND `rt`.`type` IN ('hod', 'purchaser', 'employee');
INSERT INTO `#__redshopb_acl_simple_access_xref` (`simple_access_id`, `access_id`, `role_type_id`, `scope`)
	SELECT `sa`.`id`, `a`.`id`, `rt`.`id`, 'department'
	FROM `#__redshopb_acl_access` AS `sa`, `#__redshopb_acl_access` AS `a`, `#__redshopb_role_type` AS `rt`
	WHERE `sa`.`name` = 'redshopb.collection.manage' AND `a`.`name` = 'redshopb.collection.view' AND `rt`.`type` IN ('hod', 'purchaser', 'employee');

INSERT INTO `#__redshopb_acl_simple_access_xref` (`simple_access_id`, `access_id`, `role_type_id`, `scope`)
	SELECT `sa`.`id`, `a`.`id`, `rt`.`id`, 'global'
	FROM `#__redshopb_acl_access` AS `sa`, `#__redshopb_acl_access` AS `a`, `#__redshopb_role_type` AS `rt`
	WHERE `sa`.`name` = 'redshopb.product.manage' AND `a`.`name` = 'redshopb.product.manage' AND `rt`.`type` IN ('hod', 'purchaser', 'employee');
INSERT INTO `#__redshopb_acl_simple_access_xref` (`simple_access_id`, `access_id`, `role_type_id`, `scope`)
	SELECT `sa`.`id`, `a`.`id`, `rt`.`id`, 'global'
	FROM `#__redshopb_acl_access` AS `sa`, `#__redshopb_acl_access` AS `a`, `#__redshopb_role_type` AS `rt`
	WHERE `sa`.`name` = 'redshopb.product.manage' AND `a`.`name` = 'redshopb.product.manage.own' AND `rt`.`type` IN ('hod', 'purchaser', 'employee');
INSERT INTO `#__redshopb_acl_simple_access_xref` (`simple_access_id`, `access_id`, `role_type_id`, `scope`)
	SELECT `sa`.`id`, `a`.`id`, `rt`.`id`, 'global'
	FROM `#__redshopb_acl_access` AS `sa`, `#__redshopb_acl_access` AS `a`, `#__redshopb_role_type` AS `rt`
	WHERE `sa`.`name` = 'redshopb.product.manage' AND `a`.`name` = 'redshopb.product.view' AND `rt`.`type` IN ('hod', 'purchaser', 'employee');

INSERT INTO `#__redshopb_acl_simple_access_xref` (`simple_access_id`, `access_id`, `role_type_id`, `scope`)
	SELECT `sa`.`id`, `a`.`id`, `rt`.`id`, 'department'
	FROM `#__redshopb_acl_access` AS `sa`, `#__redshopb_acl_access` AS `a`, `#__redshopb_role_type` AS `rt`
	WHERE `sa`.`name` = 'redshopb.order.manage' AND `a`.`name` = 'redshopb.order.manage' AND `rt`.`type` IN ('hod', 'purchaser');
INSERT INTO `#__redshopb_acl_simple_access_xref` (`simple_access_id`, `access_id`, `role_type_id`, `scope`)
	SELECT `sa`.`id`, `a`.`id`, `rt`.`id`, 'global'
	FROM `#__redshopb_acl_access` AS `sa`, `#__redshopb_acl_access` AS `a`, `#__redshopb_role_type` AS `rt`
	WHERE `sa`.`name` = 'redshopb.order.manage' AND `a`.`name` = 'redshopb.order.manage.own' AND `rt`.`type` IN ('hod', 'purchaser', 'employee');
INSERT INTO `#__redshopb_acl_simple_access_xref` (`simple_access_id`, `access_id`, `role_type_id`, `scope`)
	SELECT `sa`.`id`, `a`.`id`, `rt`.`id`, 'department'
	FROM `#__redshopb_acl_access` AS `sa`, `#__redshopb_acl_access` AS `a`, `#__redshopb_role_type` AS `rt`
	WHERE `sa`.`name` = 'redshopb.order.manage' AND `a`.`name` = 'redshopb.order.view' AND `rt`.`type` IN ('hod');

INSERT INTO `#__redshopb_acl_simple_access_xref` (`simple_access_id`, `access_id`, `role_type_id`, `scope`)
	SELECT `sa`.`id`, `a`.`id`, `rt`.`id`, 'global'
	FROM `#__redshopb_acl_access` AS `sa`, `#__redshopb_acl_access` AS `a`, `#__redshopb_role_type` AS `rt`
	WHERE `sa`.`name` = 'redshopb.category.manage' AND `a`.`name` = 'redshopb.category.manage' AND `rt`.`type` IN ('hod', 'purchaser', 'employee');
INSERT INTO `#__redshopb_acl_simple_access_xref` (`simple_access_id`, `access_id`, `role_type_id`, `scope`)
	SELECT `sa`.`id`, `a`.`id`, `rt`.`id`, 'global'
	FROM `#__redshopb_acl_access` AS `sa`, `#__redshopb_acl_access` AS `a`, `#__redshopb_role_type` AS `rt`
	WHERE `sa`.`name` = 'redshopb.category.manage' AND `a`.`name` = 'redshopb.category.manage.own' AND `rt`.`type` IN ('hod', 'purchaser', 'employee');
INSERT INTO `#__redshopb_acl_simple_access_xref` (`simple_access_id`, `access_id`, `role_type_id`, `scope`)
	SELECT `sa`.`id`, `a`.`id`, `rt`.`id`, 'global'
	FROM `#__redshopb_acl_access` AS `sa`, `#__redshopb_acl_access` AS `a`, `#__redshopb_role_type` AS `rt`
	WHERE `sa`.`name` = 'redshopb.category.manage' AND `a`.`name` = 'redshopb.category.view' AND `rt`.`type` IN ('hod', 'purchaser', 'employee');

INSERT INTO `#__redshopb_acl_simple_access_xref` (`simple_access_id`, `access_id`, `role_type_id`, `scope`)
	SELECT `sa`.`id`, `a`.`id`, `rt`.`id`, 'company'
	FROM `#__redshopb_acl_access` AS `sa`, `#__redshopb_acl_access` AS `a`, `#__redshopb_role_type` AS `rt`
	WHERE `sa`.`name` = 'redshopb.layout.manage' AND `a`.`name` = 'redshopb.layout.manage' AND `rt`.`type` IN ('hod', 'purchaser', 'employee');
INSERT INTO `#__redshopb_acl_simple_access_xref` (`simple_access_id`, `access_id`, `role_type_id`, `scope`)
	SELECT `sa`.`id`, `a`.`id`, `rt`.`id`, 'global'
	FROM `#__redshopb_acl_access` AS `sa`, `#__redshopb_acl_access` AS `a`, `#__redshopb_role_type` AS `rt`
	WHERE `sa`.`name` = 'redshopb.layout.manage' AND `a`.`name` = 'redshopb.layout.manage.own' AND `rt`.`type` IN ('hod', 'purchaser', 'employee');
INSERT INTO `#__redshopb_acl_simple_access_xref` (`simple_access_id`, `access_id`, `role_type_id`, `scope`)
	SELECT `sa`.`id`, `a`.`id`, `rt`.`id`, 'company'
	FROM `#__redshopb_acl_access` AS `sa`, `#__redshopb_acl_access` AS `a`, `#__redshopb_role_type` AS `rt`
	WHERE `sa`.`name` = 'redshopb.layout.manage' AND `a`.`name` = 'redshopb.layout.view' AND `rt`.`type` IN ('hod', 'purchaser', 'employee');

INSERT INTO `#__redshopb_acl_simple_access_xref` (`simple_access_id`, `access_id`, `role_type_id`, `scope`)
	SELECT `sa`.`id`, `a`.`id`, `rt`.`id`, 'global'
	FROM `#__redshopb_acl_access` AS `sa`, `#__redshopb_acl_access` AS `a`, `#__redshopb_role_type` AS `rt`
	WHERE `sa`.`name` = 'redshopb.currency.manage' AND `a`.`name` = 'redshopb.currency.manage' AND `rt`.`type` IN ('hod', 'purchaser', 'employee');
INSERT INTO `#__redshopb_acl_simple_access_xref` (`simple_access_id`, `access_id`, `role_type_id`, `scope`)
	SELECT `sa`.`id`, `a`.`id`, `rt`.`id`, 'global'
	FROM `#__redshopb_acl_access` AS `sa`, `#__redshopb_acl_access` AS `a`, `#__redshopb_role_type` AS `rt`
	WHERE `sa`.`name` = 'redshopb.currency.manage' AND `a`.`name` = 'redshopb.currency.view' AND `rt`.`type` IN ('hod', 'purchaser', 'employee');

INSERT INTO `#__redshopb_acl_simple_access_xref` (`simple_access_id`, `access_id`, `role_type_id`, `scope`)
	SELECT `sa`.`id`, `a`.`id`, `rt`.`id`, 'global'
	FROM `#__redshopb_acl_access` AS `sa`, `#__redshopb_acl_access` AS `a`, `#__redshopb_role_type` AS `rt`
	WHERE `sa`.`name` = 'redshopb.mainwarehouse.manage' AND `a`.`name` = 'redshopb.mainwarehouse.manage' AND `rt`.`type` IN ('hod', 'purchaser', 'employee');

INSERT INTO `#__redshopb_acl_simple_access_xref` (`simple_access_id`, `access_id`, `role_type_id`, `scope`)
	SELECT `sa`.`id`, `a`.`id`, `rt`.`id`, 'global'
	FROM `#__redshopb_acl_access` AS `sa`, `#__redshopb_acl_access` AS `a`, `#__redshopb_role_type` AS `rt`
	WHERE `sa`.`name` = 'redshopb.tag.manage' AND `a`.`name` = 'redshopb.tag.manage' AND `rt`.`type` IN ('hod', 'purchaser', 'employee');
INSERT INTO `#__redshopb_acl_simple_access_xref` (`simple_access_id`, `access_id`, `role_type_id`, `scope`)
	SELECT `sa`.`id`, `a`.`id`, `rt`.`id`, 'global'
	FROM `#__redshopb_acl_access` AS `sa`, `#__redshopb_acl_access` AS `a`, `#__redshopb_role_type` AS `rt`
	WHERE `sa`.`name` = 'redshopb.tag.manage' AND `a`.`name` = 'redshopb.tag.manage.own' AND `rt`.`type` IN ('hod', 'purchaser', 'employee');
INSERT INTO `#__redshopb_acl_simple_access_xref` (`simple_access_id`, `access_id`, `role_type_id`, `scope`)
	SELECT `sa`.`id`, `a`.`id`, `rt`.`id`, 'global'
	FROM `#__redshopb_acl_access` AS `sa`, `#__redshopb_acl_access` AS `a`, `#__redshopb_role_type` AS `rt`
	WHERE `sa`.`name` = 'redshopb.tag.manage' AND `a`.`name` = 'redshopb.tag.view' AND `rt`.`type` IN ('hod', 'purchaser', 'employee');

INSERT INTO `#__redshopb_acl_simple_access_xref` (`simple_access_id`, `access_id`, `role_type_id`, `scope`)
	SELECT `sa`.`id`, `a`.`id`, `rt`.`id`, 'department'
	FROM `#__redshopb_acl_access` AS `sa`, `#__redshopb_acl_access` AS `a`, `#__redshopb_role_type` AS `rt`
	WHERE `sa`.`name` = 'redshopb.address.manage' AND `a`.`name` = 'redshopb.address.manage' AND `rt`.`type` IN ('hod', 'purchaser');
INSERT INTO `#__redshopb_acl_simple_access_xref` (`simple_access_id`, `access_id`, `role_type_id`, `scope`)
	SELECT `sa`.`id`, `a`.`id`, `rt`.`id`, 'global'
	FROM `#__redshopb_acl_access` AS `sa`, `#__redshopb_acl_access` AS `a`, `#__redshopb_role_type` AS `rt`
	WHERE `sa`.`name` = 'redshopb.address.manage' AND `a`.`name` = 'redshopb.address.manage.own' AND `rt`.`type` IN ('hod', 'purchaser', 'employee');
INSERT INTO `#__redshopb_acl_simple_access_xref` (`simple_access_id`, `access_id`, `role_type_id`, `scope`)
	SELECT `sa`.`id`, `a`.`id`, `rt`.`id`, 'department'
	FROM `#__redshopb_acl_access` AS `sa`, `#__redshopb_acl_access` AS `a`, `#__redshopb_role_type` AS `rt`
	WHERE `sa`.`name` = 'redshopb.address.manage' AND `a`.`name` = 'redshopb.address.view' AND `rt`.`type` IN ('hod', 'purchaser');

INSERT INTO `#__redshopb_acl_simple_access_xref` (`simple_access_id`, `access_id`, `role_type_id`, `scope`)
	SELECT `sa`.`id`, `a`.`id`, `rt`.`id`, 'department'
	FROM `#__redshopb_acl_access` AS `sa`, `#__redshopb_acl_access` AS `a`, `#__redshopb_role_type` AS `rt`
	WHERE `sa`.`name` = 'redshopb.user.points' AND `a`.`name` = 'redshopb.user.points' AND `rt`.`type` IN ('hod', 'purchaser', 'employee');

INSERT INTO `#__redshopb_acl_simple_access_xref` (`simple_access_id`, `access_id`, `role_type_id`, `scope`)
	SELECT `sa`.`id`, `a`.`id`, `rt`.`id`, 'department'
	FROM `#__redshopb_acl_access` AS `sa`, `#__redshopb_acl_access` AS `a`, `#__redshopb_role_type` AS `rt`
	WHERE `sa`.`name` = 'redshopb.order.impersonate' AND `a`.`name` = 'redshopb.order.impersonate' AND `rt`.`type` IN ('hod', 'employee');

INSERT INTO `#__redshopb_acl_simple_access_xref` (`simple_access_id`, `access_id`, `role_type_id`, `scope`)
	SELECT `sa`.`id`, `a`.`id`, `rt`.`id`, 'department'
	FROM `#__redshopb_acl_access` AS `sa`, `#__redshopb_acl_access` AS `a`, `#__redshopb_role_type` AS `rt`
	WHERE `sa`.`name` = 'redshopb.order.statusupdate' AND `a`.`name` = 'redshopb.order.statusupdate' AND `rt`.`type` IN ('hod', 'purchaser', 'employee');

INSERT INTO `#__redshopb_acl_simple_access_xref` (`simple_access_id`, `access_id`, `role_type_id`, `scope`)
	SELECT `sa`.`id`, `a`.`id`, `rt`.`id`, 'global'
	FROM `#__redshopb_acl_access` AS `sa`, `#__redshopb_acl_access` AS `a`, `#__redshopb_role_type` AS `rt`
	WHERE `sa`.`name` = 'redshopb.order.place' AND `a`.`name` = 'redshopb.order.place' AND `rt`.`type` IN ('hod', 'purchaser', 'employee');

INSERT INTO `#__redshopb_acl_simple_access_xref` (`simple_access_id`, `access_id`, `role_type_id`, `scope`)
	SELECT `sa`.`id`, `a`.`id`, `rt`.`id`, 'global'
	FROM `#__redshopb_acl_access` AS `sa`, `#__redshopb_acl_access` AS `a`, `#__redshopb_role_type` AS `rt`
	WHERE `sa`.`name` = 'redshopb.shopprice.view' AND `a`.`name` = 'redshopb.shopprice.view' AND `rt`.`type` IN ('hod', 'purchaser', 'employee');

INSERT INTO `#__redshopb_acl_simple_access_xref` (`simple_access_id`, `access_id`, `role_type_id`, `scope`)
	SELECT `sa`.`id`, `a`.`id`, `rt`.`id`, 'global'
	FROM `#__redshopb_acl_access` AS `sa`, `#__redshopb_acl_access` AS `a`, `#__redshopb_role_type` AS `rt`
	WHERE `sa`.`name` = 'redshopb.order.import' AND `a`.`name` = 'redshopb.order.import' AND `rt`.`type` IN ('admin', 'hod', 'sales', 'purchaser', 'employee');

INSERT INTO `#__redshopb_type` (`id`, `name`, `alias`, `value_type`, `field_name`, `multiple`) VALUES
  (1, 'Textbox - string', 'textboxstring', 'string_value', 'text', 1),
  (2, 'Textbox - float', 'textboxfloat', 'float_value', 'text', 1),
  (3, 'Textbox - int', 'textboxint', 'int_value', 'text', 1),
  (4, 'Textbox - text', 'textboxtext', 'text_value', 'multipleeditor', 1),
  (5, 'Dropdown - single', 'dropdownsingle', 'field_value', 'rList', 0),
  (6, 'Dropdown - multiple', 'dropdownmultiple', 'field_value', 'rList', 1),
  (7, 'Checkbox', 'checkbox', 'field_value', 'checkboxes', 1),
  (8, 'Radio', 'radio', 'field_value', 'radio', 0),
  (9, 'Scale', 'scale', 'field_value', 'range', 0),
  (10, 'Date', 'date', 'string_value', 'rdatepicker', 1),
  (11, 'Radio - boolean', 'radioboolean', 'int_value', 'radioRedshopb', 0),
  (12, 'Documents', 'documents', 'string_value', 'mediaRedshopb', 1),
  (13, 'Videos', 'videos', 'string_value', 'mediaRedshopb', 1),
  (14, 'Images', 'field-images', 'string_value', 'mediaRedshopb', 1),
  (15, 'Div elements - Single', 'divelements-single', 'field_value', 'rList', 1),
  (16, 'Div elements - Multiple', 'divelements-multiple', 'field_value', 'rList', 1),
  (17, 'Radio - Yes', 'radioyes', 'int_value', 'radioRedshopb', 0),
  (18, 'Files', 'files', 'string_value', 'mediaRedshopb', 1),
	(19, 'Range', 'range', 'string_value', 'aesECRange', 0)
;

INSERT INTO `#__redshopb_template` (`id`, `name`, `alias`, `template_group`, `scope`, `content`, `state`, `default`, `editable`, `checked_out`, `checked_out_time`, `created_by`, `created_date`, `modified_by`, `modified_date`, `params`, `description`) VALUES
	(1, 'Generic mail template', 'generic-mail-template', 'email', 'email', '', 1, 1, 0, NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00','', 'COM_REDSHOPB_TEMPLATE_GENERIC_MAIL_TEMPLATE'),
	(2, 'Generic Product Template', 'product', 'shop', 'product', '', 1, 1, 0, NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00', '', 'COM_REDSHOPB_TEMPLATE_PRODUCT'),
	(3, 'Generic Category Template', 'category', 'shop', 'category', '', 1, 1, 0, NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00', '', 'COM_REDSHOPB_TEMPLATE_CATEGORY'),
	(4, 'Send Offer', 'send-offer', 'email', 'offer', '', 1, 1, 0, NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00', '{"0":{"mail_subject":"Offer Mail"}}', 'COM_REDSHOPB_TEMPLATE_SEND_OFFER'),
	(5, 'Activation Email', 'activation-email', 'email', 'activation-email', '', 1, 1, 0, NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00', '{"0":{"mail_subject":"Activation Email"}}', 'COM_REDSHOPB_TEMPLATE_ACTIVATION_EMAIL'),
	(6, 'Product List', 'product-list', 'shop', 'product-list', '', 1, 1, 0, NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00', '', 'COM_REDSHOPB_TEMPLATE_PRODUCT_LIST'),
	(7, 'Product List Collection', 'product-list-collection', 'shop', 'product-list-collection', '', 1, 1, 0, NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00', '', 'COM_REDSHOPB_TEMPLATE_PRODUCT_LIST_COLLECTION'),
	(8, 'Product List Style Grid', 'grid', 'shop', 'product-list-style', '', 1, 0, 0, NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00', '', 'COM_REDSHOPB_TEMPLATE_GRID'),
	(9, 'Product List Style List', 'list', 'shop', 'product-list-style', '', 1, 1, 0, NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00', '', 'COM_REDSHOPB_TEMPLATE_LIST'),
	(10, 'Generic send-to-friend mail template', 'send-to-friend', 'email', 'send-to-friend', '', 1, 1, 0, NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00', '', 'COM_REDSHOPB_TEMPLATE_SEND_TO_FRIEND'),
	(11, 'Generic list product template', 'list-element', 'shop', 'list-product', '', 1, 1, 0, NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00', '', 'COM_REDSHOPB_TEMPLATE_LIST_ELEMENT'),
	(12, 'Generic grid product template', 'grid-element', 'shop', 'grid-product', '', 1, 1, 0, NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00', '', 'COM_REDSHOPB_TEMPLATE_GRID_ELEMENT'),
	(13, 'Generic Print Product Template', 'product-print', 'shop', 'product-print', '', 1, 1, 0, NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00', '', 'COM_REDSHOPB_TEMPLATE_PRODUCT_PRINT'),
	(14, 'New user added email template', 'user-added', 'email', 'user-added', '', 1, 1, 0, NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00', '', 'COM_REDSHOPB_TEMPLATE_USER_ADDED'),
	(15, 'Admin approval email', 'admin-approval', 'email', 'admin-approval', '', 1, 1, 0, NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00', '', 'COM_REDSHOPB_TEMPLATE_ADMIN_APPROVED'),
	(16, 'User approved email', 'user-approved', 'email', 'user-approved', '', 1, 1, 0, NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00', '', 'COM_REDSHOPB_TEMPLATE_USER_APPROVED'),
	(17, 'User notify after register', 'user-approve-after-register', 'email', 'user-approve-after-register', '', 1, 1, 0, NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00', '', 'COM_REDSHOPB_TEMPLATE_USER_NOTIFY_AFTER_REGISTER'),
	(18, 'Product List Massive', 'product-list-massive', 'shop', 'product-list-massive', '', 1, 1, 0, NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00', '', 'COM_REDSHOPB_TEMPLATE_PRODUCT_LIST_MASSIVE'),
	(19, 'Product List Style Massive', 'massive', 'shop', 'product-list-style', '', 1, 0, 0, NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00', '', 'COM_REDSHOPB_TEMPLATE_MASSIVE'),
	(20, 'Generic massive product template', 'massive-element', 'shop', 'massive-product', '', 1, 1, 0, NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00', '', 'COM_REDSHOPB_TEMPLATE_MASSIVE_ELEMENT'),
	(21, 'Product variants modal template', 'product-variants', 'shop', 'product-variants', '', 1, 1, 0, NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00', '', 'COM_REDSHOPB_TEMPLATE_PROUCT_VARIANTS_MODAL');

INSERT INTO `#__redshopb_holiday` (`id`, `name`, `day`, `month`, `year`, `country_id`, `checked_out`, `checked_out_time`, `created_by`, `created_date`, `modified_by`, `modified_date`) VALUES
    (1, 'New Years Day', 1, 1, NULL, 59, NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00'),
    (2, 'Maundy Thursday', 13, 4, 2017, 59, NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00'),
    (3, 'Good Friday', 14, 4, 2017, 59, NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00'),
    (4, 'Easter Monday', 17, 4, 2017, 59, NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00'),
    (5, 'Great Prayer Day', 12, 5, 2017, 59, NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00'),
    (6, 'Ascension Day', 25, 5, 2017, 59, NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00'),
    (7, 'Whit Sunday', 4, 6, 2017, 59, NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00'),
    (8, 'Whit Monday', 5, 6, 2017, 59, NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00'),
    (9, 'Christmas Eve Day', 24, 12, NULL, 59, NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00'),
    (10, 'Christmas Day', 25, 12, NULL, 59, NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00'),
    (11, '2nd Christmas Day', 26, 12, NULL, 59, NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00', NULL, '0000-00-00 00:00:00');

INSERT INTO `#__redshopb_calc_type` (`id`, `name`) VALUES
  (1, 'weight'),
  (2, 'volume');

SET FOREIGN_KEY_CHECKS = 1;
