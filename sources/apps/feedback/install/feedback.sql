INSERT INTO `yun_model` (`name`, `description`, `tablename`, `setting`, `addtime`, `items`, `enablesearch`, `disabled`, `default_style`, `category_template`, `list_template`, `show_template`, `js_template`, `sort`, `type`)  VALUES('留言反馈', '留言反馈', 'feedback', '', 0, 1, 1, 0, 'default', NULL, NULL, 'show', 'show_js', 0, 4);
CREATE TABLE IF NOT EXISTS `yuncms_feedback` (
  `fid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `userid` mediumint(8) unsigned NOT NULL,
  `username` varchar(20) NOT NULL,
  `datetime` int(10) unsigned NOT NULL,
  `ip` char(15) NOT NULL,
  PRIMARY KEY (`fid`)
) ENGINE=MyISAM;