<?php
/**
 *
 * @author Tongle Xu <xutongle@gmail.com> 2013-2-26
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id$
 */
return array (
		'stat' => true,// 是否开启附件状态统计
		'storage' => 'Local',//附件保存地方 local ftp OSS
		'upload_path' => DATA_PATH . 'attachment/',// 附件保存路径
		'maxsize' => '2048',// 允许上传附件大小
		'allowext' => 'jpg|jpeg|gif|bmp|png|doc|docx|xls|xlsx|ppt|pptx|pdf|txt|rar|zip|swf',// 允许上传附件类型

		//本地附件
		'upload_url' => 'http://dev-local.yuncms.net/data/attachment/',// //附件URL路径
		'avatar_url' => 'http://dev-local.yuncms.net/data/avatar/',// //用户头像URL路径

		//远程附件
		'ftp_ssl' => false,
		'ftp_host' => '42.121.2.83',
		'ftp_port' => '21',
		'ftp_username' => 'demo',
		'ftp_password' => 'demo1234',
		'ftp_pasv' => false,
		'ftp_attachdir' => '/202',
		'ftp_timeout' => '30',
		'ftp_url' => 'http://dev.yuncms.net/data/attachment/1',

		//阿里开放存储服务 OSS
		'oss_host' => 'oss.aliyuncs.com',
		'oss_access_id' => '2x4tihk6wv8fy0lvlgkp84t3',
		'oss_access_key' => 'ajVTJkcN1wjph0JW9LRHvsCSdbA=',
		'oss_bucket' => 'yuncms',
		'oss_domain_style' => false,
		'oss_url' => 'http://oss.aliyuncs.com/yuncms/',

		//水印
		'watermark_enable' => '1',// 是否开启图片水印
		'watermark_minwidth' => '300',// 水印添加条件
		'watermark_minheight' => '300',// 水印添加条件
		'watermark_img' => 'mark.gif',// 水印图片
		'watermark_pct' => '100',
		'watermark_quality' => '80',
		'watermark_pos' => '9',
);