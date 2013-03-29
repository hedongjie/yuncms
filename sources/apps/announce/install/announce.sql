DROP TABLE IF EXISTS `yuncms_announce`;
CREATE TABLE IF NOT EXISTS `yuncms_announce` (
  `aid` smallint(4) unsigned NOT NULL AUTO_INCREMENT,
  `title` char(80) NOT NULL,
  `content` text NOT NULL,
  `starttime` date NOT NULL DEFAULT '0000-00-00',
  `endtime` date NOT NULL DEFAULT '0000-00-00',
  `username` varchar(40) NOT NULL,
  `addtime` int(10) unsigned NOT NULL DEFAULT '0',
  `hits` smallint(5) unsigned NOT NULL DEFAULT '0',
  `passed` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `style` char(15) NOT NULL ,
  `show_template` char(30) NOT NULL,
  PRIMARY KEY (`aid`),
  KEY `passed` (`passed`,`endtime`)
) ENGINE=MyISAM ;