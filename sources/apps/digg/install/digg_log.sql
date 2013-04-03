DROP TABLE IF EXISTS `yuncms_digg_log`;
CREATE TABLE IF NOT EXISTS `yuncms_digg_log` (
  `contentid` mediumint(8) unsigned NOT NULL default '0',
  `flag` tinyint(1) unsigned NOT NULL default '0',
  `userid` mediumint(8) unsigned NOT NULL default '0',
  `username` char(20) NOT NULL,
  `ip` char(15) NOT NULL,
  `time` int(10) unsigned NOT NULL default '0',
  KEY `contentid` (`contentid`,`flag`,`time`),
  KEY `userid` (`userid`,`contentid`),
  KEY `ip` (`ip`,`contentid`)
) ENGINE=MyISAM;