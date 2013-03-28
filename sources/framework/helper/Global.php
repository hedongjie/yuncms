<?php
/**
 * 用户自定义
 * @author Tongle Xu <xutongle@gmail.com> 2012-12-26
 * @copyright Copyright (c) 2003-2103 www.tintsoft.com
 * @version $Id: Global.php 2 2013-01-14 07:14:05Z xutongle $
 */
/**
 * 提示信息页面跳转，跳转地址如果传入数组，页面会提示多个地址供用户选择，默认跳转地址为数组的第一个值，时间为5秒。
 * showmessage('登录成功', array('默认跳转地址'=>'http://www.yuncms.net'));
 *
 * @param string $msg 提示信息
 * @param mixed(string/array) $url_forward 跳转地址
 * @param int $ms 跳转等待时间
 */
function showmessage($msg, $url_forward = 'goback', $ms = 1250, $dialog = '', $returnjs = '') {
	if ($ms == 301) {
		Loader::session ();
		$_SESSION ['msg'] = $msg;
		Header ( "HTTP/1.1 301 Moved Permanently" );
		Header ( "Location: $url_forward" );
		exit ();
	}
	if (defined ( 'IN_ADMIN' )) {
		include (admin::admin_tpl ( 'showmessage', 'admin' ));
	} else {
		include (template ( 'yuncms', 'message' ));
	}
	if (isset ( $_SESSION ['msg'] )) unset ( $_SESSION ['msg'] );
	exit ();
}

/**
 * 对用户的密码进行加密
 *
 * @param $password
 * @param $encrypt //传入加密串，在修改密码时做认证
 * @return array/password
 */
function password($password, $encrypt = '') {
	$pwd = array ();
	$pwd ['encrypt'] = $encrypt ? $encrypt : random ( 6 );
	$pwd ['password'] = md5 ( md5 ( trim ( $password ) ) . $pwd ['encrypt'] );
	return $encrypt ? $pwd ['password'] : $pwd;
}

/**
 * 生成上传附件验证
 *
 * @param $args 参数
 * @param $operation 操作类型(加密解密)
 */
function upload_key($args, $operation = 'ENCODE') {
	$auth_key = md5 ( C ( 'config', 'auth_key' ) . $_SERVER ['HTTP_USER_AGENT'] );
	$authkey = authcode ( $args, $operation, $auth_key );
	return $authkey;
}

/**
 * 获取YUNCMS版本号
 */
function get_version($type = 0) {
	$version = C ( 'version' );
	if ($type == 1) {
		return $version ['version'];
	} elseif ($type == 2) {
		return $version ['release'];
	} else {
		return $version ['version'] . ' ' . $version ['release'];
	}
}

/**
 * 安全过滤函数
 *
 * @param
 *        	$string
 * @return string
 */
function safe_replace($string) {
	$string = str_replace ( '%20', '', $string );
	$string = str_replace ( '%27', '', $string );
	$string = str_replace ( '%2527', '', $string );
	$string = str_replace ( '*', '', $string );
	$string = str_replace ( '"', '&quot;', $string );
	$string = str_replace ( "'", '', $string );
	$string = str_replace ( '"', '', $string );
	$string = str_replace ( ';', '', $string );
	$string = str_replace ( '<', '&lt;', $string );
	$string = str_replace ( '>', '&gt;', $string );
	$string = str_replace ( "{", '', $string );
	$string = str_replace ( '}', '', $string );
	$string = str_replace ( '\\', '', $string );
	return $string;
}

/**
 * 将文件大小以字节(bytes)格式化，并添加适合的缩写单位。
 *
 * @param string $filesize
 * @return string
 */
function byte_format($filesize) {
	if ($filesize >= 1073741824) {
		$filesize = round ( $filesize / 1073741824 * 100 ) / 100 . ' GB';
	} elseif ($filesize >= 1048576) {
		$filesize = round ( $filesize / 1048576 * 100 ) / 100 . ' MB';
	} elseif ($filesize >= 1024) {
		$filesize = round ( $filesize / 1024 * 100 ) / 100 . ' KB';
	} else {
		$filesize = $filesize . ' Bytes';
	}
	return $filesize;
}

/**
 * 获取附件URL访问路径
 *
 * @param string $storage
 * @return string
 */
function upload_url($storage) {
	if (C ( 'attachment', 'storage' ) == 'Ftp') {
		return C ( 'attachment', 'ftp_url' );
	} else if (C ( 'attachment', 'storage' ) == 'ALIOSS') {
		return C ( 'attachment', 'oss_url' );
	} else {
		return C ( 'attachment', 'upload_url' );
	}
}
/**
 * 生成CNZZ统计代码
 */
function tjcode() {
	$config = S ( 'common/cnzz' );
	if (empty ( $config )) {
		return false;
	} else {
		return '<script src=\'http://pw.cnzz.com/c.php?id=' . $config ['username'] . '&l=2\' language=\'JavaScript\' charset=\'gb2312\'></script>';
	}
}

/**
 * 水印添加
 *
 * @param $source 原图片路径
 * @param $target 生成水印图片途径，默认为空，覆盖原图
 */
function watermark($source, $target = '') {
	static $image = null;
	if (empty ( $source )) return $source;
	if (! extension_loaded ( 'gd' ) || strpos ( $source, '://' )) return $source;
	if (! $target) $target = $source;
	if ($image == null) $image = new Image ( 0 );
	$image->watermark ( $source, $target );
	return $target;
}

/**
 * 生成缩略图函数
 *
 * @param $imgurl 图片路径
 * @param $width 缩略图宽度
 * @param $height 缩略图高度
 * @param $autocut 是否自动裁剪
 *        	默认裁剪，当高度或宽度有一个数值为0是，自动关闭
 * @param $smallpic 无图片是默认图片路径
 */
function thumb($imgurl, $width = 100, $height = 100, $autocut = 1, $smallpic = 'nopic.gif') {
	static $image = null;
	$upload_url = C ( 'attachment', 'upload_url' );
	$upload_path = C ( 'attachment', 'upload_path' );
	if (empty ( $imgurl )) return IMG_PATH . $smallpic;
	$imgurl_replace = str_replace ( $upload_url, '', $imgurl );
	if (! extension_loaded ( 'gd' ) || strpos ( $imgurl_replace, '://' )) return $imgurl;
	if (! file_exists ( $upload_path . $imgurl_replace )) return IMG_PATH . $smallpic;
	list ( $width_t, $height_t, $type, $attr ) = getimagesize ( $upload_path . $imgurl_replace );
	if ($width >= $width_t || $height >= $height_t) return $imgurl;
	$newimgurl = dirname ( $imgurl_replace ) . '/thumb_' . $width . '_' . $height . '_' . basename ( $imgurl_replace );
	if (file_exists ( $upload_path . $newimgurl )) return $upload_url . $newimgurl;
	if ($image == null) $image = new Image ( 1 );
	return $image->thumb ( $upload_path . $imgurl_replace, $upload_path . $newimgurl, $width, $height, '', $autocut ) ? $upload_url . $newimgurl : $imgurl;
}

/**
 * 检查id是否存在于数组中
 *
 * @param
 *        	$id
 * @param
 *        	$ids
 * @param
 *        	$s
 */
function check_in($id, $ids = '', $s = ',') {
	if (! $ids) return false;
	$ids = explode ( $s, $ids );
	return is_array ( $id ) ? array_intersect ( $id, $ids ) : in_array ( $id, $ids );
}

/**
 * 系统视图类 继承 视图类
 *
 * @param $$application 应用名称
 * @param $template 模版名称
 * @param $style 视图风格名称
 */
function template($application = 'index', $template = 'index', $style = '') {
	if (! empty ( $style ) && preg_match ( '/([a-z0-9\-_]+)/is', $style )) {
	} elseif (empty ( $style ) && defined ( 'STYLE' )) {
		$style = STYLE;
	} else {
		$style = C ( 'template', 'name' );
	}
	if (empty ( $style )) $style = 'default';
	$compiledtplfile = Template::instance ()->compile ( $template, $application, $style );
	return $compiledtplfile;
}

/**
 * 判断应用是否安装
 *
 * @param $app 应用名称
 */
function application_exists($application = '') {
	if ($application == 'admin') return true;
	$applications = S ( 'common/application' );
	$applications = array_keys ( $applications );
	return in_array ( $application, $applications );
}

/**
 * 生成SEO
 *
 * @param $catid 栏目ID
 * @param $title 标题
 * @param $description 描述
 * @param $keyword 关键词
 */
function seo($catid = '', $title = '', $description = '', $keyword = '') {
	if (! empty ( $title )) $title = strip_tags ( $title );
	if (! empty ( $description )) $description = strip_tags ( $description );
	if (! empty ( $keyword )) $keyword = str_replace ( ' ', ',', strip_tags ( $keyword ) );
	$site = S ( 'common/common' );
	$cat = array ();
	if (! empty ( $catid )) {
		$categorys = S ( 'common/category_content' );
		$cat = $categorys [$catid];
		$cat ['setting'] = unserialize ( $cat ['setting'] );
	}
	$seo ['site_title'] = isset ( $site ['site_title'] ) && ! empty ( $site ['site_title'] ) ? $site ['site_title'] : $site ['name'];
	$seo ['keyword'] = ! empty ( $keyword ) ? $keyword : $site ['keywords'];
	$seo ['description'] = isset ( $description ) && ! empty ( $description ) ? $description : (isset ( $cat ['setting'] ['meta_description'] ) && ! empty ( $cat ['setting'] ['meta_description'] ) ? $cat ['setting'] ['meta_description'] : (isset ( $site ['description'] ) && ! empty ( $site ['description'] ) ? $site ['description'] : ''));
	$seo ['title'] = (isset ( $title ) && ! empty ( $title ) ? $title . ' - ' : '') . (isset ( $cat ['setting'] ['meta_title'] ) && ! empty ( $cat ['setting'] ['meta_title'] ) ? $cat ['setting'] ['meta_title'] . ' - ' : (isset ( $cat ['catname'] ) && ! empty ( $cat ['catname'] ) ? $cat ['catname'] . ' - ' : ''));
	foreach ( $seo as $k => $v ) {
		$seo [$k] = str_replace ( array ("\n","\r" ), '', $v );
	}
	return $seo;
}

/**
 * 生成标题样式
 *
 * @param $style 样式
 * @param $html 是否显示完整的STYLE
 */
function title_style($style, $html = 1) {
	$str = '';
	if ($html) $str = ' style="';
	$style_arr = explode ( ';', $style );
	if (! empty ( $style_arr [0] )) $str .= 'color:' . $style_arr [0] . ';';
	if (! empty ( $style_arr [1] )) $str .= 'font-weight:' . $style_arr [1] . ';';
	if ($html) $str .= '" ';
	return $str;
}

/**
 * 获取子栏目
 *
 * @param $parentid 父级id
 * @param $type 栏目类型
 * @param $self 是否包含本身
 *        	0为不包含
 */
function subcat($parentid = NULL, $type = NULL, $self = 0) {
	$category = S ( 'common/category_content' );
	foreach ( $category as $id => $cat ) {
		if (($parentid === NULL || $cat ['parentid'] == $parentid) && ($type === NULL || $cat ['type'] == $type)) $subcat [$id] = $cat;
		if ($self == 1 && $cat ['catid'] == $parentid && ! $cat ['child']) $subcat [$id] = $cat;
	}
	return $subcat;
}

/**
 * 当前路径
 * 返回指定栏目路径层级
 *
 * @param $catid 栏目id
 * @param $symbol 栏目间隔符
 */
function catpos($catid, $symbol = ' > ') {
	$category_arr = array ();
	$category_arr = S ( 'common/category_content' );
	if (! isset ( $category_arr [$catid] )) return '';
	$pos = '';
	$arrparentid = array_filter ( explode ( ',', $category_arr [$catid] ['arrparentid'] . ',' . $catid ) );
	foreach ( $arrparentid as $catid ) {
		$url = $category_arr [$catid] ['url'];
		if (strpos ( $url, '://' ) === false) $url = substr ( SITE_URL, 0, - 1 ) . $url;
		$pos .= '<a href="' . $url . '">' . $category_arr [$catid] ['catname'] . '</a>' . $symbol;
	}
	return $pos;
}

/**
 * 组装生成ID号
 *
 * @param $applications 模块名
 * @param $contentid 内容
 */
function id_encode($applications, $contentid) {
	return urlencode ( $applications . '-' . $contentid );
}

/**
 * 解析ID
 *
 * @param $id 评论ID
 */
function id_decode($id) {
	return explode ( '-', $id );
}

/**
 * 将文本格式成适合js输出的字符串
 *
 * @param string $string
 *        	需要处理的字符串
 * @param intval $isjs
 *        	是否执行字符串格式化，默认为执行
 * @return string 处理后的字符串
 */
function format_js($string, $isjs = 1) {
	$string = addslashes ( str_replace ( array ("\r","\n" ), array ('','' ), $string ) );
	return $isjs ? 'document.write("' . $string . '");' : $string;
}

/**
 * 获取内容地址
 *
 * @param $catid 栏目ID
 * @param $id 文章ID
 * @param $allurl 是否以绝对路径返回
 */
function go($catid, $id, $allurl = 0) {
	static $category = null;
	if ($category == null) $category = S ( 'common/category_content' );
	$id = intval ( $id );
	if (! $id || ! isset ( $category [$catid] )) return '';
	$modelid = $category [$catid] ['modelid'];
	if (! $modelid) return '';
	$db = Loader::model ( 'content_model' );
	$db->set_model ( $modelid );
	$r = $db->where ( array ('id' => $id ) )->field('url')->find();
	if (! empty ( $allurl )) {
		if (strpos ( $r ['url'], '://' ) === false) {
			if (strpos ( $category [$catid] ['url'], '://' ) === FALSE) {
				$r ['url'] = substr ( SITE_URL, 0, - 1 ) . $r ['url'];
			} else {
				$r ['url'] = $category [$catid] ['url'] . $r ['url'];
			}
		}
	}
	return $r ['url'];
}

/**
 * 获取在线客服列表
 * 依赖JQuery
 */
function Sonline() {
	$config = S ( 'common/common' );
	if (! $config ['live_ifonserver']) return '';
	return '<script type="text/javascript" src="' . JS_PATH . 'jquery.Sonline.js"></script><script type="text/javascript">$(function(){$().Sonline({Position:"'.$config['live_serverlistp'].'",	Top:100,Width:165,Style:6,Effect:true,DefaultsOpen:'.$config['live_boxopen'].',Tel:"4000-094-858",Qqlist:"'.$config['qq'].'"});})</script>';
}