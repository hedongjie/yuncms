DROP TABLE IF EXISTS `yuncms_license_client`;
CREATE TABLE `yuncms_license_client` (
  `clientid` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `typeid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `uuid` varchar(50) DEFAULT '',
  `sitename` varchar(50) DEFAULT '',
  `siteurl` varchar(100) DEFAULT '',
  `charset` varchar(10) DEFAULT '',
  `version` varchar(20) DEFAULT '',
  `release` varchar(20) DEFAULT '',
  `os` varchar(20) DEFAULT '',
  `php` varchar(20) DEFAULT '',
  `mysql` varchar(20) DEFAULT '',
  `browser` varchar(255) DEFAULT '',
  `username` varchar(30) DEFAULT '',
  `email` varchar(50) DEFAULT '',
  `listorder` smallint(5) unsigned DEFAULT '0',
  `addtime` int(10) unsigned DEFAULT '0',
  `starttime` date DEFAULT '0000-00-00',
  `endtime` date DEFAULT '0000-00-00',
  PRIMARY KEY (`clientid`),
  KEY `typeid` (`typeid`,`listorder`,`clientid`)
) ENGINE=MyISAM;