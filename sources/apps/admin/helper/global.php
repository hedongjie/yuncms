<?php
/**
 *
 * @author Tongle Xu <xutongle@gmail.com> 2013-2-26
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id$
 */
/**
 * 获取系统信息
 */
function get_sysinfo() {
	$sys_info ['os'] = PHP_OS;
	$sys_info ['zlib'] = function_exists ( 'gzclose' ); // zlib
	$sys_info ['safe_mode'] = ( boolean ) ini_get ( 'safe_mode' ); // safe_mode =
	                                                               // Off
	$sys_info ['safe_mode_gid'] = ( boolean ) ini_get ( 'safe_mode_gid' ); // safe_mode_gid
	                                                                       // =
	                                                                       // Off
	$sys_info ['timezone'] = function_exists ( "date_default_timezone_get" ) ? date_default_timezone_get () : L ( 'no_setting' );
	$sys_info ['socket'] = function_exists ( 'fsockopen' );
	$sys_info ['web_server'] = $_SERVER ['SERVER_SOFTWARE'];
	$sys_info ['phpv'] = phpversion ();
	$sys_info ['fileupload'] = @ini_get ( 'file_uploads' ) ? ini_get ( 'upload_max_filesize' ) : 'unknown';
	return $sys_info;
}

/**
 * 返回系统日志大小，单位MB
 * TODO 暂无效
 */
function errorlog_size() {
	$logfile = DATA_PATH . 'logs' . DIRECTORY_SEPARATOR . 'error_log.log';
	if (file_exists ( $logfile )) {
		return filesize ( $logfile );
	}
	return 0;
}

/**
 * 检查目录可写性
 *
 * @param $dir 目录路径
 */
function dir_writeable($dir) {
	$writeable = 0;
	if (is_dir ( $dir )) {
		if ($fp = @fopen ( "$dir/chkdir.test", 'w' )) {
			@fclose ( $fp );
			@unlink ( "$dir/chkdir.test" );
			$writeable = 1;
		} else {
			$writeable = 0;
		}
	}
	return $writeable;
}

/**
 * 浮动弹窗生成
 *
 * @param $url 要打开的URL
 * @param $id 窗口ID
 * @param $title 窗口标题
 * @param $w 宽度
 * @param $h 高度
 * @param $sub 按钮文字
 */
function big_menu($url, $id, $title, $w, $h, $sub = NULL) {
	if (empty ( $sub )) $sub = $title;
	return array (
			'javascript:window.top.art.dialog.open(\'' . $url . '\',{id:\'' . $id . '\',title:\'' . $title . '\',width:\'' . $w . 'px\',height:\'' . $h . 'px\',lock:true,ok:function(iframeWin, topWin){var form = iframeWin.document.getElementById(\'dosubmit\');form.click();return false;},cancel: function(){}});void(0);',
			$sub );
}

/**
 * 询问框生成
 *
 * @param $title 提示文字
 * @param $url 确定后跳转的URL
 */
function art_confirm($title, $url) {
	return 'javascript:window.top.art.dialog.confirm(\'' . $title . '\',function(topWin){redirect(\'' . $url . '\');},function(){});void(0);';
}

function system_information($data) {
	$update = Loader::lib ( 'Update' );
	$notice_url = $update->notice ();
	$string = base64_decode ( 'PHNjcmlwdCB0eXBlPSJ0ZXh0L2phdmFzY3JpcHQiPiQoIiNtYWluX2ZyYW1laWQiKS5yZW1vdmVDbGFzcygiZGlzcGxheSIpOzwvc2NyaXB0PjxkaXYgaWQ9Inl1bmNtc19ub3RpY2UiPjwvZGl2PjxzY3JpcHQgdHlwZT0idGV4dC9qYXZhc2NyaXB0IiBzcmM9Ik5PVElDRV9VUkwiPjwvc2NyaXB0Pg==' );
	$string = str_replace ( 'NOTICE_URL', $notice_url, $string );
	echo str_replace ( '</body>', $string . "\r\n</body>", $data );
}

/**
 * 模板风格列表
 *
 * @param integer $disable 是否显示停用的{1:是,0:否}
 */
function template_list($disable = 0) {
	$list = glob ( SOURCE_PATH . 'template' . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR );
	$arr = array ();
	foreach ( $list as $key => $v ) {
		$dirname = basename ( $v );
		if (file_exists ( $v . DIRECTORY_SEPARATOR . 'config.php' )) {
			$arr [$key] = include $v . DIRECTORY_SEPARATOR . 'config.php';
			if (! $disable && isset ( $arr [$key] ['disable'] ) && $arr [$key] ['disable'] == 1) {
				unset ( $arr [$key] );
				continue;
			}
		} else {
			$arr [$key] ['name'] = $dirname;
		}
		$arr [$key] ['dirname'] = $dirname;
	}
	return $arr;
}