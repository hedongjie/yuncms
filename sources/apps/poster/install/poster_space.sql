DROP TABLE IF EXISTS `yuncms_poster_space`;
CREATE TABLE IF NOT EXISTS `yuncms_poster_space` (
  `spaceid` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `name` char(50) NOT NULL,
  `type` char(30) NOT NULL,
  `path` char(40) NOT NULL DEFAULT '',
  `width` smallint(4) unsigned NOT NULL DEFAULT '0',
  `height` smallint(4) unsigned NOT NULL DEFAULT '0',
  `setting` char(100) NOT NULL,
  `description` char(100) NOT NULL,
  `items` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `disabled` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`spaceid`),
  KEY `disabled` (`disabled`)
) ENGINE=MyISAM ;