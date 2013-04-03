DROP TABLE IF EXISTS `yuncms_digg`;
CREATE TABLE IF NOT EXISTS `yuncms_digg` (
  `contentid` mediumint(8) unsigned NOT NULL default '0',
  `supports` mediumint(8) unsigned NOT NULL default '0',
  `againsts` mediumint(8) unsigned NOT NULL default '0',
  `supports_day` smallint(5) unsigned NOT NULL default '0',
  `againsts_day` smallint(5) unsigned NOT NULL default '0',
  `supports_week` mediumint(6) unsigned NOT NULL default '0',
  `againsts_week` mediumint(6) unsigned NOT NULL default '0',
  `supports_month` mediumint(8) unsigned NOT NULL default '0',
  `againsts_month` mediumint(8) unsigned NOT NULL default '0',
  `updatetime` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`contentid`),
  KEY `supports` (`supports`)
) ENGINE=MyISAM;