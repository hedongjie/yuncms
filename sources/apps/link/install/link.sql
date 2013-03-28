DROP TABLE IF EXISTS `yuncms_link`;
CREATE TABLE `yuncms_link` (
  `linkid` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `typeid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `linktype` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `name` varchar(50) NOT NULL DEFAULT '',
  `url` varchar(255) NOT NULL DEFAULT '',
  `logo` varchar(255) NOT NULL DEFAULT '',
  `introduce` text,
  `username` varchar(30) NOT NULL DEFAULT '',
  `listorder` smallint(5) unsigned NOT NULL DEFAULT '0',
  `elite` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `hits` int(10) unsigned DEFAULT '0',
  `passed` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `addtime` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`linkid`),
  KEY `typeid` (`typeid`,`passed`,`listorder`,`linkid`)
) ENGINE=MyISAM;

INSERT INTO `yuncms_link` VALUES ('1', '0', '0', '天智软件', 'http://www.tintsoft.com', '', '', '徐同乐', '0', '1','0', '1', '1352686234');
INSERT INTO `yuncms_link` VALUES ('2', '0', '0', 'YUNCMS', 'http://www.yuncms.net', '', '', '徐同乐', '0', '1', '0','1', '1352686249');