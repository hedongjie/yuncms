DROP TABLE IF EXISTS `yuncms_comment_data_1`;
CREATE TABLE IF NOT EXISTS `yuncms_comment_data_1` (
  `id` int(10) unsigned NOT NULL auto_increment COMMENT '评论ID',
  `commentid` char(30) NOT NULL default '' COMMENT '评论ID号',
  `userid` int(10) unsigned default '0' COMMENT '用户ID',
  `username` varchar(20) default NULL COMMENT '用户名',
  `creat_at` int(10) default NULL COMMENT '发布时间',
  `ip` varchar(15) default NULL COMMENT '用户IP地址',
  `status` tinyint(1) default '0' COMMENT '评论状态{0:未审核,-1:未通过审核,1:通过审核}',
  `content` text COMMENT '评论内容',
  `support` mediumint(8) unsigned default '0' COMMENT '支持数',
  `reply` tinyint(1) NOT NULL default '0' COMMENT '是否为回复',
  PRIMARY KEY  (`id`),
  KEY `commentid` (`commentid`),
  KEY `support` (`support`)
) ENGINE=MyISAM;
