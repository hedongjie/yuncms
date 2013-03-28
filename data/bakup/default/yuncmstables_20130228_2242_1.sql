# YUNCMS bakfile
# version:YUNCMS V2013
# time:2013-02-28 09:24:04
# type:YUNCMS
# TINTSOFT:http://www.tintsoft.com
# --------------------------------------------------------


DROP TABLE IF EXISTS `yun_admin`;
CREATE TABLE `yun_admin` (
  `userid` mediumint(6) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(20) NOT NULL,
  `password` varchar(32) NOT NULL,
  `roleid` smallint(5) DEFAULT '0',
  `encrypt` varchar(6) NOT NULL,
  `mobile` varchar(11) DEFAULT '',
  `email` varchar(40) DEFAULT '',
  `realname` varchar(50) DEFAULT '',
  `lastloginip` varchar(15) DEFAULT '',
  `lastlogintime` int(10) unsigned DEFAULT '0',
  PRIMARY KEY (`userid`),
  KEY `username` (`username`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

INSERT INTO `yun_admin` VALUES('1','admin','02c2fc56738f3093a50a6c3ecea7614c','1','ovEYRc','18615271353','xutongle@gmail.com','SuperMan','127.0.0.1','1362012943');

DROP TABLE IF EXISTS `yun_admin_log`;
CREATE TABLE `yun_admin_log` (
  `logid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `application` varchar(15) NOT NULL,
  `controller` varchar(20) NOT NULL,
  `action` varchar(30) NOT NULL,
  `querystring` varchar(255) NOT NULL,
  `data` mediumtext NOT NULL,
  `userid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `username` varchar(20) NOT NULL,
  `ip` varchar(15) NOT NULL,
  `time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`logid`),
  KEY `username` (`username`,`action`) USING BTREE,
  KEY `application` (`application`,`controller`,`action`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `yun_admin_menu`;
CREATE TABLE `yun_admin_menu` (
  `id` smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `name` char(40) NOT NULL DEFAULT '',
  `parentid` smallint(6) NOT NULL DEFAULT '0',
  `level` smallint(2) unsigned NOT NULL,
  `application` char(20) NOT NULL DEFAULT '',
  `controller` char(20) NOT NULL DEFAULT '',
  `action` char(20) NOT NULL DEFAULT '',
  `data` char(100) NOT NULL DEFAULT '',
  `listorder` smallint(6) unsigned NOT NULL DEFAULT '0',
  `display` enum('1','0') NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `listorder` (`listorder`),
  KEY `parentid` (`parentid`),
  KEY `application` (`application`,`controller`,`action`)
) ENGINE=MyISAM AUTO_INCREMENT=65 DEFAULT CHARSET=utf8;

INSERT INTO `yun_admin_menu` VALUES('1','panel','0','0','admin','Index','public_main','','0','1');
INSERT INTO `yun_admin_menu` VALUES('2','sys_setting','0','0','admin','Setting','init','','0','1');
INSERT INTO `yun_admin_menu` VALUES('3','application','0','0','admin','Application','init','','0','1');
INSERT INTO `yun_admin_menu` VALUES('4','content','0','0','content','Content','init','','0','1');
INSERT INTO `yun_admin_menu` VALUES('5','member','0','0','member','Member','init','','0','1');
INSERT INTO `yun_admin_menu` VALUES('6','userinterface','0','0','template','Style','init','','0','1');
INSERT INTO `yun_admin_menu` VALUES('7','extend','0','0','admin','Extend','init_extend','','0','1');
INSERT INTO `yun_admin_menu` VALUES('8','admininfo','1','1','admin','Private','init','','0','1');
INSERT INTO `yun_admin_menu` VALUES('9','editpwd','8','2','admin','Private','public_edit_pwd','','0','1');
INSERT INTO `yun_admin_menu` VALUES('10','editinfo','8','2','admin','Private','public_edit_info','','0','1');
INSERT INTO `yun_admin_menu` VALUES('11','create_html_quick','1','1','content','Create_html','index','','0','1');
INSERT INTO `yun_admin_menu` VALUES('12','create_index','11','2','content','Create_html','public_index','','0','1');
INSERT INTO `yun_admin_menu` VALUES('13','correlative_setting','2','1','admin','Admin','init','','0','1');
INSERT INTO `yun_admin_menu` VALUES('14','admin_setting','2','1','admin','Admin','init','','0','1');
INSERT INTO `yun_admin_menu` VALUES('15','basic_config','13','2','admin','Setting','init','','0','1');
INSERT INTO `yun_admin_menu` VALUES('16','site_config','13','2','admin','Setting','init','tab=2','0','1');
INSERT INTO `yun_admin_menu` VALUES('17','safe_config','13','2','admin','Setting','init','tab=3','0','1');
INSERT INTO `yun_admin_menu` VALUES('18','attachment_config','13','2','admin','Setting','init','tab=4','0','1');
INSERT INTO `yun_admin_menu` VALUES('19','sms_config','13','2','admin','Setting','init','tab=5','0','1');
INSERT INTO `yun_admin_menu` VALUES('20','contactus_config','13','2','admin','Setting','init','tab=6','0','1');
INSERT INTO `yun_admin_menu` VALUES('21','connect_config','13','2','admin','Setting','init','tab=7','0','1');
INSERT INTO `yun_admin_menu` VALUES('22','mail_config','13','2','admin','Setting','init','tab=8','0','1');
INSERT INTO `yun_admin_menu` VALUES('23','admin_manage','14','2','admin','Admin','init','','0','1');
INSERT INTO `yun_admin_menu` VALUES('24','admin_add','23','3','admin','Admin','add','','0','1');
INSERT INTO `yun_admin_menu` VALUES('25','admin_edit','23','3','admin','Admin','edit','','0','0');
INSERT INTO `yun_admin_menu` VALUES('26','admin_delete','23','3','admin','Admin','delete','','0','0');
INSERT INTO `yun_admin_menu` VALUES('27','role_manage','14','2','admin','Role','init','','0','1');
INSERT INTO `yun_admin_menu` VALUES('28','role_add','27','3','admin','Role','add','','0','1');
INSERT INTO `yun_admin_menu` VALUES('29','priv_setting','27','3','admin','Role','setting_cat_priv','','0','0');
INSERT INTO `yun_admin_menu` VALUES('30','role_priv','27','3','admin','Role','role_priv','','0','0');
INSERT INTO `yun_admin_menu` VALUES('31','role_edit','27','3','admin','Role','edit','','0','0');
INSERT INTO `yun_admin_menu` VALUES('32','rolemember_manage','27','3','admin','Role','member_manage','','0','0');
INSERT INTO `yun_admin_menu` VALUES('33','role_delete','27','3','admin','Role','delete','','0','0');
INSERT INTO `yun_admin_menu` VALUES('34','application_manage','3','1','admin','Application','init','','0','1');
INSERT INTO `yun_admin_menu` VALUES('35','application_manage','34','2','admin','Application','init','','0','1');
INSERT INTO `yun_admin_menu` VALUES('36','content_publish','4','1','content','','','','0','1');
INSERT INTO `yun_admin_menu` VALUES('37','create_manage','4','1','content','','','','0','1');
INSERT INTO `yun_admin_menu` VALUES('38','content_settings','4','1','content','','','','0','1');
INSERT INTO `yun_admin_menu` VALUES('39','manage_member','5','1','member','','','','0','1');
INSERT INTO `yun_admin_menu` VALUES('40','template_manager','6','1','template','','','','0','1');
INSERT INTO `yun_admin_menu` VALUES('41','template_style','40','2','template','Style','init','','0','1');
INSERT INTO `yun_admin_menu` VALUES('42','import_style','41','3','template','Style','import','','0','0');
INSERT INTO `yun_admin_menu` VALUES('43','template_export','42','4','template','Style','export','','0','0');
INSERT INTO `yun_admin_menu` VALUES('44','template_onoff','42','4','template','Style','disable','','0','0');
INSERT INTO `yun_admin_menu` VALUES('45','template_updatename','42','4','template','Style','updatename','','0','0');
INSERT INTO `yun_admin_menu` VALUES('46','template_file_list','42','4','template','File','init','','0','0');
INSERT INTO `yun_admin_menu` VALUES('47','template_file_edit','42','4','template','File','edit_file','','0','0');
INSERT INTO `yun_admin_menu` VALUES('48','file_add_file','42','4','template','File','add_file','','0','0');
INSERT INTO `yun_admin_menu` VALUES('49','template_visualization','42','4','template','File','visualization','','0','0');
INSERT INTO `yun_admin_menu` VALUES('50','yuncms_tag_edit','42','4','template','File','edit_yuncms_tag','','0','0');
INSERT INTO `yun_admin_menu` VALUES('51','history_version','42','4','template','Template_bak','init','','0','0');
INSERT INTO `yun_admin_menu` VALUES('52','restore_version','42','4','template','Template_bak','restore','','0','0');
INSERT INTO `yun_admin_menu` VALUES('53','del_history_version','42','4','template','Template_bak','delete','','0','0');
INSERT INTO `yun_admin_menu` VALUES('54','extend_all','7','1','admin','Extend','init','','0','1');
INSERT INTO `yun_admin_menu` VALUES('55','menu_manage','54','2','admin','Menu','init','','0','1');
INSERT INTO `yun_admin_menu` VALUES('56','menu_add','55','3','admin','Menu','add','','0','1');
INSERT INTO `yun_admin_menu` VALUES('57','edit_menu','55','3','admin','Menu','edit','','0','0');
INSERT INTO `yun_admin_menu` VALUES('58','delete_menu','55','3','admin','Menu','delete','','0','0');
INSERT INTO `yun_admin_menu` VALUES('59','admin_log','54','2','admin','Log','init','','0','1');
INSERT INTO `yun_admin_menu` VALUES('60','delete_log','59','3','admin','Log','delete','','0','0');
INSERT INTO `yun_admin_menu` VALUES('61','cache_all','54','2','admin','Cache_all','Init','','0','1');
INSERT INTO `yun_admin_menu` VALUES('62','database_toos','54','2','admin','Database','export','','0','1');
INSERT INTO `yun_admin_menu` VALUES('63','database_export','62','3','admin','Database','export','','0','1');
INSERT INTO `yun_admin_menu` VALUES('64','database_import','62','3','admin','Database','import','','0','1');

DROP TABLE IF EXISTS `yun_admin_panel`;
CREATE TABLE `yun_admin_panel` (
  `menuid` mediumint(8) unsigned NOT NULL,
  `userid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `name` char(32) DEFAULT NULL,
  `url` char(255) DEFAULT NULL,
  `datetime` int(10) unsigned DEFAULT '0',
  UNIQUE KEY `userid` (`menuid`,`userid`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `yun_admin_role`;
CREATE TABLE `yun_admin_role` (
  `roleid` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `rolename` varchar(50) NOT NULL,
  `description` text NOT NULL,
  `listorder` smallint(5) unsigned NOT NULL DEFAULT '0',
  `disabled` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`roleid`),
  KEY `listorder` (`listorder`) USING BTREE,
  KEY `disabled` (`disabled`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

INSERT INTO `yun_admin_role` VALUES('1','超级管理员','','0','0');
INSERT INTO `yun_admin_role` VALUES('2','网站管理员','','0','0');

DROP TABLE IF EXISTS `yun_admin_role_priv`;
CREATE TABLE `yun_admin_role_priv` (
  `roleid` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `application` char(20) NOT NULL,
  `controller` char(20) NOT NULL,
  `action` char(20) NOT NULL,
  `data` char(30) NOT NULL DEFAULT '',
  KEY `roleid` (`roleid`,`application`,`controller`,`action`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `yun_application`;
CREATE TABLE `yun_application` (
  `application` varchar(15) NOT NULL,
  `name` varchar(20) NOT NULL,
  `iscore` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `version` varchar(50) NOT NULL DEFAULT '',
  `description` varchar(255) NOT NULL,
  `setting` mediumtext NOT NULL,
  `listorder` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `disabled` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `installdate` date NOT NULL DEFAULT '0000-00-00',
  `updatedate` date NOT NULL DEFAULT '0000-00-00',
  PRIMARY KEY (`application`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `yun_application` VALUES('admin','核心','1','V2013','','a:17:{s:9:\"site_name\";s:23:\"12316彩短信云平台\";s:10:\"site_title\";s:23:\"12316彩短信云平台\";s:8:\"keywords\";s:23:\"12316彩短信云平台\";s:11:\"description\";s:23:\"12316彩短信云平台\";s:10:\"copyrights\";s:23:\"12316彩短信云平台\";s:3:\"icp\";s:20:\"鲁ICP备09088162号\";s:19:\"maxloginfailedtimes\";s:2:\"10\";s:15:\"live_ifonserver\";s:4:\"true\";s:12:\"live_boxopen\";s:4:\"true\";s:11:\"live_boxtip\";s:4:\"true\";s:11:\"companyname\";s:0:\"\";s:12:\"contact_name\";s:0:\"\";s:6:\"mobile\";s:0:\"\";s:9:\"telephone\";s:0:\"\";s:2:\"qq\";s:0:\"\";s:7:\"address\";s:0:\"\";s:5:\"email\";s:0:\"\";}','0','0','2010-05-18','2012-05-18');

DROP TABLE IF EXISTS `yun_cache`;
CREATE TABLE `yun_cache` (
  `filename` char(50) NOT NULL,
  `path` char(50) NOT NULL,
  `data` mediumtext NOT NULL,
  PRIMARY KEY (`filename`,`path`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `yun_ipbanned`;
CREATE TABLE `yun_ipbanned` (
  `ipbannedid` smallint(5) NOT NULL AUTO_INCREMENT,
  `ip` char(15) NOT NULL,
  `expires` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`ipbannedid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `yun_session` (
  `sessionid` char(32) NOT NULL,
  `userid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `ip` char(15) NOT NULL,
  `lastvisit` int(10) unsigned NOT NULL DEFAULT '0',
  `roleid` tinyint(3) unsigned DEFAULT '0',
  `groupid` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `application` char(20) NOT NULL,
  `controller` char(20) NOT NULL,
  `action` char(30) NOT NULL,
  `data` char(255) NOT NULL,
  PRIMARY KEY (`sessionid`),
  KEY `lastvisit` (`lastvisit`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `yun_times`;
CREATE TABLE `yun_times` (
  `username` char(40) NOT NULL,
  `ip` char(15) NOT NULL,
  `logintime` int(10) unsigned NOT NULL DEFAULT '0',
  `isadmin` tinyint(1) NOT NULL DEFAULT '0',
  `times` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`username`,`isadmin`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8;


