DROP TABLE IF EXISTS `yuncms_mood`;
CREATE TABLE IF NOT EXISTS `yuncms_mood` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `catid` int(10) unsigned NOT NULL default '0' COMMENT '栏目id',
  `contentid` int(10) unsigned NOT NULL default '0' COMMENT '文章id',
  `total` int(10) unsigned NOT NULL default '0' COMMENT '总数',
  `n1` int(10) unsigned NOT NULL default '0',
  `n2` int(10) unsigned NOT NULL default '0',
  `n3` int(10) unsigned NOT NULL default '0',
  `n4` int(10) unsigned NOT NULL default '0',
  `n5` int(10) unsigned NOT NULL default '0',
  `n6` int(10) unsigned NOT NULL default '0',
  `n7` int(10) unsigned NOT NULL default '0',
  `n8` int(10) unsigned NOT NULL default '0',
  `n9` int(10) unsigned NOT NULL default '0',
  `n10` int(10) unsigned NOT NULL default '0',
  `lastupdate` int(10) unsigned NOT NULL default '0' COMMENT '最后更新时间',
  PRIMARY KEY  (`id`),
  KEY `total` (`total`),
  KEY `lastupdate` (`lastupdate`),
  KEY `catid` (`catid`,`contentid`)
) ENGINE=MyISAM;
