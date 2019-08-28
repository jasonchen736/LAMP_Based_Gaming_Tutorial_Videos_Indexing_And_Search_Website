CREATE TABLE `adminGroupAccess` (
  `adminGroupAccessID` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `adminGroupID` INTEGER UNSIGNED NOT NULL,
  `access` VARCHAR(10) NOT NULL,
  PRIMARY KEY (`adminGroupAccessID`),
  INDEX `adminGroupID_access`(`adminGroupID`, `access`),
  INDEX `access`(`access`)
)
ENGINE = InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `adminGroups` (
  `adminGroupID` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`adminGroupID`),
  UNIQUE INDEX `name`(`name`)
)
ENGINE = InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `adminUserAccess` (
  `adminUserAccessID` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `adminUserID` INTEGER UNSIGNED NOT NULL,
  `access` VARCHAR(10) NOT NULL,
  PRIMARY KEY (`adminUserAccessID`),
  INDEX `adminUserID_access`(`adminUserID`, `access`),
  INDEX `access`(`access`)
)
ENGINE = InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `adminUserGroupMap` (
  `adminUserID` INTEGER UNSIGNED NOT NULL,
  `adminGroupID` INTEGER UNSIGNED NOT NULL,
  PRIMARY KEY (`adminUserID`, `adminGroupID`),
  INDEX `adminGroupID`(`adminGroupID`)
)
ENGINE = InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE  `adminUsers` (
  `adminUserID` int(10) unsigned NOT NULL auto_increment,
  `email` varchar(100) NOT NULL,
  `login` varchar(45) NOT NULL,
  `password` varchar(46) NOT NULL,
  `name` varchar(45) NOT NULL,
  `status` enum('active','inactive') NOT NULL default 'active',
  `created` datetime NOT NULL,
  PRIMARY KEY  (`adminUserID`),
  UNIQUE KEY `login` (`login`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `adminUsers` VALUES (1, '', 'admin', 'fe7813ff2520c86f22c52eb2df93965ddbd5bb3f2f703d', 'admin', 'active', NOW());
INSERT INTO `adminUserAccess` (`adminUserID`, `access`) VALUES (1, 'SUPERADMIN');

CREATE TABLE  `ads` (
  `adID` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  `location` enum('right column') NOT NULL,
  `url` text NOT NULL,
  `content` text NOT NULL,
  `status` enum('active','inactive') NOT NULL,
  `poster` varchar(10) NOT NULL,
  `posterID` int(10) unsigned NOT NULL,
  `posted` DATETIME NOT NULL,
  `lastModified` datetime NOT NULL,
  PRIMARY KEY  (`adID`),
  KEY `name` (`name`),
  KEY `url` (`url`(255)),
  KEY `location_status` (`location`,`status`),
  KEY `status_location` (`status`,`location`),
  KEY `poster`(`poster`),
  KEY `posted`(`posted`),
  KEY `posterID_poster_posted`(`posterID`, `poster`, `posted`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE  `adsHistory` (
  `adsHistoryID` int(10) unsigned NOT NULL auto_increment,
  `recordEditor` varchar(10) NOT NULL,
  `recordEditorID` int(10) unsigned NOT NULL,
  `action` varchar(10) NOT NULL,
  `comments` text NOT NULL,
  `adID` int(10) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `location` enum('right column') NOT NULL,
  `url` text NOT NULL,
  `content` text NOT NULL,
  `status` enum('active','inactive') NOT NULL,
  `poster` varchar(10) NOT NULL,
  `posterID` int(10) unsigned NOT NULL,
  `posted` DATETIME NOT NULL,
  `lastModified` datetime NOT NULL,
  `effectiveThrough` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY (`adsHistoryID`),
  KEY `postID`(`adID`),
  KEY `lastModified_effectiveThrough` (`lastModified`,`effectiveThrough`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE  `content` (
  `contentID` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `metaDescription` TEXT NOT NULL,
  `metaKeywords` TEXT NOT NULL,
  `status` ENUM('visible', 'isolated', 'disabled') NOT NULL DEFAULT 'visible',
  `dateAdded` datetime NOT NULL,
  `lastModified` datetime NOT NULL,
  PRIMARY KEY  (`contentID`),
  UNIQUE KEY `name` USING BTREE (`name`),
  KEY `status` USING BTREE (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE  `contentHistory` (
  `contentHistoryID` int(10) unsigned NOT NULL auto_increment,
  `recordEditor` varchar(10) NOT NULL,
  `recordEditorID` int(10) unsigned NOT NULL,
  `action` varchar(10) NOT NULL,
  `comments` text NOT NULL,
  `contentID` int(10) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `metaDescription` TEXT NOT NULL,
  `metaKeywords` TEXT NOT NULL,
  `status` ENUM('visible', 'isolated', 'disabled') NOT NULL DEFAULT 'visible',
  `dateAdded` datetime NOT NULL,
  `lastModified` datetime NOT NULL,
  `effectiveThrough` datetime NOT NULL,
  PRIMARY KEY  (`contentHistoryID`),
  KEY `contentID` (`contentID`),
  KEY `lastModified_effectiveThrough` (`lastModified`,`effectiveThrough`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE  `contentImages` (
  `imageID` int(10) unsigned NOT NULL auto_increment,
  `image` varchar(255) NOT NULL,
  `size` int(10) unsigned NOT NULL,
  `width` int(4) unsigned NOT NULL,
  `height` int(4) unsigned NOT NULL,
  `dateAdded` datetime NOT NULL,
  `lastModified` datetime NOT NULL,
  PRIMARY KEY  (`imageID`),
  UNIQUE KEY `image` (`image`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `countryCodes` (
  `number` INTEGER UNSIGNED NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `A2` CHAR(2) NOT NULL,
  `A3` CHAR(3) NOT NULL,
  PRIMARY KEY (`number`),
  UNIQUE INDEX `name`(`name`),
  UNIQUE INDEX `A2`(`A2`),
  UNIQUE INDEX `A3`(`A3`)
)
ENGINE = InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `countryCodes` (`name`, `A2`, `A3`, `number`) VALUES ('Aaland Islands', 'AX', 'ALA', '248'), ('Afghanistan', 'AF', 'AFG', '004'), ('Albania', 'AL', 'ALB', '008'), ('Algeria', 'DZ', 'DZA', '012'), ('American Samoa', 'AS', 'ASM', '016'), ('Andorra', 'AD', 'AND', '020'), ('Angola', 'AO', 'AGO', '024'), ('Anguilla', 'AI', 'AIA', '660'), ('Antarctica', 'AQ', 'ATA', '010'), ('Antigua and Barbuda', 'AG', 'ATG', '028'), ('Argentina', 'AR', 'ARG', '032'), ('Armenia', 'AM', 'ARM', '051'), ('Aruba', 'AW', 'ABW', '533'), ('Australia', 'AU', 'AUS', '036'), ('Austria', 'AT', 'AUT', '040'), ('Azerbaijan', 'AZ', 'AZE', '031'), ('Bahamas', 'BS', 'BHS', '044'), ('Bahrain', 'BH', 'BHR', '048'), ('Bangladesh', 'BD', 'BGD', '050'), ('Barbados', 'BB', 'BRB', '052'), ('Belarus', 'BY', 'BLR', '112'), ('Belgium', 'BE', 'BEL', '056'), ('Belize', 'BZ', 'BLZ', '084'), ('Benin', 'BJ', 'BEN', '204'), ('Bermuda', 'BM', 'BMU', '060'), ('Bhutan', 'BT', 'BTN', '064'), ('Bolivia', 'BO', 'BOL', '068'), ('Bosnia and Herzegowina', 'BA', 'BIH', '070'), ('Botswana', 'BW', 'BWA', '072'), ('Bouvet Island', 'BV', 'BVT', '074'), ('Brazil', 'BR', 'BRA', '076'), ('British Indian Ocean Territory', 'IO', 'IOT', '086'), ('Brunei Darussalam', 'BN', 'BRN', '096'), ('Bulgaria', 'BG', 'BGR', '100'), ('Burkina Faso', 'BF', 'BFA', '854'), ('Burundi', 'BI', 'BDI', '108'), ('Cambodia', 'KH', 'KHM', '116'), ('Cameroon', 'CM', 'CMR', '120'), ('Canada', 'CA', 'CAN', '124'), ('Cape Verde', 'CV', 'CPV', '132'), ('Cayman Islands', 'KY', 'CYM', '136'), ('Central African Republic', 'CF', 'CAF', '140'), ('Chad', 'TD', 'TCD', '148'), ('Chile', 'CL', 'CHL', '152'), ('China', 'CN', 'CHN', '156'), ('Christmas Island', 'CX', 'CXR', '162'), ('Cocos (Keeling) Islands', 'CC', 'CCK', '166'), ('Colombia', 'CO', 'COL', '170'), ('Comoros', 'KM', 'COM', '174'), ('Congo, Democratic Republic of (was Zaire)', 'CD', 'COD', '180'), ('Congo, Republic of', 'CG', 'COG', '178'), ('Cook Islands', 'CK', 'COK', '184'), ('Costa Rica', 'CR', 'CRI', '188'), ('Cote D\'Ivoire', 'CI', 'CIV', '384'), ('Croatia (Local Name: Hrvatska)', 'HR', 'HRV', '191'), ('Cuba', 'CU', 'CUB', '192'), ('Cyprus', 'CY', 'CYP', '196'), ('Czech Republic', 'CZ', 'CZE', '203'), ('Denmark', 'DK', 'DNK', '208'), ('Djibouti', 'DJ', 'DJI', '262'), ('Dominica', 'DM', 'DMA', '212'), ('Dominican Republic', 'DO', 'DOM', '214'), ('Ecuador', 'EC', 'ECU', '218'), ('Egypt', 'EG', 'EGY', '818'), ('El Salvador', 'SV', 'SLV', '222'), ('Equatorial Guinea', 'GQ', 'GNQ', '226'), ('Eritrea', 'ER', 'ERI', '232'), ('Estonia', 'EE', 'EST', '233'), ('Ethiopia', 'ET', 'ETH', '231'), ('Falkland Islands (Malvinas)', 'FK', 'FLK', '238'), ('Faroe Islands', 'FO', 'FRO', '234'), ('Fiji', 'FJ', 'FJI', '242'), ('Finland', 'FI', 'FIN', '246'), ('France', 'FR', 'FRA', '250'), ('French Guiana', 'GF', 'GUF', '254'), ('French Polynesia', 'PF', 'PYF', '258'), ('French Southern Territories', 'TF', 'ATF', '260'), ('Gabon', 'GA', 'GAB', '266'), ('Gambia', 'GM', 'GMB', '270'), ('Georgia', 'GE', 'GEO', '268'), ('Germany', 'DE', 'DEU', '276'), ('Ghana', 'GH', 'GHA', '288'), ('Gibraltar', 'GI', 'GIB', '292'), ('Greece', 'GR', 'GRC', '300'), ('Greenland', 'GL', 'GRL', '304'), ('Grenada', 'GD', 'GRD', '308'), ('Guadeloupe', 'GP', 'GLP', '312'), ('Guam', 'GU', 'GUM', '316'), ('Guatemala', 'GT', 'GTM', '320'), ('Guinea', 'GN', 'GIN', '324'), ('Guinea-Bissau', 'GW', 'GNB', '624'), ('Guyana', 'GY', 'GUY', '328'), ('Haiti', 'HT', 'HTI', '332'), ('Heard and Mc Donald Islands', 'HM', 'HMD', '334'), ('Honduras', 'HN', 'HND', '340'), ('Hong Kong', 'HK', 'HKG', '344'), ('Hungary', 'HU', 'HUN', '348'), ('Iceland', 'IS', 'ISL', '352'), ('India', 'IN', 'IND', '356'), ('Indonesia', 'ID', 'IDN', '360'), ('Iran (Islamic Republic of)', 'IR', 'IRN', '364'), ('Iraq', 'IQ', 'IRQ', '368'), ('Ireland', 'IE', 'IRL', '372'), ('Israel', 'IL', 'ISR', '376'), ('Italy', 'IT', 'ITA', '380'), ('Jamaica', 'JM', 'JAM', '388'), ('Japan', 'JP', 'JPN', '392'), ('Jordan', 'JO', 'JOR', '400'), ('Kazakhstan', 'KZ', 'KAZ', '398'), ('Kenya', 'KE', 'KEN', '404'), ('Kiribati', 'KI', 'KIR', '296'), ('Korea, Democratic People\'s Republic of', 'KP', 'PRK', '408'), ('Korea, Republic of', 'KR', 'KOR', '410'), ('Kuwait', 'KW', 'KWT', '414'), ('Kyrgyzstan', 'KG', 'KGZ', '417'), ('Lao People\'s Democratic Republic', 'LA', 'LAO', '418'), ('Latvia', 'LV', 'LVA', '428'), ('Lebanon', 'LB', 'LBN', '422'), ('Lesotho', 'LS', 'LSO', '426'), ('Liberia', 'LR', 'LBR', '430'), ('Libyan Arab Jamahiriya', 'LY', 'LBY', '434'), ('Liechtenstein', 'LI', 'LIE', '438'), ('Lithuania', 'LT', 'LTU', '440'), ('Luxembourg', 'LU', 'LUX', '442'), ('Macau', 'MO', 'MAC', '446'), ('Macedonia, The Former Yugoslav Republic of', 'MK', 'MKD', '807'), ('Madagascar', 'MG', 'MDG', '450'), ('Malawi', 'MW', 'MWI', '454'), ('Malaysia', 'MY', 'MYS', '458'), ('Maldives', 'MV', 'MDV', '462'), ('Mali', 'ML', 'MLI', '466'), ('Malta', 'MT', 'MLT', '470'), ('Marshall Islands', 'MH', 'MHL', '584'), ('Martinique', 'MQ', 'MTQ', '474'), ('Mauritania', 'MR', 'MRT', '478'), ('Mauritius', 'MU', 'MUS', '480'), ('Mayotte', 'YT', 'MYT', '175'), ('Mexico', 'MX', 'MEX', '484'), ('Micronesia, Federated States of', 'FM', 'FSM', '583'), ('Moldova, Republic of', 'MD', 'MDA', '498'), ('Monaco', 'MC', 'MCO', '492'), ('Mongolia', 'MN', 'MNG', '496'), ('Montserrat', 'MS', 'MSR', '500'), ('Morocco', 'MA', 'MAR', '504'), ('Mozambique', 'MZ', 'MOZ', '508'), ('Myanmar', 'MM', 'MMR', '104'), ('Namibia', 'NA', 'NAM', '516'), ('Nauru', 'NR', 'NRU', '520'), ('Nepal', 'NP', 'NPL', '524'), ('Netherlands', 'NL', 'NLD', '528'), ('Netherlands Antilles', 'AN', 'ANT', '530'), ('New Caledonia', 'NC', 'NCL', '540'), ('New Zealand', 'NZ', 'NZL', '554'), ('Nicaragua', 'NI', 'NIC', '558'), ('Niger', 'NE', 'NER', '562'), ('Nigeria', 'NG', 'NGA', '566'), ('Niue', 'NU', 'NIU', '570'), ('Norfolk Island', 'NF', 'NFK', '574'), ('Northern Mariana Islands', 'MP', 'MNP', '580'), ('Norway', 'NO', 'NOR', '578'), ('Oman', 'OM', 'OMN', '512'), ('Pakistan', 'PK', 'PAK', '586'), ('Palau', 'PW', 'PLW', '585'), ('Palestinian Territory, Occupied', 'PS', 'PSE', '275'), ('Panama', 'PA', 'PAN', '591'), ('Papua New Guinea', 'PG', 'PNG', '598'), ('Paraguay', 'PY', 'PRY', '600'), ('Peru', 'PE', 'PER', '604'), ('Philippines', 'PH', 'PHL', '608'), ('Pitcairn', 'PN', 'PCN', '612'), ('Poland', 'PL', 'POL', '616'), ('Portugal', 'PT', 'PRT', '620'), ('Puerto Rico', 'PR', 'PRI', '630'), ('Qatar', 'QA', 'QAT', '634'), ('Reunion', 'RE', 'REU', '638'), ('Romania', 'RO', 'ROU', '642'), ('Russian Federation', 'RU', 'RUS', '643'), ('Rwanda', 'RW', 'RWA', '646'), ('Saint Helena', 'SH', 'SHN', '654'), ('Saint Kitts and Nevis', 'KN', 'KNA', '659'), ('Saint Lucia', 'LC', 'LCA', '662'), ('Saint Pierre and Miquelon', 'PM', 'SPM', '666'), ('Saint Vincent and The Grenadines', 'VC', 'VCT', '670'), ('Samoa', 'WS', 'WSM', '882'), ('San Marino', 'SM', 'SMR', '674'), ('Sao Tome and Principe', 'ST', 'STP', '678'), ('Saudi Arabia', 'SA', 'SAU', '682'), ('Senegal', 'SN', 'SEN', '686'), ('Serbia and Montenegro', 'CS', 'SCG', '891'), ('Seychelles', 'SC', 'SYC', '690'), ('Sierra Leone', 'SL', 'SLE', '694'), ('Singapore', 'SG', 'SGP', '702'), ('Slovakia', 'SK', 'SVK', '703'), ('Slovenia', 'SI', 'SVN', '705'), ('Solomon Islands', 'SB', 'SLB', '090'), ('Somalia', 'SO', 'SOM', '706'), ('South Africa', 'ZA', 'ZAF', '710'), ('South Georgia and The South Sandwich Islands', 'GS', 'SGS', '239'), ('Spain', 'ES', 'ESP', '724'), ('Sri Lanka', 'LK', 'LKA', '144'), ('Sudan', 'SD', 'SDN', '736'), ('Suriname', 'SR', 'SUR', '740'), ('Svalbard and Jan Mayen Islands', 'SJ', 'SJM', '744'), ('Swaziland', 'SZ', 'SWZ', '748'), ('Sweden', 'SE', 'SWE', '752'), ('Switzerland', 'CH', 'CHE', '756'), ('Syrian Arab Republic', 'SY', 'SYR', '760'), ('Taiwan', 'TW', 'TWN', '158'), ('Tajikistan', 'TJ', 'TJK', '762'), ('Tanzania, United Republic of', 'TZ', 'TZA', '834'), ('Thailand', 'TH', 'THA', '764'), ('Timor-Leste', 'TL', 'TLS', '626'), ('Togo', 'TG', 'TGO', '768'), ('Tokelau', 'TK', 'TKL', '772'), ('Tonga', 'TO', 'TON', '776'), ('Trinidad and Tobago', 'TT', 'TTO', '780'), ('Tunisia', 'TN', 'TUN', '788'), ('Turkey', 'TR', 'TUR', '792'), ('Turkmenistan', 'TM', 'TKM', '795'), ('Turks and Caicos Islands', 'TC', 'TCA', '796'), ('Tuvalu', 'TV', 'TUV', '798'), ('Uganda', 'UG', 'UGA', '800'), ('Ukraine', 'UA', 'UKR', '804'), ('United Arab Emirates', 'AE', 'ARE', '784'), ('United Kingdom', 'GB', 'GBR', '826'), ('United States', 'US', 'USA', '840'), ('United States Minor Outlying Islands', 'UM', 'UMI', '581'), ('Uruguay', 'UY', 'URY', '858'), ('Uzbekistan', 'UZ', 'UZB', '860'), ('Vanuatu', 'VU', 'VUT', '548'), ('Vatican City State (Holy See)', 'VA', 'VAT', '336'), ('Venezuela', 'VE', 'VEN', '862'), ('Viet Nam', 'VN', 'VNM', '704'), ('Virgin Islands (British)', 'VG', 'VGB', '092'), ('Virgin Islands (U.S.)', 'VI', 'VIR', '850'), ('Wallis and Futuna Islands', 'WF', 'WLF', '876'), ('Western Sahara', 'EH', 'ESH', '732'), ('Yemen', 'YE', 'YEM', '887'), ('Zambia', 'ZM', 'ZMB', '894'), ('Zimbabwe', 'ZW', 'ZWE', '716');

CREATE TABLE  `emails` (
  `emailID` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '',
  `subject` varchar(255) NOT NULL default '',
  `html` text NOT NULL,
  `text` text NOT NULL,
  `fromEmail` varchar(255) NOT NULL,
  `headerID` INTEGER UNSIGNED NOT NULL,
  `footerID` INTEGER UNSIGNED NOT NULL,
  `dateAdded` datetime NOT NULL,
  `lastModified` datetime NOT NULL,
  PRIMARY KEY  (`emailID`),
  UNIQUE KEY `name` USING BTREE (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE  `emailsHistory` (
  `emailsHistoryID` int(10) unsigned NOT NULL auto_increment,
  `recordEditor` varchar(10) NOT NULL,
  `recordEditorID` int(10) unsigned NOT NULL,
  `action` varchar(10) NOT NULL,
  `comments` text NOT NULL,
  `emailID` int(11) unsigned NOT NULL,
  `name` varchar(100) NOT NULL default '',
  `subject` varchar(255) NOT NULL default '',
  `html` text NOT NULL,
  `text` text NOT NULL,
  `fromEmail` varchar(255) NOT NULL,
  `headerID` INTEGER UNSIGNED NOT NULL,
  `footerID` INTEGER UNSIGNED NOT NULL,
  `dateAdded` datetime NOT NULL,
  `lastModified` datetime NOT NULL,
  `effectiveThrough` datetime NOT NULL,
  PRIMARY KEY  (`emailsHistoryID`),
  KEY `emailID` (`emailID`),
  KEY `lastModified_effectiveThrough` (`lastModified`,`effectiveThrough`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `emails` (`name`, `subject`, `html`, `text`, `fromEmail`, `headerID`, `footerID`, `dateAdded`, `lastModified`) VALUES ('userActivation', 'User account activation for {$user.email}', 'http://site.com/user/activation/action/activate/activationCode/{$activationCode}', 'http://site.com/user/activation/action/activate/activationCode/{$activationCode}', 'support@site.com', 0, 0, NOW(), NOW()), ('forgotPassword', 'Password reset for {$user.email}', '{$password}', '{$password}', 'support@site.com', 0, 0, NOW(), NOW());

INSERT INTO `emailsHistory` (`recordEditor`, `recordEditorID`, `action`, `comments`, `emailID`, `name`, `subject`, `html`, `text`, `fromEmail`, `headerID`, `footerID`, `dateAdded`, `lastModified`, `effectiveThrough`) SELECT 'SYSTEM', 0, 'SAVE', 'New record', `emailID`, `name`, `subject`, `html`, `text`, `fromEmail`, `headerID`, `footerID`, `dateAdded`, `lastModified`, '9999-12-31 23:59:59' FROM `emails`;

CREATE TABLE `emailSections` (
  `emailSectionID` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `type` ENUM('header', 'footer') NOT NULL DEFAULT 'header',
  `name` VARCHAR(100) NOT NULL,
  `html` TEXT NOT NULL,
  `text` TEXT NOT NULL,
  `dateAdded` DATETIME NOT NULL,
  `lastModified` DATETIME NOT NULL,
  PRIMARY KEY (`emailSectionID`),
  UNIQUE INDEX `type_name`(`type`, `name`),
  INDEX `name`(`name`)
)
ENGINE = InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `emailSectionsHistory` (
  `emailSectionsHistoryID` int(11) unsigned NOT NULL auto_increment,
  `recordEditor` varchar(10) NOT NULL,
  `recordEditorID` int(10) unsigned NOT NULL,
  `action` varchar(10) NOT NULL,
  `comments` text NOT NULL,
  `emailSectionID` INTEGER UNSIGNED NOT NULL,
  `type` ENUM('header', 'footer') NOT NULL DEFAULT 'header',
  `name` VARCHAR(100) NOT NULL,
  `html` TEXT NOT NULL,
  `text` TEXT NOT NULL,
  `dateAdded` DATETIME NOT NULL,
  `lastModified` DATETIME NOT NULL,
  `effectiveThrough` datetime NOT NULL,
  PRIMARY KEY (`emailSectionsHistoryID`),
  INDEX `emailSectionID`(`emailSectionID`),
  KEY `lastModified_effectiveThrough` (`lastModified`,`effectiveThrough`)
)
ENGINE = InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE  `errors` (
  `errorID` int(11) unsigned NOT NULL auto_increment,
  `class` enum('exception','error') NOT NULL default 'error',
  `code` int(10) unsigned NOT NULL default '0',
  `type` enum('Unknown','Error','Warning','Parsing Error','Notice','Core Error','Core Warning','Compile Error','Compile Warning','User Error','User Warning','User Notice','Runtime Notice','Catchable Fatal Error','Fatal') NOT NULL default 'Unknown',
  `file` varchar(255) NOT NULL default '',
  `line` int(10) unsigned NOT NULL default '0',
  `function` varchar(255) NOT NULL default '',
  `message` text NOT NULL,
  `date` date NOT NULL default '0000-00-00',
  `count` int(10) unsigned NOT NULL default '1',
  `status` enum('uncaught','new','review','resolved','overflow','uncaughtoverflow') NOT NULL default 'new',
  `trace` text NOT NULL,
  `comments` text character set utf8,
  `lastModified` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`errorID`),
  UNIQUE KEY `class_code_file_line_function_message_date` USING BTREE (`class`,`code`,`file`,`line`,`function`,`message`(100),`date`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `feeds` (
  `feedID` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `postType` ENUM('video', 'link', 'wiki', 'blog') NOT NULL,
  `url` VARCHAR(255) NOT NULL,
  `source` VARCHAR(45),
  `gameTitleID` INTEGER UNSIGNED,
  `parameters` VARCHAR(255),
  `entryPath` VARCHAR(255),
  `gameTitlePath` VARCHAR(255),
  `identifierPath` VARCHAR(255),
  `postTitlePath` VARCHAR(255),
  `urlPath` VARCHAR(255),
  `imagePath` VARCHAR(255),
  `descriptionPath` VARCHAR(255),
  `contentPath` VARCHAR(255),
  `require` VARCHAR(255),
  `reject` VARCHAR(255),
  `replace` VARCHAR(255),
  `status` ENUM('active','inactive') NOT NULL,
  `interval` ENUM('daily','weekly','bi weekly','monthly','bi monthly','6 months') NOT NULL,
  `priority` TINYINT(3) UNSIGNED NOT NULL,
  `poster` varchar(10) NOT NULL,
  `posterID` int(10) unsigned NOT NULL,
  `dateAdded` DATETIME NOT NULL,
  `lastModified` DATETIME NOT NULL,
  PRIMARY KEY (`feedID`),
  INDEX `status_interval_priority`(`status`, `interval`, `priority`)
)
ENGINE = InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `feedsHistory` (
  `feedsHistoryID` int(10) unsigned NOT NULL auto_increment,
  `recordEditor` varchar(10) NOT NULL,
  `recordEditorID` int(10) unsigned NOT NULL,
  `action` varchar(10) NOT NULL,
  `comments` text NOT NULL,
  `feedID` INTEGER UNSIGNED NOT NULL,
  `postType` ENUM('video', 'link', 'wiki', 'blog') NOT NULL,
  `url` VARCHAR(255) NOT NULL,
  `source` VARCHAR(45),
  `gameTitleID` INTEGER UNSIGNED,
  `parameters` VARCHAR(255),
  `entryPath` VARCHAR(255),
  `gameTitlePath` VARCHAR(255),
  `identifierPath` VARCHAR(255),
  `postTitlePath` VARCHAR(255),
  `urlPath` VARCHAR(255),
  `imagePath` VARCHAR(255),
  `descriptionPath` VARCHAR(255),
  `contentPath` VARCHAR(255),
  `require` VARCHAR(255),
  `reject` VARCHAR(255),
  `replace` VARCHAR(255),
  `status` ENUM('active','inactive') NOT NULL,
  `interval` ENUM('daily','weekly','bi weekly','monthly','bi monthly','6 months') NOT NULL,
  `priority` TINYINT(3) UNSIGNED NOT NULL,
  `poster` varchar(10) NOT NULL,
  `posterID` int(10) unsigned NOT NULL,
  `dateAdded` DATETIME NOT NULL,
  `lastModified` DATETIME NOT NULL,
  `effectiveThrough` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY (`feedsHistoryID`),
  KEY (`feedID`),
  KEY `lastModified_effectiveThrough` (`lastModified`,`effectiveThrough`)
)
ENGINE = InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `gameTitles` (
  `gameTitleID` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `gameTitle` VARCHAR(255) NOT NULL,
  `gameTitleURL` VARCHAR(255) NOT NULL,
  `gameTitleKey` VARCHAR(255) NOT NULL,
  `dateAdded` DATETIME NOT NULL,
  PRIMARY KEY (`gameTitleID`),
  UNIQUE INDEX `gameTitleURL`(`gameTitleURL`),
  UNIQUE INDEX `gameTitleKey`(`gameTitleKey`)
)
ENGINE = InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `gameTitleStatistics` (
  `gameTitleID` INTEGER UNSIGNED NOT NULL,
  `posts` INTEGER UNSIGNED NOT NULL,
  `postViews` BIGINT UNSIGNED NOT NULL,
  `weight` BIGINT UNSIGNED NOT NULL,
  `status` ENUM('current', 'new') NOT NULL DEFAULT 'current',
  INDEX `gameTitleID`(`gameTitleID`),
  INDEX `posts`(`posts`),
  INDEX `postViews`(`postViews`),
  INDEX `weight`(`weight`),
  INDEX `status_weight`(`status`, `weight`)
)
ENGINE = InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE  `postComments0` (
  `postCommentID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `postID` int(10) unsigned NOT NULL,
  `parentCommentID` int(10) unsigned NOT NULL,
  `comment` text NOT NULL,
  `upVotes` INTEGER NOT NULL DEFAULT 0,
  `downVotes` INTEGER NOT NULL DEFAULT 0,
  `totalVotes` INTEGER NOT NULL DEFAULT 0,
  `poster` varchar(10) NOT NULL,
  `posterID` int(10) unsigned NOT NULL,
  `posted` datetime NOT NULL,
  `lastModified` DATETIME NOT NULL,
  PRIMARY KEY (`postCommentID`),
  KEY `postID_parentCommentID` (`postID`,`parentCommentID`),
  KEY `posterID_poster_posted`(`posterID`, `poster`, `posted`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE  `postComments1` (
  `postCommentID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `postID` int(10) unsigned NOT NULL,
  `parentCommentID` int(10) unsigned NOT NULL,
  `comment` text NOT NULL,
  `upVotes` INTEGER NOT NULL DEFAULT 0,
  `downVotes` INTEGER NOT NULL DEFAULT 0,
  `totalVotes` INTEGER NOT NULL DEFAULT 0,
  `poster` varchar(10) NOT NULL,
  `posterID` int(10) unsigned NOT NULL,
  `posted` datetime NOT NULL,
  `lastModified` DATETIME NOT NULL,
  PRIMARY KEY (`postCommentID`),
  KEY `postID_parentCommentID` (`postID`,`parentCommentID`),
  KEY `posterID_poster_posted`(`posterID`, `poster`, `posted`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE  `postComments2` (
  `postCommentID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `postID` int(10) unsigned NOT NULL,
  `parentCommentID` int(10) unsigned NOT NULL,
  `comment` text NOT NULL,
  `upVotes` INTEGER NOT NULL DEFAULT 0,
  `downVotes` INTEGER NOT NULL DEFAULT 0,
  `totalVotes` INTEGER NOT NULL DEFAULT 0,
  `poster` varchar(10) NOT NULL,
  `posterID` int(10) unsigned NOT NULL,
  `posted` datetime NOT NULL,
  `lastModified` DATETIME NOT NULL,
  PRIMARY KEY (`postCommentID`),
  KEY `postID_parentCommentID` (`postID`,`parentCommentID`),
  KEY `posterID_poster_posted`(`posterID`, `poster`, `posted`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE  `postComments3` (
  `postCommentID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `postID` int(10) unsigned NOT NULL,
  `parentCommentID` int(10) unsigned NOT NULL,
  `comment` text NOT NULL,
  `upVotes` INTEGER NOT NULL DEFAULT 0,
  `downVotes` INTEGER NOT NULL DEFAULT 0,
  `totalVotes` INTEGER NOT NULL DEFAULT 0,
  `poster` varchar(10) NOT NULL,
  `posterID` int(10) unsigned NOT NULL,
  `posted` datetime NOT NULL,
  `lastModified` DATETIME NOT NULL,
  PRIMARY KEY (`postCommentID`),
  KEY `postID_parentCommentID` (`postID`,`parentCommentID`),
  KEY `posterID_poster_posted`(`posterID`, `poster`, `posted`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE  `postComments4` (
  `postCommentID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `postID` int(10) unsigned NOT NULL,
  `parentCommentID` int(10) unsigned NOT NULL,
  `comment` text NOT NULL,
  `upVotes` INTEGER NOT NULL DEFAULT 0,
  `downVotes` INTEGER NOT NULL DEFAULT 0,
  `totalVotes` INTEGER NOT NULL DEFAULT 0,
  `poster` varchar(10) NOT NULL,
  `posterID` int(10) unsigned NOT NULL,
  `posted` datetime NOT NULL,
  `lastModified` DATETIME NOT NULL,
  PRIMARY KEY (`postCommentID`),
  KEY `postID_parentCommentID` (`postID`,`parentCommentID`),
  KEY `posterID_poster_posted`(`posterID`, `poster`, `posted`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE  `postComments5` (
  `postCommentID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `postID` int(10) unsigned NOT NULL,
  `parentCommentID` int(10) unsigned NOT NULL,
  `comment` text NOT NULL,
  `upVotes` INTEGER NOT NULL DEFAULT 0,
  `downVotes` INTEGER NOT NULL DEFAULT 0,
  `totalVotes` INTEGER NOT NULL DEFAULT 0,
  `poster` varchar(10) NOT NULL,
  `posterID` int(10) unsigned NOT NULL,
  `posted` datetime NOT NULL,
  `lastModified` DATETIME NOT NULL,
  PRIMARY KEY (`postCommentID`),
  KEY `postID_parentCommentID` (`postID`,`parentCommentID`),
  KEY `posterID_poster_posted`(`posterID`, `poster`, `posted`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE  `postComments6` (
  `postCommentID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `postID` int(10) unsigned NOT NULL,
  `parentCommentID` int(10) unsigned NOT NULL,
  `comment` text NOT NULL,
  `upVotes` INTEGER NOT NULL DEFAULT 0,
  `downVotes` INTEGER NOT NULL DEFAULT 0,
  `totalVotes` INTEGER NOT NULL DEFAULT 0,
  `poster` varchar(10) NOT NULL,
  `posterID` int(10) unsigned NOT NULL,
  `posted` datetime NOT NULL,
  `lastModified` DATETIME NOT NULL,
  PRIMARY KEY (`postCommentID`),
  KEY `postID_parentCommentID` (`postID`,`parentCommentID`),
  KEY `posterID_poster_posted`(`posterID`, `poster`, `posted`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE  `postComments7` (
  `postCommentID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `postID` int(10) unsigned NOT NULL,
  `parentCommentID` int(10) unsigned NOT NULL,
  `comment` text NOT NULL,
  `upVotes` INTEGER NOT NULL DEFAULT 0,
  `downVotes` INTEGER NOT NULL DEFAULT 0,
  `totalVotes` INTEGER NOT NULL DEFAULT 0,
  `poster` varchar(10) NOT NULL,
  `posterID` int(10) unsigned NOT NULL,
  `posted` datetime NOT NULL,
  `lastModified` DATETIME NOT NULL,
  PRIMARY KEY (`postCommentID`),
  KEY `postID_parentCommentID` (`postID`,`parentCommentID`),
  KEY `posterID_poster_posted`(`posterID`, `poster`, `posted`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE  `postComments8` (
  `postCommentID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `postID` int(10) unsigned NOT NULL,
  `parentCommentID` int(10) unsigned NOT NULL,
  `comment` text NOT NULL,
  `upVotes` INTEGER NOT NULL DEFAULT 0,
  `downVotes` INTEGER NOT NULL DEFAULT 0,
  `totalVotes` INTEGER NOT NULL DEFAULT 0,
  `poster` varchar(10) NOT NULL,
  `posterID` int(10) unsigned NOT NULL,
  `posted` datetime NOT NULL,
  `lastModified` DATETIME NOT NULL,
  PRIMARY KEY (`postCommentID`),
  KEY `postID_parentCommentID` (`postID`,`parentCommentID`),
  KEY `posterID_poster_posted`(`posterID`, `poster`, `posted`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE  `postComments9` (
  `postCommentID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `postID` int(10) unsigned NOT NULL,
  `parentCommentID` int(10) unsigned NOT NULL,
  `comment` text NOT NULL,
  `upVotes` INTEGER NOT NULL DEFAULT 0,
  `downVotes` INTEGER NOT NULL DEFAULT 0,
  `totalVotes` INTEGER NOT NULL DEFAULT 0,
  `poster` varchar(10) NOT NULL,
  `posterID` int(10) unsigned NOT NULL,
  `posted` datetime NOT NULL,
  `lastModified` DATETIME NOT NULL,
  PRIMARY KEY (`postCommentID`),
  KEY `postID_parentCommentID` (`postID`,`parentCommentID`),
  KEY `posterID_poster_posted`(`posterID`, `poster`, `posted`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE  `postCommentsCache` (
  `postID` int(10) unsigned NOT NULL,
  `data` MEDIUMTEXT,
  `expired` tinyint(1) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`postID`),
  KEY `expired`(`expired`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE  `postCommentsHistory0` (
  `postCommentHistoryID` int(10) unsigned NOT NULL auto_increment,
  `recordEditor` varchar(10) NOT NULL,
  `recordEditorID` int(10) unsigned NOT NULL,
  `action` varchar(10) NOT NULL,
  `comments` text NOT NULL,
  `postCommentID` int(10) unsigned NOT NULL,
  `postID` int(10) unsigned NOT NULL,
  `parentCommentID` int(10) unsigned NOT NULL,
  `comment` text NOT NULL,
  `poster` varchar(10) NOT NULL,
  `posterID` int(10) unsigned NOT NULL,
  `posted` datetime NOT NULL,
  `lastModified` DATETIME NOT NULL,
  `effectiveThrough` datetime NOT NULL,
  PRIMARY KEY (`postCommentHistoryID`),
  KEY (`postCommentID`),
  KEY `lastModified_effectiveThrough` (`lastModified`,`effectiveThrough`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE  `postCommentsHistory1` (
  `postCommentHistoryID` int(10) unsigned NOT NULL auto_increment,
  `recordEditor` varchar(10) NOT NULL,
  `recordEditorID` int(10) unsigned NOT NULL,
  `action` varchar(10) NOT NULL,
  `comments` text NOT NULL,
  `postCommentID` int(10) unsigned NOT NULL,
  `postID` int(10) unsigned NOT NULL,
  `parentCommentID` int(10) unsigned NOT NULL,
  `comment` text NOT NULL,
  `poster` varchar(10) NOT NULL,
  `posterID` int(10) unsigned NOT NULL,
  `posted` datetime NOT NULL,
  `lastModified` DATETIME NOT NULL,
  `effectiveThrough` datetime NOT NULL,
  PRIMARY KEY (`postCommentHistoryID`),
  KEY (`postCommentID`),
  KEY `lastModified_effectiveThrough` (`lastModified`,`effectiveThrough`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE  `postCommentsHistory2` (
  `postCommentHistoryID` int(10) unsigned NOT NULL auto_increment,
  `recordEditor` varchar(10) NOT NULL,
  `recordEditorID` int(10) unsigned NOT NULL,
  `action` varchar(10) NOT NULL,
  `comments` text NOT NULL,
  `postCommentID` int(10) unsigned NOT NULL,
  `postID` int(10) unsigned NOT NULL,
  `parentCommentID` int(10) unsigned NOT NULL,
  `comment` text NOT NULL,
  `poster` varchar(10) NOT NULL,
  `posterID` int(10) unsigned NOT NULL,
  `posted` datetime NOT NULL,
  `lastModified` DATETIME NOT NULL,
  `effectiveThrough` datetime NOT NULL,
  PRIMARY KEY (`postCommentHistoryID`),
  KEY (`postCommentID`),
  KEY `lastModified_effectiveThrough` (`lastModified`,`effectiveThrough`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE  `postCommentsHistory3` (
  `postCommentHistoryID` int(10) unsigned NOT NULL auto_increment,
  `recordEditor` varchar(10) NOT NULL,
  `recordEditorID` int(10) unsigned NOT NULL,
  `action` varchar(10) NOT NULL,
  `comments` text NOT NULL,
  `postCommentID` int(10) unsigned NOT NULL,
  `postID` int(10) unsigned NOT NULL,
  `parentCommentID` int(10) unsigned NOT NULL,
  `comment` text NOT NULL,
  `poster` varchar(10) NOT NULL,
  `posterID` int(10) unsigned NOT NULL,
  `posted` datetime NOT NULL,
  `lastModified` DATETIME NOT NULL,
  `effectiveThrough` datetime NOT NULL,
  PRIMARY KEY (`postCommentHistoryID`),
  KEY (`postCommentID`),
  KEY `lastModified_effectiveThrough` (`lastModified`,`effectiveThrough`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE  `postCommentsHistory4` (
  `postCommentHistoryID` int(10) unsigned NOT NULL auto_increment,
  `recordEditor` varchar(10) NOT NULL,
  `recordEditorID` int(10) unsigned NOT NULL,
  `action` varchar(10) NOT NULL,
  `comments` text NOT NULL,
  `postCommentID` int(10) unsigned NOT NULL,
  `postID` int(10) unsigned NOT NULL,
  `parentCommentID` int(10) unsigned NOT NULL,
  `comment` text NOT NULL,
  `poster` varchar(10) NOT NULL,
  `posterID` int(10) unsigned NOT NULL,
  `posted` datetime NOT NULL,
  `lastModified` DATETIME NOT NULL,
  `effectiveThrough` datetime NOT NULL,
  PRIMARY KEY (`postCommentHistoryID`),
  KEY (`postCommentID`),
  KEY `lastModified_effectiveThrough` (`lastModified`,`effectiveThrough`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE  `postCommentsHistory5` (
  `postCommentHistoryID` int(10) unsigned NOT NULL auto_increment,
  `recordEditor` varchar(10) NOT NULL,
  `recordEditorID` int(10) unsigned NOT NULL,
  `action` varchar(10) NOT NULL,
  `comments` text NOT NULL,
  `postCommentID` int(10) unsigned NOT NULL,
  `postID` int(10) unsigned NOT NULL,
  `parentCommentID` int(10) unsigned NOT NULL,
  `comment` text NOT NULL,
  `poster` varchar(10) NOT NULL,
  `posterID` int(10) unsigned NOT NULL,
  `posted` datetime NOT NULL,
  `lastModified` DATETIME NOT NULL,
  `effectiveThrough` datetime NOT NULL,
  PRIMARY KEY (`postCommentHistoryID`),
  KEY (`postCommentID`),
  KEY `lastModified_effectiveThrough` (`lastModified`,`effectiveThrough`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE  `postCommentsHistory6` (
  `postCommentHistoryID` int(10) unsigned NOT NULL auto_increment,
  `recordEditor` varchar(10) NOT NULL,
  `recordEditorID` int(10) unsigned NOT NULL,
  `action` varchar(10) NOT NULL,
  `comments` text NOT NULL,
  `postCommentID` int(10) unsigned NOT NULL,
  `postID` int(10) unsigned NOT NULL,
  `parentCommentID` int(10) unsigned NOT NULL,
  `comment` text NOT NULL,
  `poster` varchar(10) NOT NULL,
  `posterID` int(10) unsigned NOT NULL,
  `posted` datetime NOT NULL,
  `lastModified` DATETIME NOT NULL,
  `effectiveThrough` datetime NOT NULL,
  PRIMARY KEY (`postCommentHistoryID`),
  KEY (`postCommentID`),
  KEY `lastModified_effectiveThrough` (`lastModified`,`effectiveThrough`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE  `postCommentsHistory7` (
  `postCommentHistoryID` int(10) unsigned NOT NULL auto_increment,
  `recordEditor` varchar(10) NOT NULL,
  `recordEditorID` int(10) unsigned NOT NULL,
  `action` varchar(10) NOT NULL,
  `comments` text NOT NULL,
  `postCommentID` int(10) unsigned NOT NULL,
  `postID` int(10) unsigned NOT NULL,
  `parentCommentID` int(10) unsigned NOT NULL,
  `comment` text NOT NULL,
  `poster` varchar(10) NOT NULL,
  `posterID` int(10) unsigned NOT NULL,
  `posted` datetime NOT NULL,
  `lastModified` DATETIME NOT NULL,
  `effectiveThrough` datetime NOT NULL,
  PRIMARY KEY (`postCommentHistoryID`),
  KEY (`postCommentID`),
  KEY `lastModified_effectiveThrough` (`lastModified`,`effectiveThrough`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE  `postCommentsHistory8` (
  `postCommentHistoryID` int(10) unsigned NOT NULL auto_increment,
  `recordEditor` varchar(10) NOT NULL,
  `recordEditorID` int(10) unsigned NOT NULL,
  `action` varchar(10) NOT NULL,
  `comments` text NOT NULL,
  `postCommentID` int(10) unsigned NOT NULL,
  `postID` int(10) unsigned NOT NULL,
  `parentCommentID` int(10) unsigned NOT NULL,
  `comment` text NOT NULL,
  `poster` varchar(10) NOT NULL,
  `posterID` int(10) unsigned NOT NULL,
  `posted` datetime NOT NULL,
  `lastModified` DATETIME NOT NULL,
  `effectiveThrough` datetime NOT NULL,
  PRIMARY KEY (`postCommentHistoryID`),
  KEY (`postCommentID`),
  KEY `lastModified_effectiveThrough` (`lastModified`,`effectiveThrough`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE  `postCommentsHistory9` (
  `postCommentHistoryID` int(10) unsigned NOT NULL auto_increment,
  `recordEditor` varchar(10) NOT NULL,
  `recordEditorID` int(10) unsigned NOT NULL,
  `action` varchar(10) NOT NULL,
  `comments` text NOT NULL,
  `postCommentID` int(10) unsigned NOT NULL,
  `postID` int(10) unsigned NOT NULL,
  `parentCommentID` int(10) unsigned NOT NULL,
  `comment` text NOT NULL,
  `poster` varchar(10) NOT NULL,
  `posterID` int(10) unsigned NOT NULL,
  `posted` datetime NOT NULL,
  `lastModified` DATETIME NOT NULL,
  `effectiveThrough` datetime NOT NULL,
  PRIMARY KEY (`postCommentHistoryID`),
  KEY (`postCommentID`),
  KEY `lastModified_effectiveThrough` (`lastModified`,`effectiveThrough`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `postContentRevisions` (
  `postContentRevisionID` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `postID` INTEGER UNSIGNED NOT NULL,
  `content` TEXT NOT NULL,
  `poster` varchar(10) NOT NULL,
  `posterID` int(10) unsigned NOT NULL,
  `posted` DATETIME NOT NULL,
  PRIMARY KEY (`postContentRevisionID`),
  KEY `postID_posted`(`postID`, `posted`),
  KEY `posterID_poster_posted`(`posterID`, `poster`, `posted`)
)
ENGINE = InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `postIndexDelta` (
  `postID` INTEGER UNSIGNED NOT NULL,
  `status` ENUM('updated', 'indexed') NOT NULL DEFAULT 'updated',
  PRIMARY KEY (`postID`),
  INDEX status(`status`)
)
ENGINE = InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `posts` (
  `postID` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `type` ENUM('video', 'link', 'wiki', 'blog') NOT NULL,
  `source` VARCHAR(45) DEFAULT NULL,
  `identifier` VARCHAR(45) DEFAULT NULL,
  `gameTitleID` INTEGER NOT NULL,
  `postTitle` VARCHAR(255) NOT NULL,
  `postTitleURL` VARCHAR(255) NOT NULL,
  `url` TEXT DEFAULT NULL,
  `image` TEXT DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `content` TEXT DEFAULT NULL,
  `status` ENUM('active', 'disabled') NOT NULL,
  `poster` varchar(10) NOT NULL,
  `posterID` int(10) unsigned NOT NULL,
  `posted` DATETIME NOT NULL,
  `lastModified` DATETIME NOT NULL,
  PRIMARY KEY (`postID`),
  UNIQUE INDEX `gameTitleID_postTitleURL`(`gameTitleID`, `postTitleURL`),
  KEY `type_source_identifier`(`type`, `source`, `identifier`),
  KEY `source`(`source`),
  KEY `postTitleURL`(`postTitleURL`),
  KEY `url`(`url` (255)),
  KEY `status`(`status`),
  KEY `poster`(`poster`),
  KEY `posted`(`posted`),
  KEY `posterID_poster_posted`(`posterID`, `poster`, `posted`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `postsHistory` (
  `postsHistoryID` int(10) unsigned NOT NULL auto_increment,
  `recordEditor` varchar(10) NOT NULL,
  `recordEditorID` int(10) unsigned NOT NULL,
  `action` varchar(10) NOT NULL,
  `comments` text NOT NULL,
  `postID` INTEGER UNSIGNED NOT NULL,
  `type` ENUM('video', 'link', 'wiki', 'blog') NOT NULL,
  `source` VARCHAR(45) DEFAULT NULL,
  `identifier` VARCHAR(45) DEFAULT NULL,
  `gameTitleID` INTEGER NOT NULL,
  `postTitle` VARCHAR(255) NOT NULL,
  `postTitleURL` VARCHAR(255) NOT NULL,
  `url` TEXT DEFAULT NULL,
  `image` TEXT DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `content` TEXT DEFAULT NULL,
  `status` ENUM('active', 'disabled') NOT NULL,
  `poster` varchar(10) NOT NULL,
  `posterID` int(10) unsigned NOT NULL,
  `posted` DATETIME NOT NULL,
  `lastModified` DATETIME NOT NULL,
  `effectiveThrough` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY (`postsHistoryID`),
  KEY `postID`(`postID`),
  KEY `lastModified_effectiveThrough` (`lastModified`,`effectiveThrough`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `postStatistics` (
  `postID` INTEGER UNSIGNED NOT NULL,
  `views` INTEGER UNSIGNED NOT NULL DEFAULT 0,
  `comments` INTEGER UNSIGNED NOT NULL DEFAULT 0,
  `upVotes` INTEGER NOT NULL DEFAULT 0,
  `downVotes` INTEGER NOT NULL DEFAULT 0,
 PRIMARY KEY (`postID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `postViews` (
  `postID` INTEGER UNSIGNED NOT NULL,
  `date` DATE NOT NULL,
  `views` INTEGER UNSIGNED NOT NULL,
  PRIMARY KEY (`postID`, `date`),
  KEY `date_postID`(`date`, `postID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE  `repairOrderComments` (
  `repairOrderCommentID` int(10) unsigned NOT NULL auto_increment,
  `repairOrderID` int(10) unsigned NOT NULL,
  `comment` text NOT NULL,
  `userID` int(10) unsigned NOT NULL,
  `dateAdded` datetime NOT NULL,
  PRIMARY KEY  (`repairOrderCommentID`),
  KEY `repairOrderID_dateAdded` (`repairOrderID`, `dateAdded`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE  `repairOrders` (
  `repairOrderID` int(10) unsigned NOT NULL auto_increment,
  `console` int(10) unsigned NOT NULL,
  `serial` varchar(255) NOT NULL,
  `systemProblemID` int(10) unsigned NOT NULL,
  `description` text NOT NULL,
  `status` enum('new','received','3 days in','7 days in','repaired','irreparable','invoice sent','paid','shipped','completed','cancelled') NOT NULL,
  `receiveMethod` enum('drop off', 'ship') NOT NULL,
  `returnMethod` enum('pick up', 'ship regular', 'ship express') NOT NULL,
  `user` varchar(10) NOT NULL,
  `userID` int(10) unsigned NOT NULL,
  `first` varchar(50) NOT NULL,
  `last` varchar(50) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address1` varchar(255),
  `address2` varchar(255),
  `city` varchar(50),
  `state` varchar(50),
  `postal` varchar(10),
  `country` char(3),
  `contact` ENUM('phone','email','none') NOT NULL DEFAULT 'none',
  `cost` DOUBLE(6,2) NOT NULL DEFAULT 0,
  `orderDate` datetime NOT NULL,
  `lastModified` datetime NOT NULL,
  PRIMARY KEY  (`repairOrderID`),
  KEY `console` (`console`),
  KEY `serial` (`serial`),
  KEY `status` (`status`),
  KEY `orderDate_status` (`orderDate`,`status`),
  KEY `user`(`user`),
  KEY `userID_user_orderDate`(`userID`, `user`, `orderDate`),
  KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE  `repairOrdersHistory` (
  `repairOrderHistoryID` int(10) unsigned NOT NULL auto_increment,
  `recordEditor` varchar(10) NOT NULL,
  `recordEditorID` int(10) unsigned NOT NULL,
  `action` varchar(10) NOT NULL,
  `comments` text NOT NULL,
  `repairOrderID` int(10) unsigned NOT NULL,
  `console` int(10) unsigned NOT NULL,
  `serial` varchar(255) NOT NULL,
  `systemProblemID` int(10) unsigned NOT NULL,
  `description` text NOT NULL,
  `status` enum('new','received','3 days in','7 days in','repaired','irreparable','invoice sent','paid','shipped','completed','cancelled') NOT NULL,
  `receiveMethod` enum('drop off', 'ship') NOT NULL,
  `returnMethod` enum('pick up', 'ship regular', 'ship express') NOT NULL,
  `user` varchar(10) NOT NULL,
  `userID` int(10) unsigned NOT NULL,
  `first` varchar(50) NOT NULL,
  `last` varchar(50) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address1` varchar(255),
  `address2` varchar(255),
  `city` varchar(50),
  `state` varchar(50),
  `postal` varchar(10),
  `country` char(3),
  `contact` ENUM('phone','email','none') NOT NULL DEFAULT 'none',
  `cost` DOUBLE(6,2) NOT NULL DEFAULT 0,
  `orderDate` datetime NOT NULL,
  `lastModified` datetime NOT NULL,
  `effectiveThrough` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY (`repairOrderHistoryID`),
  KEY `postID`(`repairOrderID`),
  KEY `lastModified_effectiveThrough` (`lastModified`,`effectiveThrough`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE  `sessions` (
  `session_id` varchar(32) NOT NULL,
  `session_data` longtext NOT NULL,
  `expires` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`session_id`),
  KEY `expires` (`expires`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE  `siteTags` (
  `siteTagID` int(10) unsigned NOT NULL auto_increment,
  `referrer` varchar(100) NOT NULL default '',
  `description` varchar(100) NOT NULL default '',
  `matchType` enum('exact match','regular expression', 'explicit call') NOT NULL default 'exact match',
  `matchValue` varchar(255) NOT NULL,
  `placement` enum('header','footer') NOT NULL default 'header',
  `weight` tinyint(3) unsigned NOT NULL,
  `HTTP` text NOT NULL,
  `HTTPS` text NOT NULL,
  `status` enum('active','inactive') NOT NULL default 'active',
  `dateAdded` datetime NOT NULL default '0000-00-00 00:00:00',
  `lastModified` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`siteTagID`),
  UNIQUE KEY `referrer_description` (`referrer`,`description`),
  KEY `description` (`description`),
  KEY `dateAdded` (`dateAdded`),
  KEY `matchType` (`matchType`),
  KEY `status_placement` USING BTREE (`status`,`placement`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE  `siteTagsHistory` (
  `siteTagsHistoryID` int(10) unsigned NOT NULL auto_increment,
  `recordEditor` varchar(10) NOT NULL,
  `recordEditorID` int(10) unsigned NOT NULL,
  `action` varchar(10) NOT NULL,
  `comments` text NOT NULL,
  `siteTagID` int(10) unsigned NOT NULL,
  `referrer` varchar(100) NOT NULL default '',
  `description` varchar(100) NOT NULL default '',
  `matchType` enum('exact match','regular expression', 'explicit call') NOT NULL default 'exact match',
  `matchValue` varchar(255) NOT NULL,
  `placement` enum('header','footer') NOT NULL default 'header',
  `weight` tinyint(3) unsigned NOT NULL,
  `HTTP` text NOT NULL,
  `HTTPS` text NOT NULL,
  `status` enum('active','inactive') NOT NULL default 'active',
  `dateAdded` datetime NOT NULL default '0000-00-00 00:00:00',
  `lastModified` datetime NOT NULL default '0000-00-00 00:00:00',
  `effectiveThrough` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`siteTagsHistoryID`),
  KEY `siteTagID` (`siteTagID`),
  KEY `lastModified_effectiveThrough` (`lastModified`,`effectiveThrough`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `stateCodes` (
  `stateCode` char(2) NOT NULL default '',
  `stateName` varchar(45) NOT NULL default '',
  PRIMARY KEY  (`stateCode`),
  KEY `stateName` (`stateName`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `stateCodes` VALUES ('AL','Alabama'),('AK','Alaska'),('AS','American Samoa'),('AZ','Arizona'),('AR','Arkansas'),('CA','California'),('CO','Colorado'),('CT','Connecticut'),('DE','Delaware'),('DC','District Of Columbia'),('FM','Federated States Of Micronesia'),('FL','Florida'),('GA','Georgia'),('GU','Guam'),('HI','Hawaii'),('ID','Idaho'),('IL','Illinois'),('IN','Indiana'),('IA','Iowa'),('KS','Kansas'),('KY','Kentucky'),('LA','Louisiana'),('ME','Maine'),('MH','Marshall Islands'),('MD','Maryland'),('MA','Massachusetts'),('MI','Michigan'),('MN','Minnesota'),('MS','Mississippi'),('MO','Missouri'),('MT','Montana'),('NE','Nebraska'),('NV','Nevada'),('NH','New Hampshire'),('NJ','New Jersey'),('NM','New Mexico'),('NY','New York'),('NC','North Carolina'),('ND','North Dakota'),('MP','Northern Mariana Islands'),('OH','Ohio'),('OK','Oklahoma'),('OR','Oregon'),('PW','Palau'),('PA','Pennsylvania'),('PR','Puerto Rico'),('RI','Rhode Island'),('SC','South Carolina'),('SD','South Dakota'),('TN','Tennessee'),('TX','Texas'),('UT','Utah'),('VT','Vermont'),('VI','Virgin Islands'),('VA','Virginia'),('WA','Washington'),('WV','West Virginia'),('WI','Wisconsin'),('WY','Wyoming');

CREATE TABLE `systemProblems` (
  `systemProblemID` int(10) unsigned NOT NULL auto_increment,
  `systemID` int(10) unsigned NOT NULL,
  `name` varchar(100) NOT NULL,
  `cost` DOUBLE(6,2) NOT NULL DEFAULT 0,
  `status` enum('active', 'inactive') NOT NULL DEFAULT 'active',
  `dateAdded` datetime NOT NULL,
  PRIMARY KEY  (`systemProblemID`),
  KEY `systemID_status_name` (`systemID`,`status`,`name`),
  KEY `status` (`status`),
  KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `systemProblems` (`systemID`, `name`, `dateAdded`) VALUES (5, 'Red Ring of Death', NOW()), (5, 'Unit locks up or freezes', NOW()), (5, 'Overheating', NOW()), (5, 'No picture or sound', NOW()), (5, 'Error codes', NOW()), (2, 'Loss of power / Unit won\'t power up', NOW()), (2, 'Can\'t read discs', NOW()), (2, 'Discs won\'t eject', NOW()), (2, 'Fan malfunction / Lack of cooling', NOW()), (2, 'Memory card errors', NOW()), (2, 'Games freezing during play', NOW()), (4, 'Unit won\'t sync with remotes', NOW()), (4, 'Loss of power / Unit won\'t power up', NOW()), (4, 'Discs won\'t eject', NOW()), (4, 'Can\'t read discs', NOW()), (4, 'Games freezing during play', NOW()), (1, 'Power and charging problems', NOW()), (1, 'Cracked LCD screens', NOW()), (1, 'Games freezing during play', NOW()), (1, 'No picture or sound', NOW()), (1, 'Minor water damage', NOW()), (3, 'Power and charging problems', NOW()), (3, 'Cracked LCD screens', NOW()), (3, 'Games freezing during play', NOW()), (3, 'No picture or sound', NOW()), (3, 'Minor water damage', NOW()), (0, 'Other', NOW());

CREATE TABLE  `systems` (
  `systemID` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(50) NOT NULL,
  `status` enum('active','inactive') NOT NULL default 'active',
  `dateAdded` datetime NOT NULL,
  PRIMARY KEY  (`systemID`),
  UNIQUE KEY `name` (`name`),
  KEY `status_name` (`status`,`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `systems` (`systemID`, `name`, `dateAdded`) VALUES (1, 'Nintendo DS', NOW()), (2, 'PS3', NOW()), (3, 'PSP', NOW()), (4, 'Wii', NOW()), (5, 'XBox 360', NOW());

CREATE TABLE  `trafficSourceHits` (
  `trafficSourceHitsID` int(11) unsigned NOT NULL auto_increment,
  `ID` int(10) unsigned NOT NULL default '0',
  `subID` varchar(255) NOT NULL default '',
  `extra` varchar(255) NOT NULL default '',
  `date` date NOT NULL default '0000-00-00',
  `hits` int(11) unsigned NOT NULL default '0',
  `uniqueHits` int(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (`trafficSourceHitsID`),
  UNIQUE KEY `ID_subID_extra_date` USING BTREE (`ID`,`subID`,`extra`,`date`),
  KEY `date` USING BTREE (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE  `userActivations` (
  `userActivationID` int(10) unsigned NOT NULL auto_increment,
  `userID` int(10) unsigned NOT NULL,
  `activationCode` varchar(32) NOT NULL,
  `expiration` varchar(45) NOT NULL,
  `activated` datetime NOT NULL,
  `posted` datetime NOT NULL,
  PRIMARY KEY  (`userActivationID`),
  KEY `activationCode_expiration_activated` (`activationCode`,`expiration`,`activated`),
  KEY `userID` (`userID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE  `userCommentVotes0` (
  `userID` int(10) unsigned NOT NULL,
  `postID` int(10) unsigned NOT NULL,
  `postCommentID` int(10) unsigned NOT NULL,
  `vote` tinyint(1) NOT NULL,
  PRIMARY KEY (`userID`,`postID`,`postCommentID`),
  KEY `userID_postCommentID` (`userID`,`postCommentID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE  `userCommentVotes1` (
  `userID` int(10) unsigned NOT NULL,
  `postID` int(10) unsigned NOT NULL,
  `postCommentID` int(10) unsigned NOT NULL,
  `vote` tinyint(1) NOT NULL,
  PRIMARY KEY (`userID`,`postID`,`postCommentID`),
  KEY `userID_postCommentID` (`userID`,`postCommentID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE  `userCommentVotes2` (
  `userID` int(10) unsigned NOT NULL,
  `postID` int(10) unsigned NOT NULL,
  `postCommentID` int(10) unsigned NOT NULL,
  `vote` tinyint(1) NOT NULL,
  PRIMARY KEY (`userID`,`postID`,`postCommentID`),
  KEY `userID_postCommentID` (`userID`,`postCommentID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE  `userCommentVotes3` (
  `userID` int(10) unsigned NOT NULL,
  `postID` int(10) unsigned NOT NULL,
  `postCommentID` int(10) unsigned NOT NULL,
  `vote` tinyint(1) NOT NULL,
  PRIMARY KEY (`userID`,`postID`,`postCommentID`),
  KEY `userID_postCommentID` (`userID`,`postCommentID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE  `userCommentVotes4` (
  `userID` int(10) unsigned NOT NULL,
  `postID` int(10) unsigned NOT NULL,
  `postCommentID` int(10) unsigned NOT NULL,
  `vote` tinyint(1) NOT NULL,
  PRIMARY KEY (`userID`,`postID`,`postCommentID`),
  KEY `userID_postCommentID` (`userID`,`postCommentID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE  `userCommentVotes5` (
  `userID` int(10) unsigned NOT NULL,
  `postID` int(10) unsigned NOT NULL,
  `postCommentID` int(10) unsigned NOT NULL,
  `vote` tinyint(1) NOT NULL,
  PRIMARY KEY (`userID`,`postID`,`postCommentID`),
  KEY `userID_postCommentID` (`userID`,`postCommentID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE  `userCommentVotes6` (
  `userID` int(10) unsigned NOT NULL,
  `postID` int(10) unsigned NOT NULL,
  `postCommentID` int(10) unsigned NOT NULL,
  `vote` tinyint(1) NOT NULL,
  PRIMARY KEY (`userID`,`postID`,`postCommentID`),
  KEY `userID_postCommentID` (`userID`,`postCommentID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE  `userCommentVotes7` (
  `userID` int(10) unsigned NOT NULL,
  `postID` int(10) unsigned NOT NULL,
  `postCommentID` int(10) unsigned NOT NULL,
  `vote` tinyint(1) NOT NULL,
  PRIMARY KEY (`userID`,`postID`,`postCommentID`),
  KEY `userID_postCommentID` (`userID`,`postCommentID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE  `userCommentVotes8` (
  `userID` int(10) unsigned NOT NULL,
  `postID` int(10) unsigned NOT NULL,
  `postCommentID` int(10) unsigned NOT NULL,
  `vote` tinyint(1) NOT NULL,
  PRIMARY KEY (`userID`,`postID`,`postCommentID`),
  KEY `userID_postCommentID` (`userID`,`postCommentID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE  `userCommentVotes9` (
  `userID` int(10) unsigned NOT NULL,
  `postID` int(10) unsigned NOT NULL,
  `postCommentID` int(10) unsigned NOT NULL,
  `vote` tinyint(1) NOT NULL,
  PRIMARY KEY (`userID`,`postID`,`postCommentID`),
  KEY `userID_postCommentID` (`userID`,`postCommentID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE  `userGameStatistics` (
  `userID` int(10) unsigned NOT NULL,
  `gameTitleID` INTEGER NOT NULL,
  `knowledge` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`userID`, `gameTitleID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE  `userPostVotes0` (
  `userID` int(10) unsigned NOT NULL,
  `postID` int(10) unsigned NOT NULL,
  `vote` tinyint(1) NOT NULL,
  PRIMARY KEY (`userID`, `postID`),
  KEY (`postID`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE  `userPostVotes1` (
  `userID` int(10) unsigned NOT NULL,
  `postID` int(10) unsigned NOT NULL,
  `vote` tinyint(1) NOT NULL,
  PRIMARY KEY (`userID`, `postID`),
  KEY (`postID`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE  `userPostVotes2` (
  `userID` int(10) unsigned NOT NULL,
  `postID` int(10) unsigned NOT NULL,
  `vote` tinyint(1) NOT NULL,
  PRIMARY KEY (`userID`, `postID`),
  KEY (`postID`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE  `userPostVotes3` (
  `userID` int(10) unsigned NOT NULL,
  `postID` int(10) unsigned NOT NULL,
  `vote` tinyint(1) NOT NULL,
  PRIMARY KEY (`userID`, `postID`),
  KEY (`postID`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE  `userPostVotes4` (
  `userID` int(10) unsigned NOT NULL,
  `postID` int(10) unsigned NOT NULL,
  `vote` tinyint(1) NOT NULL,
  PRIMARY KEY (`userID`, `postID`),
  KEY (`postID`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE  `userPostVotes5` (
  `userID` int(10) unsigned NOT NULL,
  `postID` int(10) unsigned NOT NULL,
  `vote` tinyint(1) NOT NULL,
  PRIMARY KEY (`userID`, `postID`),
  KEY (`postID`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE  `userPostVotes6` (
  `userID` int(10) unsigned NOT NULL,
  `postID` int(10) unsigned NOT NULL,
  `vote` tinyint(1) NOT NULL,
  PRIMARY KEY (`userID`, `postID`),
  KEY (`postID`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE  `userPostVotes7` (
  `userID` int(10) unsigned NOT NULL,
  `postID` int(10) unsigned NOT NULL,
  `vote` tinyint(1) NOT NULL,
  PRIMARY KEY (`userID`, `postID`),
  KEY (`postID`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE  `userPostVotes8` (
  `userID` int(10) unsigned NOT NULL,
  `postID` int(10) unsigned NOT NULL,
  `vote` tinyint(1) NOT NULL,
  PRIMARY KEY (`userID`, `postID`),
  KEY (`postID`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE  `userPostVotes9` (
  `userID` int(10) unsigned NOT NULL,
  `postID` int(10) unsigned NOT NULL,
  `vote` tinyint(1) NOT NULL,
  PRIMARY KEY (`userID`, `postID`),
  KEY (`postID`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE  `users` (
  `userID` int(10) unsigned NOT NULL auto_increment,
  `externalProvider` VARCHAR(45) DEFAULT NULL,
  `externalID` varchar(255) DEFAULT NULL,
  `userName` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(46) NOT NULL,
  `status` enum('new','active','inactive','deactivated','banned') NOT NULL default 'new',
  `dateCreated` datetime NOT NULL default '0000-00-00 00:00:00',
  `lastModified` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`userID`),
  UNIQUE KEY `externalID` (`externalID`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `userName` (`userName`),
  KEY `externalProvider` (`externalProvider`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE  `usersHistory` (
  `usersHistoryID` int(10) unsigned NOT NULL auto_increment,
  `recordEditor` varchar(10) NOT NULL,
  `recordEditorID` int(10) unsigned NOT NULL,
  `action` varchar(10) NOT NULL,
  `comments` text NOT NULL,
  `userID` int(10) unsigned NOT NULL default '0',
  `externalProvider` VARCHAR(45) DEFAULT NULL,
  `externalID` varchar(255) DEFAULT NULL,
  `userName` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(46) NOT NULL,
  `status` enum('new','active','inactive','deactivated','banned') NOT NULL default 'new',
  `dateCreated` datetime NOT NULL default '0000-00-00 00:00:00',
  `lastModified` datetime NOT NULL default '0000-00-00 00:00:00',
  `effectiveThrough` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  USING BTREE (`usersHistoryID`),
  KEY `userID` (`userID`),
  KEY `lastModified_effectiveThrough` (`lastModified`,`effectiveThrough`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE  `userStatistics` (
  `userID` int(10) unsigned NOT NULL,
  `reputation` int(10) NOT NULL,
  `knowledge` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`userID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE  `userSubscriptions` (
  `userID` int(10) unsigned NOT NULL,
  `gameTitleID` INTEGER NOT NULL,
  PRIMARY KEY  (`userID`, `gameTitleID`),
  INDEX `gameTitleID`(`gameTitleID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
