<?php
/**
 *
 * @author Tongle Xu <xutongle@gmail.com> 2012-12-24
 * @copyright Copyright (c) 2003-2103 www.tintsoft.com
 * @version $Id: config.php 2 2013-01-14 07:14:05Z xutongle $
 */
return array (
		//核心设置
		'charset'=>'UTF-8',
		'lang'=>'zh-cn',
		'timezone'=>'Etc/GMT-8',
		'lock_ex'=>true,//文件读写互斥锁
		'gzip' => true, // 是否Gzip压缩后输出
		'debug' => true,// 调试模式
		'firephp' => true,
		'auth_key'=>'asdfasdfaksdhfakjshdf',// 加密随机符
		'url_model'=>0,//URL 模式 0 普通模式 1 URL友好模式（需服务器支持）

		'show_time' => true,// 显示运行时间
		'show_trace' => false,// 显示trace信息
		'trace_exception'=>true,//trace错误信息是否抛出异常 目前仅数据库有效

		'db_sql_build_cache'=>true, //开启SQL编译缓存
		'db_sql_log'=>true,
		'db_fields_cache'=>true, //数据字段缓存
		'db_fields_version'=>1,
		'db_cache_expire'=>1, // 数据库查询操作的默认缓存时间单位：秒
		'db_cache_setting'=>'default', // 数据库缓存所加载的配置

		//AJAX
		'default_ajax_return'=>'json',//AJAX默认返回
		'default_ajax_submit'=>'ajax',//Ajax默认提交参数
		'default_jsonp_callback'=>'callback',//jsonp默认回调函数 在URL中自定义需get callback

		'cache_wrapper'=>true,

		//异常处理 如果定义了error_page这异常信息显示将无效
		'error_page' => '', //错误页面优先级高于错误信息
		'show_error_msg' => true,
		'error_message' => '服务器被外星人劫持。。。。。。',//错误信息

);