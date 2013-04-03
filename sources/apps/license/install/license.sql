DROP TABLE IF EXISTS `yuncms_license`;
CREATE TABLE `yuncms_license` (
  `licenseid` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `typeid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `uuid` varchar(50) DEFAULT '',
  `sitename` varchar(50) NOT NULL DEFAULT '',
  `domain` varchar(255) NOT NULL DEFAULT '',
  `truename` varchar(50) DEFAULT '',
  `telephone` varchar(50) DEFAULT '',
  `mobile` varchar(50) DEFAULT '',
  `email` varchar(50) DEFAULT '',
  `msn` varchar(50) DEFAULT '',
  `qq` varchar(15) DEFAULT '',
  `address` varchar(255) DEFAULT '',
  `postcode` varchar(6) DEFAULT '',
  `licensekey` varchar(255) DEFAULT '',
  `listorder` smallint(5) unsigned DEFAULT '0',
  `addtime` int(10) unsigned DEFAULT '0',
  `starttime` date DEFAULT '0000-00-00',
  `endtime` date DEFAULT '0000-00-00',
  PRIMARY KEY (`licenseid`),
  KEY `typeid` (`typeid`,`listorder`,`licenseid`)
) ENGINE=MyISAM;