DROP TABLE IF EXISTS `yuncms_comment_check`;
CREATE TABLE IF NOT EXISTS `yuncms_comment_check` (
  `id` int(10) NOT NULL auto_increment,
  `comment_data_id` int(10) default '0' COMMENT '论评ID号',
  `tableid` mediumint(8) default '0' COMMENT '数据存储表ID',
  PRIMARY KEY  (`id`),
  KEY `comment_data_id` (`comment_data_id`)
) ENGINE=MyISAM;
