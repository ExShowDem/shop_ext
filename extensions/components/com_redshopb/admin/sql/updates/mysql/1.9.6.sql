SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS `#__redshopb_state` (
	`id`               INT(10) UNSIGNED     NOT NULL AUTO_INCREMENT,
	`country_id`       SMALLINT(5) UNSIGNED NOT NULL,
	`name`             VARCHAR(255)         NOT NULL,
	`alpha2`           VARCHAR(2)           NOT NULL,
	`alpha3`           VARCHAR(3)           NOT NULL,
	`company_id`       INT(10) UNSIGNED     NULL     DEFAULT NULL,
	`checked_out`      INT(11)                       DEFAULT NULL,
	`checked_out_time` DATETIME                      DEFAULT NULL,
	PRIMARY KEY (`id`),
	INDEX `#__rs_state_fk1` (`country_id`),
	INDEX `#__rs_state_fk2` (`checked_out`),
	INDEX `#__rs_state_fk3` (`company_id`),
	UNIQUE `#__rs_state_fk4` (`alpha2`, `country_id`, `company_id`),
	UNIQUE `#__rs_state_fk5` (`alpha3`, `country_id`, `company_id`),
	CONSTRAINT `#__redshopb_state_ibfk_2`
	FOREIGN KEY (`checked_out`)
	REFERENCES `#__users` (`id`)
		ON DELETE SET NULL
		ON UPDATE CASCADE,
	CONSTRAINT `#__redshopb_state_ibfk_1`
	FOREIGN KEY (`country_id`)
	REFERENCES `#__redshopb_country` (`id`)
		ON DELETE CASCADE
		ON UPDATE CASCADE,
	CONSTRAINT `#__redshopb_state_ibfk_3`
	FOREIGN KEY (`company_id`)
	REFERENCES `#__redshopb_company` (`id`)
		ON DELETE CASCADE
		ON UPDATE CASCADE
)
	ENGINE = InnoDB
	DEFAULT CHARSET = utf8;

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

CREATE TABLE IF NOT EXISTS `#__redshopb_tax_group` (
	`id`               INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`name`             VARCHAR(255)     NOT NULL,
	`state`            TINYINT(4)       NOT NULL,
	`company_id`       INT(10) UNSIGNED          DEFAULT NULL,
	`checked_out`      INT(11)                   DEFAULT NULL,
	`checked_out_time` DATETIME                  DEFAULT NULL,
	PRIMARY KEY (`id`),
	INDEX `#__rs_taxgr_fk1` (`company_id`),
	INDEX `#__rs_taxgr_fk2` (`checked_out`),
	CONSTRAINT `#__redshopb_tax_group_ibfk_2`
	FOREIGN KEY (`checked_out`)
	REFERENCES `#__users` (`id`)
		ON DELETE SET NULL
		ON UPDATE CASCADE,
	CONSTRAINT `#__redshopb_tax_group_ibfk_1`
	FOREIGN KEY (`company_id`)
	REFERENCES `#__redshopb_company` (`id`)
		ON DELETE CASCADE
		ON UPDATE CASCADE
)
	ENGINE = InnoDB
	DEFAULT CHARSET = utf8;

ALTER TABLE `#__redshopb_tax`
	ADD `country_id` SMALLINT(4) UNSIGNED DEFAULT NULL
	AFTER `state`,
	ADD `state_id` INT(10) UNSIGNED DEFAULT NULL
	AFTER `country_id`,
	ADD `is_eu_country` TINYINT(4) NOT NULL DEFAULT '0'
	AFTER `state_id`,
	ADD `company_id` INT(10) UNSIGNED NULL DEFAULT NULL
	AFTER `is_eu_country`,
	ADD INDEX `#__rs_t_fk5_idx` (`country_id`),
	ADD INDEX `#__rs_t_fk6_idx` (`state_id`),
	ADD INDEX `#__rs_t_fk7_idx` (`company_id`),
	ADD CONSTRAINT `#__redshopb_tax_ibfk_2`
FOREIGN KEY (`country_id`)
REFERENCES `#__redshopb_country` (`id`)
	ON DELETE CASCADE
	ON UPDATE CASCADE,
	ADD CONSTRAINT `#__redshopb_tax_ibfk_1`
FOREIGN KEY (`state_id`)
REFERENCES `#__redshopb_state` (`id`)
	ON DELETE CASCADE
	ON UPDATE CASCADE,
	ADD CONSTRAINT `#__redshopb_tax_ibfk_3`
FOREIGN KEY (`company_id`)
REFERENCES `#__redshopb_company` (`id`)
	ON DELETE CASCADE
	ON UPDATE CASCADE,
	CHANGE `price_debtor_group_id` `price_debtor_group_id` INT(10) UNSIGNED NULL DEFAULT NULL;

CREATE TABLE IF NOT EXISTS `#__redshopb_tax_group_xref` (
	`tax_group_id` INT(10) UNSIGNED NOT NULL,
	`tax_id`       INT(10) UNSIGNED NOT NULL,
	INDEX `#__rs_tgx_fk1_idx` (`tax_group_id`),
	INDEX `#__rs_tgx_fk2_idx` (`tax_id`),
	CONSTRAINT `#__redshopb_tax_group_xref_ibfk_2`
	FOREIGN KEY (`tax_id`)
	REFERENCES `#__redshopb_tax` (`id`)
		ON DELETE CASCADE
		ON UPDATE CASCADE,
	CONSTRAINT `#__redshopb_tax_group_xref_ibfk_1`
	FOREIGN KEY (`tax_group_id`)
	REFERENCES `#__redshopb_tax_group` (`id`)
		ON DELETE CASCADE
		ON UPDATE CASCADE
)
	ENGINE = InnoDB
	DEFAULT CHARSET = utf8;

ALTER TABLE `#__redshopb_product`
	ADD `tax_group_id` INT(10) UNSIGNED NULL DEFAULT NULL
	AFTER `company_id`,
	ADD INDEX `#__rs_prod_fk10` (`tax_group_id`),
	ADD CONSTRAINT `#__redshopb_product_ibfk_1`
FOREIGN KEY (`tax_group_id`)
REFERENCES `#__redshopb_tax_group` (`id`)
	ON DELETE SET NULL
	ON UPDATE CASCADE;

ALTER TABLE `#__redshopb_company`
	ADD `tax_group_id` INT(10) UNSIGNED NULL DEFAULT NULL
	AFTER `stockroom_verification`,
	ADD `tax_based_on` VARCHAR(100) NOT NULL
	AFTER `tax_group_id`,
	ADD `calculate_vat_on` VARCHAR(100) NOT NULL
	AFTER `tax_based_on`,
	ADD `tax_exempt` INT(4) NOT NULL DEFAULT '0'
	AFTER `calculate_vat_on`,
	ADD `customer_tax_exempt` TINYINT(4) NOT NULL DEFAULT '0'
	AFTER `tax_exempt`,
	ADD `vat_number` VARCHAR(255) NOT NULL
	AFTER `customer_tax_exempt`,
	ADD INDEX `#__rs_company_fk9` (`tax_group_id`),
	ADD CONSTRAINT `#__redshopb_company_ibfk_1`
FOREIGN KEY (`tax_group_id`)
REFERENCES `#__redshopb_tax_group` (`id`)
	ON DELETE SET NULL
	ON UPDATE CASCADE;

ALTER TABLE `#__redshopb_address`
	ADD `state_id` INT(10) UNSIGNED NULL DEFAULT NULL
	AFTER `country_id`,
	ADD INDEX `#__rs_address_fk5` (`state_id`),
	ADD CONSTRAINT `#__redshopb_address_ibfk_1`
FOREIGN KEY (`state_id`)
REFERENCES `#__redshopb_state` (`id`)
	ON DELETE RESTRICT
	ON UPDATE CASCADE;

ALTER TABLE `#__redshopb_country`
	ADD `eu_zone` SMALLINT(4) NOT NULL DEFAULT '0'
	AFTER `numeric`,
	ADD `company_id` INT(10) UNSIGNED DEFAULT NULL
	AFTER `eu_zone`,
	ADD `checked_out` INT(11) DEFAULT NULL
	AFTER `company_id`,
	ADD `checked_out_time` DATETIME DEFAULT NULL
	AFTER `checked_out`,
	DROP INDEX `idx_numeric`,
	ADD UNIQUE `idx_numeric` (`numeric`, `company_id`),
	DROP INDEX `idx_name`,
	ADD UNIQUE `idx_name` (`name`, `company_id`),
	DROP INDEX `idx_alpha2`,
	ADD UNIQUE `idx_alpha2` (`alpha2`, `company_id`),
	DROP INDEX `idx_alpha3`,
	ADD UNIQUE `idx_alpha3` (`alpha3`, `company_id`),
	ADD INDEX `#__rs_country_fk1` (`company_id`),
	ADD INDEX `#__rs_country_fk2` (`checked_out`),
	ADD CONSTRAINT `#__redshopb_country_ibfk_1`
FOREIGN KEY (`company_id`)
REFERENCES `#__redshopb_company` (`id`)
	ON DELETE CASCADE
	ON UPDATE CASCADE,
	ADD CONSTRAINT `#__redshopb_country_ibfk_2`
FOREIGN KEY (`checked_out`)
REFERENCES `#__users` (`id`)
	ON DELETE SET NULL
	ON UPDATE CASCADE;

-- VAT alpha2 for Greek is EL
UPDATE `#__redshopb_country`
SET `alpha2` = 'EL'
WHERE `alpha2` = 'GR';

UPDATE `#__redshopb_country`
SET `eu_zone` = '1'
WHERE `alpha2` IN
			('BE', 'LU', 'MT', 'AT', 'CZ', 'DK', 'FI', 'FR', 'HU', 'IT', 'EL', 'BG', 'LV', 'HR', 'DE', 'LT', 'NL', 'CY', 'EE', 'IE', 'ES', 'NL', 'CZ', 'GB', 'CY', 'HR', 'AT', 'PL');

ALTER TABLE `#__redshopb_order`
	ADD `delivery_address_state` VARCHAR(255) NOT NULL
	AFTER `delivery_address_country_code`,
	ADD `delivery_address_state_code` CHAR(2) NOT NULL DEFAULT ''
	AFTER `delivery_address_state`;

CREATE TABLE IF NOT EXISTS `#__redshopb_order_tax` (
	`id`         INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`order_id`   INT(10) UNSIGNED NOT NULL,
	`name`       VARCHAR(255)     NOT NULL,
	`tax_rate`   DECIMAL(10, 4)   NOT NULL,
	`price`      DECIMAL(10, 2)   NOT NULL,
	`product_id` INT(10) UNSIGNED          DEFAULT NULL,
	PRIMARY KEY (`id`),
	KEY `#__rs_order_tx_fk1` (`order_id`),
	CONSTRAINT `#__redshopb_order_tax_ibfk_1`
	FOREIGN KEY (`order_id`)
	REFERENCES `#__redshopb_order` (`id`)
		ON DELETE CASCADE
		ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8;

ALTER TABLE `#__redshopb_tax`
DROP FOREIGN KEY `#__rs_t_fk1`,
DROP INDEX `#__rs_t_fk1_idx`,
DROP `price_debtor_group_id`;

SET FOREIGN_KEY_CHECKS = 1;
